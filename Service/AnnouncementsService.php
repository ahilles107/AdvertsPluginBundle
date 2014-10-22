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

namespace AHS\AdvertsPluginBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Newscoop\Entity\User;
use AHS\AdvertsPluginBundle\Entity\User as ClassifiedUser;
use AHS\AdvertsPluginBundle\Entity\Announcement;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\Container;

/**
 * Announcements Service
 */
class AnnouncementsService
{
    /** @var Container */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Delete classified by given id
     *
     * @param int|string $id Classified id
     *
     * @return boolean
     */
    public function deleteClassified($id)
    {
        $em = $this->container->get('em');
        $classified = $this->getRepository()->findOneBy(array(
            'id' => $id,
            'removed' => false
        ));

        if ($classified) {
            $classified->setRemoved(true);

            foreach ($classified->getImages() as $image) {
                $this->deleteClassifiedImage($image->getId());
            }

            $em->flush();

            return true;
        }

        return false;
    }

    /**
     * Delete category by given id
     *
     * @param int|string $id Category id
     *
     * @return boolean
     */
    public function deleteCategory($id)
    {
        $em = $this->container->get('em');
        $category = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')
            ->findOneById($id);

        if ($category) {
            $em->remove($category);
            $em->flush();

            return true;
        }

        return false;
    }

    /**
     * Delete classified image by given id
     *
     * @param int|string $id Image id
     *
     * @return boolean
     */
    public function deleteClassifiedImage($id)
    {
        $em = $this->container->get('em');
        $image = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Image')
            ->findOneById($id);

        if ($image) {
            $image->setRemoved(true);
            $em->flush();

            return true;
        }

        return false;
    }

    /**
     * Activate classified by given id
     *
     * @param Announcement $classified Classified
     *
     * @return boolean
     */
    public function activateClassified(Announcement $classified)
    {
        $em = $this->container->get('em');
        $classified->setIsActive(true);
        $classified->setAnnouncementStatus(true);
        $em->flush();

        return true;
    }

    /**
     * Deactivate classified by given id
     *
     * @param Announcement $classified Classified
     *
     * @return boolean
     */
    public function deactivateClassified(Announcement $classified)
    {
        $em = $this->container->get('em');
        $classified->setIsActive(false);
        $classified->setAnnouncementStatus(false);
        $em->flush();

        return true;
    }

    /**
     * Send notification about new classified added
     *
     * @param  Request        $request    Request object
     * @param  ClassifiedUser $user       Classified user
     * @param  Announcement   $classified Announcement
     * @return void
     */
    public function sendNotificationEmail(Request $request, ClassifiedUser $user, Announcement $classified)
    {
        $emailService = $this->container->get('email');
        $router = $this->container->get('router');
        $em = $this->container->get('em');
        $templatesService = $this->container->get('newscoop.templates.service');
        $placeholdersService = $this->container->get('newscoop.placeholders.service');
        $preferencesService = $this->container->get('preferences');
        $smarty = $templatesService->getSmarty();

        $smarty->assign('user', new \MetaUser($user->getNewscoopUser()));
        $smarty->assign('classified', $classified);
        $smarty->assign('created', $classified->getCreatedAt()->format('Y-m-d H:i:s'));
        $smarty->assign('editLink', $request->getUriForPath($router->generate('ahs_advertsplugin_admin_editad', array('id' => $classified->getId()))));

        try {
            $message = $templatesService->fetchTemplate("_ahs_adverts/email_classified_notify.tpl");
        } catch (\Exception $e) {
            throw new NotFoundHttpException("Could not load template: _ahs_adverts/email_classified_notify.tpl");
        }

        $emailService->send($placeholdersService->get('subject'), $message, array($preferencesService->AdvertsNotificationEmail));
    }

    /**
     * Send message to author of given classified
     *
     * @param Announcement $classified Announcement
     * @param array        $params     Extra parameters to compose message
     *
     * @return void
     */
    public function sendMessageToAuthor(Announcement $classified, $params = array())
    {
        $emailService = $this->container->get('email');
        $em = $this->container->get('em');
        $templatesService = $this->container->get('newscoop.templates.service');
        $placeholdersService = $this->container->get('newscoop.placeholders.service');
        $preferencesService = $this->container->get('preferences');
        $smarty = $templatesService->getSmarty();
        $user = $em->getRepository('Newscoop\Entity\User')->findOneById($classified->getUser()->getNewscoopUserId());

        $smarty->assign('user', new \MetaUser($user));
        $smarty->assign('announcement', $classified);
        $smarty->assign('params', $params);

        try {
            $message = $templatesService->fetchTemplate("_ahs_adverts/email_classified_contact.tpl");
        } catch (\Exception $e) {
            throw new NotFoundHttpException("Could not load template: _ahs_adverts/email_classified_contact.tpl");
        }

        $emailService->send($placeholdersService->get('subject'), $message, $user->getEmail(), array($preferencesService->AdvertsNotificationEmail));
    }

    /**
     * Count classifieds by given criteria
     *
     * @param array $criteria
     *
     * @return int
     */
    public function countBy(array $criteria = array())
    {
        return $this->getRepository()->countBy($criteria);
    }

    /**
     * Get repository for announcments entity
     *
     * @return AHS\AdvertsPluginBundle\Repository
     */
    private function getRepository()
    {
        $em = $this->container->get('em');

        return $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement');

    }
}
