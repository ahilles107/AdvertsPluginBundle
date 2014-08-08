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
use Symfony\Component\HttpFoundation\RedirectResponse;
use AHS\AdvertsPluginBundle\Entity\Announcement;
use AHS\AdvertsPluginBundle\Form\FrontAnnouncementType;
use AHS\AdvertsPluginBundle\Entity\User;
use AHS\AdvertsPluginBundle\Entity\Image;

class FrontController extends Controller
{
    /**
     * @Route("/classifieds")
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
     * @Route("/classifieds/add", options={"expose"=true})
     */
    public function addAction(Request $request)
    {
        $auth = \Zend_Auth::getInstance();
        $templatesService = $this->get('newscoop.templates.service');
        $cacheService = \Zend_Registry::get('container')->get('newscoop.cache');
        $adsService = $this->get('ahs_adverts_plugin.ads_service');
        $translator = $this->get('translator');

        if (!$auth->hasIdentity()) {
            return new RedirectResponse($this->container->get('zend_router')->assemble(array(
                'controller' => '',
                'action' => 'auth'
            ), 'default') . '?_target_path=' . $this->generateUrl('ahs_advertsplugin_default_add'));
        }

        $announcement = new Announcement();
        $em = $this->container->get('em');
        $publicationService = $this->container->get('newscoop_newscoop.publication_service');

        $form = $this->createForm(new FrontAnnouncementType(), $announcement, array('translator' => $translator));
        $categories = $this->getCategories();

        $errors = array();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {
                // create announcement user
                $newscoopUserId = $auth->getIdentity();
                $user = $em->getRepository('AHS\AdvertsPluginBundle\Entity\User')->findOneBy(array(
                    'newscoopUserId' => $newscoopUserId
                ));

                if (!$user) {
                    $user = new User();
                    $user->setNewscoopUserId($newscoopUserId);
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
                    $adsService->sendNotificationEmail($request, $user, $announcement);
                }

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
                'form_path' => $this->generateUrl('ahs_advertsplugin_default_add'),
                'type' => 'add',
                'errors' => $errors
            )
        ));
    }

    /**
     * @Route("/classifieds/edit/{id}", requirements={"id" = "\d+"}, options={"expose"=true})
     */
    public function editAction(Request $request, $id = null)
    {
        $templatesService = $this->get('newscoop.templates.service');
        $cacheService = \Zend_Registry::get('container')->get('newscoop.cache');
        $translator = $this->get('translator');

        $auth = \Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) { // ignore for logged user

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
     * @Route("/classifieds/view/{id}/{slug}", requirements={"id" = "\d+"})
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
     * @Route("/classifieds/category/{id}/{slug}")
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
     * @Route("/classifieds/upload_photo", options={"expose"=true})
     */
    public function uploadPhotoAction(Request $request)
    {
        $em = $this->container->get('em');
        $templatesService = $this->get('newscoop.templates.service');
        global $Campsite;

        $auth = \Zend_Auth::getInstance();
        $userId = $auth->getIdentity();
        $user = $em->getRepository('AHS\AdvertsPluginBundle\Entity\User')->findOneBy(
            array(
                'newscoopUserId' => $userId
            )
        );

        $_FILES['file']['name'] = preg_replace('/[^\w\._]+/', '', $_FILES['file']['name']);
        $file = \Plupload::OnMultiFileUploadCustom($Campsite['IMAGE_DIRECTORY']);
        $photo = \Image::ProcessFile(
            $_FILES['file']['name'],
            $_FILES['file']['name'],
            $userId,
            array('Source' => 'ogłoszenia', 'Status' => 'Unapproved', 'Date' => date('Y-m-d'))
        );

        $image = new Image();
        $image->setNewscoopImageId($photo->getImageId());
        $image->setUser($user);

        $em->persist($image);
        $em->flush();

        if (!$request->getSession()->has('announcement_photos')) {
            $request->getSession()->set('announcement_photos', array(array('id' => $image->getId())));
        } else {
            $photos = $request->getSession()->get('announcement_photos', array());
            $photos[] = array('id' => $image->getId());
            $request->getSession()->set('announcement_photos', $photos);
        }

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/_tpl/renderPhotos.tpl',
            array(
                'announcementPhotos' => $this->processPhotos($request)
            )
        ));
    }

    /**
     * @Route("/classifieds/remove_photo", options={"expose"=true})
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
                        'announcementPhotos' => $this->processPhotos($request)
                    )
                ));
            }
        }
    }

    /**
     * @Route("/classifieds/render_photos", options={"expose"=true})
     */
    public function renderPhotosAction(Request $request)
    {
        $em = $this->container->get('em');
        $templatesService = $this->get('newscoop.templates.service');

        return new Response($templatesService->fetchTemplate(
            '_ahs_adverts/_tpl/renderPhotos.tpl',
            array(
                'announcementPhotos' => $this->processPhotos($request)
            )
        ));
    }

    /**
     * @Route("/classifieds/change-status/{id}/{status}", options={"expose"=true})
     * @Method("POST")
     */
    public function changeStatusAction(Request $request, $id, $status)
    {
        $userService = $this->get('user');
        $em = $this->get('em');
        $user = $userService->getCurrentUser();
        $responseStatus = false;
        $announcement = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')->findOneById($id);
        if ($announcement) {
            if ($user->getId() == (int) $announcement->getUser()->getNewscoopUserId()) {
                $announcement->setResult(false);
                if ($status === 'success') {
                    $announcement->setResult(true);
                }

                if (!is_null($request->request->get('announcementStatusComment'))) {
                    $announcement->setComment($request->request->get('announcementStatusComment'));
                }

                $responseStatus = true;
                $em->flush();
            }
        }

        return new JsonResponse(array(
            'status' => $responseStatus
        ));
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
            $newscoopImage = new \Image($photo->getNewscoopImageId());
            $processedPhotos[] = array(
                'id' => $newscoopImage->getImageId(),
                'announcementPhotoId' => $photo->getId(),
                'imageUrl' => $newscoopImage->getImageUrl(),
                'thumbnailUrl' => $newscoopImage->getThumbnailUrl()
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
