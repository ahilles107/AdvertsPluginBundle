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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AHS\AdvertsPluginBundle\TemplateList\AnnouncementCriteria;
use AHS\AdvertsPluginBundle\Entity\Announcement;
use AHS\AdvertsPluginBundle\Form\AnnouncementType;
use AHS\AdvertsPluginBundle\Form\SettingsType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Newscoop\EventDispatcher\Events\GenericEvent;

/**
 * Admin controller
 */
class AdminController extends Controller
{
    /**
     * @Route("/admin/announcements")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        if (!$user->hasPermission('plugin_classifieds_access')) {
            throw new AccessDeniedException();
        }

        if ($user->hasPermission('plugin_classifieds_access')) {
            $em = $this->container->get('em');
            $adsService = $this->get('ahs_adverts_plugin.ads_service');
            $categories = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')->findAll();

            return array(
                'categories' => $categories,
                'allAdsCount' => $adsService->countBy(),
                'activeAdsCount' => $adsService->countBy(array('is_active' => true)),
                'inactiveAdsCount' => $adsService->countBy(array('is_active' => false)),
            );
        }
    }

    /**
     * @Route("admin/announcements/load/", options={"expose"=true})
     */
    public function loadAdsAction(Request $request)
    {
        $em = $this->get('em');
        $cacheService = $this->get('newscoop.cache');
        $adsService = $this->get('ahs_adverts_plugin.ads_service');
        $zendRouter = $this->get('zend_router');
        $userService = $this->get('user');
        $translator = $this->get('translator');
        $user = $userService->getCurrentUser();

        if (!$user->hasPermission('plugin_classifieds_access')) {
            throw new AccessDeniedException();
        }

        $criteria = $this->processRequest($request);
        $adsCount = $adsService->countBy(array('is_active' => true));
        $adsInactiveCount = $adsService->countBy(array('is_active' => false));

        $cacheKey = array('classifieds__'.md5(serialize($criteria)), $adsCount, $adsInactiveCount);

        if ($cacheService->contains($cacheKey)) {
            $responseArray = $cacheService->fetch($cacheKey);
        } else {
            $ads = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')->getListByCriteria($criteria);

            $pocessed = array();
            foreach ($ads as $ad) {
                $pocessed[] = $this->processAd($ad, $zendRouter);
            }

            $responseArray = array(
                'records' => $pocessed,
                'queryRecordCount' => $ads->count,
                'totalRecordCount'=> count($ads->items)
            );

            $cacheService->save($cacheKey, $responseArray);
        }

        return new JsonResponse($responseArray);
    }

    /**
     * @Route("admin/announcements/delete/{id}", options={"expose"=true})
     */
    public function deleteAdAction(Request $request, $id)
    {
        $userService = $this->get('user');
        $translator = $this->get('translator');
        $user = $userService->getCurrentUser();
        if (!$user->hasPermission('plugin_classifieds_delete') || !$user->hasPermission('plugin_classifieds_access')) {
            throw new AccessDeniedException();
        }

        $adsService = $this->get('ahs_adverts_plugin.ads_service');

        return new JsonResponse(array(
            'status' => $adsService->deleteClassified($id)
        ));
    }

    /**
     * @Route("admin/announcements/delete/image/{id}", options={"expose"=true})
     */
    public function deleteImageAction(Request $request, $id)
    {
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        $translator = $this->get('translator');
        if (!$user->hasPermission('plugin_classifieds_delete') || !$user->hasPermission('plugin_classifieds_access')) {
            throw new AccessDeniedException();
        }

        $adsService = $this->get('ahs_adverts_plugin.ads_service');

        return new JsonResponse(array(
            'status' => $adsService->deleteClassifiedImage($id)
        ));
    }

    /**
     * @Route("admin/announcements/edit/{id}", options={"expose"=true})
     * @Template()
     */
    public function editAdAction(Request $request, $id = null)
    {
        $userService = $this->get('user');
        $translator = $this->get('translator');
        $user = $userService->getCurrentUser();
        $em = $this->getDoctrine()->getManager();

        if (!$user->hasPermission('plugin_classifieds_edit') || !$user->hasPermission('plugin_classifieds_access')) {
            throw new AccessDeniedException();
        }

        $translator = $this->get('translator');
        $classified = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->findOneById($id);

        if ($classified->getValidTo() < $classified->getCreatedAt()) {
            $classified->setValidTo(new \DateTime());
        }

        $form = $this->createForm(new AnnouncementType(), $classified, array('translator' => $translator));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $translator->trans('ads.success.saved'));
            }
        }

        $images = array();
        $isEmpty = $classified->getImages()->isEmpty();
        if ($isEmpty) {
            $images[] = $classified->getFirstImage(true);
        } else {
            foreach ($classified->getImages() as $image) {
                $images[] = $classified->processImage($image);
            }
        }

        return array(
            'form' => $form->createView(),
            'images' => $images,
            'isEmpty' => $isEmpty,
        );
    }

    /**
     * @Route("admin/announcements/activate/{id}", options={"expose"=true})
     */
    public function activateAction(Request $request, $id)
    {
        try {
            $userService = $this->get('user');
            $user = $userService->getCurrentUser();
            $translator = $this->get('translator');
            $em = $this->get('em');
            $status = true;

            if (!$user->hasPermission('plugin_classifieds_activate') || !$user->hasPermission('plugin_classifieds_access')) {
                throw new AccessDeniedException();
            }

            $classified = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
                ->findOneById($id);

            $this->get('dispatcher')->dispatch('classifieds.modified', new GenericEvent($this, array(
                'announcement' => $classified,
                'status' => true
            )));
        } catch (\Exception $e) {
            $status = false;
        }

        return new JsonResponse(array(
            'status' => $status
        ));
    }

    /**
     * @Route("admin/announcements/deactivate/{id}", options={"expose"=true})
     */
    public function deactivateAction(Request $request, $id)
    {
        try {
            $userService = $this->get('user');
            $user = $userService->getCurrentUser();
            $translator = $this->get('translator');
            $em = $this->get('em');
            $status = true;

            if (!$user->hasPermission('plugin_classifieds_deactivate') || !$user->hasPermission('plugin_classifieds_access')) {
                throw new AccessDeniedException();
            }

            $classified = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
                ->findOneById($id);

            $this->get('dispatcher')->dispatch('classifieds.modified', new GenericEvent($this, array(
                'announcement' => $classified,
                'status' => false
            )));
        } catch (\Exception $e) {
            $status = false;
        }

        return new JsonResponse(array(
            'status' => $status
        ));
    }

    /**
     * @Route("admin/announcements/settings", options={"expose"=true})
     * @Template()
     */
    public function settingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');
        $systemPreferences = $this->get('system_preferences_service');
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();

        if (!$user->hasPermission('plugin_classifieds_settings') || !$user->hasPermission('plugin_classifieds_access')) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(new SettingsType(), array(
            'notificationEmail' => $systemPreferences->AdvertsNotificationEmail,
            'review' => $systemPreferences->AdvertsReviewStatus == "1" ? true : false,
            'valid_time' => $systemPreferences->AdvertsValidTime,
            'enableNotify' => $systemPreferences->AdvertsEnableNotify == "1" ? true : false,
            'maxClassifieds' => $systemPreferences->AdvertsMaxClassifiedsPerUser,
        ));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $systemPreferences->AdvertsNotificationEmail = $data['notificationEmail'];
                $systemPreferences->AdvertsReviewStatus = $data['review'];
                $systemPreferences->AdvertsValidTime = $data['valid_time'];
                $systemPreferences->AdvertsEnableNotify = $data['enableNotify'];
                $systemPreferences->AdvertsMaxClassifiedsPerUser = $data['maxClassifieds'];

                $this->get('session')->getFlashBag()->add('success', $translator->trans('ads.success.saved'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * Process request parameters
     *
     * @param Request $request Request object
     *
     * @return AnnouncementCriteria
     */
    private function processRequest(Request $request)
    {
        $criteria = new AnnouncementCriteria();

        if ($request->query->has('sorts')) {
            foreach ($request->get('sorts') as $key => $value) {
                $criteria->orderBy[$key] = $value == '-1' ? 'desc' : 'asc';
            }
        }

        if ($request->query->has('queries')) {
            $queries = $request->query->get('queries');

            if (array_key_exists('search', $queries)) {
                $criteria->query = $queries['search'];
            }

            if (array_key_exists('filter', $queries)) {
                if ($queries['filter'] === 'all') {
                    $criteria->category = 'all';
                } else {
                    $criteria->category = $queries['filter'];
                }
            }

            if (array_key_exists('ad-status', $queries)) {
                foreach ($queries['ad-status'] as $key => $value) {
                    $criteria->status[$key] = $value;
                }
            }
        }

        $criteria->maxResults = $request->query->get('perPage', 10);
        if ($request->query->has('offset')) {
            $criteria->firstResult = $request->query->get('offset');
        }

        return $criteria;
    }

    /**
     * Process single ad
     *
     * @param Announcement $ad         Announcement
     * @param Zend_Router  $zendRouter Zend Router
     *
     * @return array
     */
    private function processAd(Announcement $ad, $zendRouter)
    {
        $em = $this->get('em');
        $user = $em->getRepository('Newscoop\Entity\User')
            ->findOneBy(array('id' => $ad->getUser()->getNewscoopUserId()));

        $image = $ad->getFirstImage();

        return array(
            'id' => $ad->getId(),
            'name' => $ad->getName(),
            'thumbnailUrl' => $image['thumbnailUrl'],
            'description' => $ad->getDescription(),
            'publication' => $ad->getPublication()->getName(),
            'price' => $ad->getPrice(),
            'reads' => $ad->getReads(),
            'username' => array(
                'href' => $zendRouter->assemble(array(
                    'module' => 'admin',
                    'controller' => 'user',
                    'action' => 'edit',
                    'user' => $user->getId(),
                ), 'default', true),
                'username' => $user->getUsername(),
            ),
            'created' => $ad->getCreatedAt(),
            'valid_to' => $ad->getValidTo(),
            'status' => $ad->getIsActive(),
            'links' => array(
                array(
                    'rel' => 'edit',
                    'href' => ""
                ),
                array(
                    'rel' => 'activate',
                    'href' => $this->generateUrl('ahs_advertsplugin_admin_activate', array('id' => $ad->getId()))
                ),
                array(
                    'rel' => 'deactivate',
                    'href' => $this->generateUrl('ahs_advertsplugin_admin_deactivate', array('id' => $ad->getId()))
                ),
                array(
                    'rel' => 'delete',
                    'href' => ""
                ),
            )
        );
    }

    private function getCategories()
    {
        $em = $this->container->get('em');

        return $latestAnnouncements = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')
            ->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
