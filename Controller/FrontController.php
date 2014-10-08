<?php

/*
 * This file is part of the Adverts Plugin.
 *
 * (c) Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package AHS\AdvertsPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 */

namespace AHS\AdvertsPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AHS\AdvertsPluginBundle\Entity\Announcement;
use AHS\AdvertsPluginBundle\Form\FrontAnnouncementType;
use AHS\AdvertsPluginBundle\Entity\User;
use AHS\AdvertsPluginBundle\Entity\Image;
use Newscoop\EventDispatcher\Events\GenericEvent;

class FrontController extends Controller
{
    /**
     * @Route("/classifieds", name="ahs_advertsplugin_default_index")
     */
    public function indexAction(Request $request)
    {
        $templatesService = $this->get('newscoop.templates.service');
        $categories = $this->getCategories();

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/main.tpl',
            array(
                'categories' => $categories,
            )
        ));
    }

    /**
     * @Route("/classifieds/add", name="ahs_advertsplugin_default_add", options={"expose"=true})
     */
    public function addAction(Request $request)
    {
        $session = $request->getSession();
        $userService = $this->get('user');
        $templatesService = $this->get('newscoop.templates.service');
        $cacheService = \Zend_Registry::get('container')->get('newscoop.cache');
        $systemPreferences = $this->get('system_preferences_service');
        $adsService = $this->get('ahs_adverts_plugin.ads_service');
        $translator = $this->get('translator');
        $em = $this->container->get('em');
        $newscoopUser = $userService->getCurrentUser();
        $limitExhausted = false;

        if (!$newscoopUser) {
            return new RedirectResponse($this->container->get('zend_router')->assemble(array(
                'controller' => '',
                'action' => 'auth'
            ), 'default') . '?_target_path=' . $this->generateUrl('ahs_advertsplugin_default_add'));
        }

        // create announcement user
        $user = $em->getRepository('AHS\AdvertsPluginBundle\Entity\User')->findOneBy(array(
            'newscoopUserId' => $newscoopUser->getId()
        ));

        $activeAnnouncementsCount = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->createQueryBuilder('a')
            ->select('count(a)')
            ->where('a.announcementStatus = true')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        $announcement = new Announcement();
        $publicationService = $this->container->get('newscoop_newscoop.publication_service');

        $form = $this->createForm(new FrontAnnouncementType(), $announcement, array('translator' => $translator));
        $categories = $this->getCategories();

        $errors = array();
        if ($systemPreferences->AdvertsMaxClassifiedsPerUserEnabled) {
            if ((int) $activeAnnouncementsCount >= (int) $systemPreferences->AdvertsMaxClassifiedsPerUser && !$session->get('ahs_adverts_nolimit')) {
                $limitExhausted = true;
                $errors[]['message'] = $translator->trans('ads.error.maxClassifieds', array('{{ count }}' => $systemPreferences->AdvertsMaxClassifiedsPerUser));
            }
        }

        if ($session->get('ahs_adverts_cantadd')) {
            $limitExhausted = true;
            $errors[]['message'] = $translator->trans('ads.error.cantaddclassifieds');
        }

        if ($request->isMethod('POST') && !$limitExhausted) {
            $form->bind($request);
            if ($form->isValid()) {
                if (!$user) {
                    $user = new User();
                    $user->setNewscoopUser($newscoopUser);
                    $em->persist($user);
                }

                $announcement->setUser($user);
                $announcement->setPublication($publicationService->getPublication());

                $systemPreferences = $this->get('system_preferences_service');

                // set valid date
                $announcement->extendFor($systemPreferences->AdvertsValidTime);
                // set anouncement default status
                if ($systemPreferences->AdvertsReviewStatus == '1') {
                    $announcement->setIsActive(false);
                }

                $em->persist($announcement);
                $em->flush();
                $cacheService->clearNamespace('announcements');

                $this->savePhotosInAnnouncement($announcement, $request);

                if ($systemPreferences->AdvertsEnableNotify == "1") {
                    $this->get('dispatcher')->dispatch('classifieds.modified', new GenericEvent($this, array(
                        'announcement' => $announcement,
                        'notification' => array($request, $user)
                    )));
                }

                if ($session->has('ahs_adverts_nolimit')) {
                    $session->remove('ahs_adverts_nolimit');
                }

                return new RedirectResponse($this->generateUrl(
                    'ahs_advertsplugin_default_show',
                    array(
                        'id' => $announcement->getId(),
                    )
                ));
            }
        }

        if ($session->has('ahs_adverts_nolimit')) {
            $session->remove('ahs_adverts_nolimit');
        }

        if ($session->has('ahs_adverts_cantadd')) {
            $session->remove('ahs_adverts_cantadd');
        }

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/add.tpl',
            array(
                'announcement' => $announcement,
                'categories' => $categories,
                'form' => $form->createView(),
                'form_path' => $this->generateUrl('ahs_advertsplugin_default_add'),
                'type' => 'add',
                'errors' => $errors
            )
        ));
    }

    /**
     * @Route("/classifieds/edit/{id}", requirements={"id" = "\d+"}, name="ahs_advertsplugin_default_edit", options={"expose"=true})
     */
    public function editAction(Request $request, $id = null)
    {
        $templatesService = $this->get('newscoop.templates.service');
        $cacheService = \Zend_Registry::get('container')->get('newscoop.cache');
        $translator = $this->get('translator');
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        if (!$user) { // ignore for logged user

            return new RedirectResponse($this->container->get('zend_router')->assemble(array(
                    'controller' => '',
                    'action' => 'auth'
            ), 'default'));
        }

        $em = $this->container->get('em');
        $announcement = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')->findOneById($id);

        $form = $this->createForm(new FrontAnnouncementType(), $announcement, array('translator' => $translator));
        $categories = $this->getCategories();

        $this->restoreSessionFromDatabase($request, $announcement->getId());
        $errors = array();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                $cacheService->clearNamespace('announcements');
                $em->flush();

                $this->savePhotosInAnnouncement($announcement, $request);

                return new RedirectResponse($this->generateUrl(
                    'ahs_advertsplugin_default_show',
                    array(
                        'id' => $announcement->getId(),
                    )
                ));
            } else {
                foreach ($form->getErrors() as $error) {
                    $errors[]['message'] = $error->getMessage();
                }
            }
        }

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/add.tpl',
            array(
                'announcement' => $announcement,
                'categories' => $categories,
                'form' => $form->createView(),
                'form_path' => $this->generateUrl(
                    'ahs_advertsplugin_default_edit',
                    array('id' => $announcement->getId())
                ),
                'type' => 'edit',
                'errors' => $errors
            )
        ));
    }

    /**
     * @Route("/classifieds/view/{id}/{slug}", requirements={"id" = "\d+"}, name="ahs_advertsplugin_default_show")
     */
    public function showAction(Request $request, $id = null, $slug = null)
    {
        $em = $this->container->get('em');
        $templatesService = $this->get('newscoop.templates.service');

        $announcement = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')->findOneById($id);

        $userSevice = $this->container->get('user.list');
        $user = $userSevice->findOneBy(array(
            'id' => $announcement->getUser()->getNewscoopUserId()
        ));

        $newscoopUser = new \MetaUser($user);
        $announcement->addRead();
        $em->flush();

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/show.tpl',
            array(
                'announcement' => $announcement,
                'announcementPhotos' => $this->processPhotos($request, $announcement),
                'newscoopUser' => $newscoopUser
            )
        ));
    }

    /**
     * @Route("/classifieds/category/{id}/{slug}", name="ahs_advertsplugin_default_category")
     */
    public function categoryAction(Request $request, $id, $slug = null)
    {
        $em = $this->container->get('em');
        $categories = $this->getCategories();
        $currentCategory = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')->findOneById($id);
        $templatesService = $this->get('newscoop.templates.service');

        $validDate = new \DateTime();
        $validDate->modify('-14 days');
        $categoryAnnouncements = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->createQueryBuilder('a')
            ->andWhere('a.category = :category')
            ->andWhere('a.is_active = true')
            ->andWhere('a.created_at >= :validDate')
            ->setParameters(array('category' => $currentCategory->getId(), 'validDate' => $validDate))
            ->orderBy('a.created_at', 'DESC')
            ->getQuery();

        $paginatorService = $this->container->get('newscoop.paginator.paginator_service');
        $paginatorService->setUsedRouteParams(
            array('id' => $currentCategory->getId(), 'slug' => $currentCategory->getSlug())
        );

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/category.tpl',
            array(
                'categories' => $categories,
                'currentCategory' => $currentCategory,
            )
        ));
    }

    /**
     * @Route("/classifieds/type/{type}", name="ahs_advertsplugin_default_type")
     */
    public function typeAction(Request $request, $type)
    {
        $templatesService = $this->get('newscoop.templates.service');

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/type.tpl',
            array(
                'currentType' => $type,
            )
        ));
    }

    /**
     * @Route("/classifieds/upload_photo", options={"expose"=true}, name="ahs_advertsplugin_default_uploadphoto")
     */
    public function uploadPhotoAction(Request $request)
    {
        $em = $this->container->get('em');
        $templatesService = $this->get('newscoop.templates.service');
        $imageService = $this->get('ahs_adverts_plugin.image_service');
        $systemPreferences = $this->get('preferences');
        $translator = $this->get('translator');
        $session = $request->getSession();

        $userService = $this->get('user');
        $limitExhausted = false;
        $result = array();
        $newscoopUser = $userService->getCurrentUser();
        $user = $em->getRepository('AHS\AdvertsPluginBundle\Entity\User')->findOneBy(
            array('newscoopUserId' => $newscoopUser->getId())
        );

        $activeAnnouncementsCount = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->createQueryBuilder('a')
            ->select('count(a)')
            ->where('a.announcementStatus = true')
            ->andWhere('a.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();

        if ($systemPreferences->AdvertsMaxClassifiedsPerUserEnabled) {
            if ((int) $activeAnnouncementsCount >= (int) $systemPreferences->AdvertsMaxClassifiedsPerUser && !$session->get('ahs_adverts_nolimit')) {
                $limitExhausted = true;
            }
        }

        if (!$limitExhausted) {
            $result = null;
            foreach ($request->files->all() as $image) {
                $result = $imageService->upload($image, array('user' => $user));
            }

            if (is_array($result)) {
                return new Response($templatesService->fetchTemplate(
                    '_ahs_adverts/_tpl/renderPhotos.tpl',
                    array(
                        'announcementPhotos' => $this->processPhotos($request),
                        'errors' => array_unique($result),
                    )
                ));
            }

            if (!$request->getSession()->has('announcement_photos')) {
                $request->getSession()->set('announcement_photos', array(array('id' => $result->getId())));
            } else {
                $photos = $request->getSession()->get('announcement_photos', array());
                $photos[] = array('id' => $result->getId());
                $request->getSession()->set('announcement_photos', $photos);
            }

            $result = array(
                'announcementPhotos' => $this->processPhotos($request),
                'result' => true,
            );
        }

        if (empty($result)) {
            $result = array(
                'announcementPhotos' => $this->processPhotos($request),
                'errors' => $translator->trans('ads.error.cantaddimages'),
            );
        }

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/_tpl/renderPhotos.tpl',
            $result
        ));
    }

    /**
     * @Route("/classifieds/remove_photo", options={"expose"=true}, name="ahs_advertsplugin_default_removephoto")
     */
    public function removePhotoAction(Request $request)
    {
        $em = $this->container->get('em');
        $templatesService = $this->get('newscoop.templates.service');
        $announcementPhotos = $request->getSession()->get('announcement_photos', array());
        $photoIdToRemove = $request->request->get('id');

        foreach ($announcementPhotos as $key => $photo) {
            if ($photo['id'] == $photoIdToRemove) {
                unset($announcementPhotos[$key]);
                $request->getSession()->set('announcement_photos', $announcementPhotos);

                // remove image from newscoop
                $photoEntityToRemove = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Image')
                    ->createQueryBuilder('i')
                    ->andWhere('i.id = (:id)')
                    ->setParameter('id', $photoIdToRemove)
                    ->getQuery()
                    ->getSingleResult();

                $em->remove($photoEntityToRemove);
                $em->flush();

                return new Response($templatesService->fetchTemplate(
                    '_ahs_adverts/_tpl/renderPhotos.tpl',
                    array(
                        'announcementPhotos' => $this->processPhotos($request),
                        'errors' => array()
                    )
                ));
            }
        }
    }

    /**
     * @Route("/classifieds/render_photos", options={"expose"=true}, name="ahs_advertsplugin_default_renderphotos")
     */
    public function renderPhotosAction(Request $request)
    {
        $em = $this->container->get('em');
        $templatesService = $this->get('newscoop.templates.service');

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/_tpl/renderPhotos.tpl',
            array(
                'announcementPhotos' => $this->processPhotos($request),
                'errors' => array()
            )
        ));
    }

    /**
     * @Route("/classifieds/change-status/{id}/{status}", options={"expose"=true}, name="ahs_advertsplugin_default_changestatus")
     * @Method("POST")
     */
    public function changeStatusAction(Request $request, $id, $status = null)
    {
        $userService = $this->get('user');
        $session = $request->getSession();
        $systemPreferences = $this->get('preferences');
        $em = $this->get('em');
        $user = $userService->getCurrentUser();
        $responseStatus = false;
        $classifiedUser = $em->getRepository('AHS\AdvertsPluginBundle\Entity\User')->findOneBy(array(
            'newscoopUserId' => $user->getId()
        ));

        $activeCount = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->createQueryBuilder('a')
            ->select('count(a)')
            ->where('a.announcementStatus = true')
            ->andWhere('a.user = :user')
            ->setParameter('user', $classifiedUser)
            ->getQuery()
            ->getSingleScalarResult();

        $announcement = $em->getReference('AHS\AdvertsPluginBundle\Entity\Announcement', $id);
        if ($announcement) {
            if ($user->getId() == (int) $announcement->getUser()->getNewscoopUserId()) {
                if ($announcement->getAnnouncementStatus()) {
                    $announcement->setResult(false);
                    $announcement->setAnnouncementStatus(false);
                    $responseStatus = true;
                    if ($status === 'success') {
                        $announcement->setResult(true);
                    }

                    if (!is_null($request->request->get('announcementComment'))) {
                        $announcement->setComment($request->request->get('announcementComment'));
                    }
                } else {
                    if ($systemPreferences->AdvertsMaxClassifiedsPerUserEnabled) {
                        if ((int) ($activeCount + 1) <= (int) $systemPreferences->AdvertsMaxClassifiedsPerUser && !$session->get('ahs_adverts_cantadd') || $session->get('ahs_adverts_nolimit')) {
                            $announcement->setAnnouncementStatus(true);
                            $session->remove('ahs_adverts_nolimit');
                            $responseStatus = true;
                            $em->flush();
                        }

                        return new JsonResponse(array(
                            'status' => $responseStatus
                        ));
                    }

                    if (!$session->get('ahs_adverts_cantadd')) {
                        $announcement->setAnnouncementStatus(true);
                        $responseStatus = true;
                    }
                }

                $em->flush();
            }
        }

        return new JsonResponse(array(
            'status' => $responseStatus
        ));
    }

    /**
     * @Route("/classifieds/my-classifieds", options={"expose"=true}, name="ahs_advertsplugin_default_myclassifieds")
     */
    public function myClassifiedsAction(Request $request)
    {
        $templatesService = $this->get('newscoop.templates.service');

        return new Response($templatesService->fetchTemplate('_ahs_adverts/my_classifieds.tpl'));
    }

    private function savePhotosInAnnouncement($announcement, $request)
    {
        $em = $this->container->get('em');
        $photosFromSession = $request->getSession()->get('announcement_photos', array());

        $ids = array();
        foreach ($photosFromSession as $photo) {
            $ids[] = $photo['id'];
        }

        if (count($ids) == 0) {
            return array();
        }

        $photos = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Image')
            ->createQueryBuilder('i')
            ->andWhere('i.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        foreach ($photos as $key => $photo) {
            $photo->setAnnouncement($announcement);
        }

        $em->flush();
        $request->getSession()->remove('announcement_photos');
    }

    private function restoreSessionFromDatabase($request, $announcementId)
    {
        $em = $this->container->get('em');

        $announcement = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')->findOneById($announcementId);
        $photos = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Image')
            ->createQueryBuilder('i')
            ->andWhere('i.announcement =:announcement')
            ->setParameter('announcement', $announcement)
            ->getQuery()
            ->getResult();

        if (count($photos) == 0) {
            return false;
        }

        $sessionPhotos = array();
        foreach ($photos as $image) {
            $sessionPhotos[] = array('id' => $image->getId());
        }

        $request->getSession()->set('announcement_photos', $sessionPhotos);
    }

    private function processPhotos($request, $announcement = null)
    {
        $em = $this->container->get('em');
        $imageService = $this->get('ahs_adverts_plugin.image_service');
        if (!$announcement) {
            $photosFromSession = $request->getSession()->get('announcement_photos', array());
            $ids = array();
            foreach ($photosFromSession as $photo) {
                $ids[] = $photo['id'];
            }

            if (count($ids) == 0) {
                return array();
            }

            $photos = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Image')
                ->createQueryBuilder('i')
                ->andWhere('i.id IN (:ids)')
                ->setParameter('ids', $ids)
                ->getQuery()
                ->getResult();
        } else {
            $photos = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Image')
                ->createQueryBuilder('i')
                ->andWhere('i.announcement =:announcement')
                ->setParameter('announcement', $announcement)
                ->getQuery()
                ->getResult();
        }

        $processedPhotos = array();
        foreach ($photos as $photo) {
            $processedPhotos[] = array(
                'id' => $photo->getId(),
                'announcementPhotoId' => $photo->getId(),
                'imageUrl' => $imageService->getImageUrl($photo->getBasename()),
                'thumbnailUrl' => $imageService->getThumbnailUrl($photo->getThumbnailPath())
            );
        }

        return $processedPhotos;
    }

    private function getCategories()
    {
        $em = $this->container->get('em');
        $cacheService = $this->container->get('newscoop.cache');

        if ($cacheService->contains('ahs_anounncements_categories')) {
            $categories = $cacheService->fetch('ahs_anounncements_categories');
        }

        $categories = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')
            ->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        $cacheService->save('ahs_anounncements_categories', $categories);

        return $categories;
    }
}
