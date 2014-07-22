<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Newscoop\Services\EmailService;
use Newscoop\Entity\User;
use Newscoop\NewscoopBundle\Services\SystemPreferencesService;
use Newscoop\Services\TemplatesService;
use Newscoop\Services\PlaceholdersService;
use AHS\AdvertsPluginBundle\Entity\User as ClassifiedUser;
use AHS\AdvertsPluginBundle\Entity\Announcement;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Announcements Service
 */
class AnnouncementsService
{
	/**
	 * @var EntityManager
	 */
	protected $em;

    /**
     * @var EmailService
     */
    protected $emailService;

    /**
     * @var TemplatesService
     */
    protected $templatesService;

    /**
     * @var PlaceholdersService
     */
    protected $placeholdersService;

    /**
     * @var SystemPreferencesService
     */
    protected $preferencesService;

    /**
     * @var Router
     */
    protected $router;

	/**
     * Construct
     *
     * @param EntityManager            $em                  Entity Manager
     * @param EmailService             $emailService        Email Service
     * @param TemplatesService         $templatesService    Templates Service
     * @param PlaceholdersService      $placeholdersService Placeholder Service
     * @param SystemPreferencesService $preferencesService  System Preferences
     * @param Router                   $router              Router
     */
	public function __construct(EntityManager $em, EmailService $emailService, TemplatesService $templatesService,
        PlaceholdersService $placeholdersService, SystemPreferencesService $preferencesService, Router $router)
	{
		$this->em = $em;
        $this->emailService = $emailService;
        $this->preferencesService = $preferencesService;
        $this->templatesService = $templatesService;
        $this->placeholdersService = $placeholdersService;
        $this->router = $router;
	}

    /**
     * Delete classified by given id
     *
     * @param  int|string $id Classified id
     *
     * @return boolean
     */
    public function deleteClassified($id)
    {
        $classified = $this->getRepository()
            ->findOneById($id);

        if ($classified) {
            $this->em->remove($classified);
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * Delete category by given id
     *
     * @param  int|string $id Category id
     *
     * @return boolean
     */
    public function deleteCategory($id)
    {
        $category = $this->em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')
            ->findOneById($id);

        if ($category) {
            $this->em->remove($category);
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * Delete classified image by given id
     *
     * @param  int|string $id Image id
     *
     * @return boolean
     */
    public function deleteClassifiedImage($id)
    {
        $image = $this->em->getRepository('AHS\AdvertsPluginBundle\Entity\Image')
            ->findOneById($id);

        if ($image) {
            $image->preRemoveHandler();
            $this->em->remove($image);
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * Activate classified by given id
     *
     * @param  int|string $id Classified id
     *
     * @return boolean
     */
    public function activateClassified($id)
    {
        $classified = $this->em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->findOneById($id);

        if ($classified) {
            $classified->setIsActive(true);
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * Deactivate classified by given id
     *
     * @param  int|string $id Classified id
     *
     * @return boolean
     */
    public function deactivateClassified($id)
    {
        $classified = $this->em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->findOneById($id);

        if ($classified) {
            $classified->setIsActive(false);
            $this->em->flush();

            return true;
        }

        return false;
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
        $smarty = $this->templatesService->getSmarty();
        $user = $this->em->getRepository('Newscoop\Entity\User')
            ->findOneBy(array('id' => $user->getNewscoopUserId()));

        $smarty->assign('user', new \MetaUser($user));
        $smarty->assign('classified', $classified);
        $smarty->assign('created', $classified->getCreatedAt()->format('Y-m-d H:i:s'));
        $smarty->assign('editLink', $request->getUriForPath($this->router->generate('ahs_advertsplugin_admin_editad', array('id' => $classified->getId()))));

        try {
            $message = $this->templatesService->fetchTemplate("_ahs_adverts/email_classified_notify.tpl");
        } catch (\Exception $e) {
            throw new NotFoundHttpException("Could not load template: _ahs_adverts/email_classified_notify.tpl");
        }

        $this->emailService->send($this->placeholdersService->get('subject'), $message, array($this->preferencesService->AdvertsNotificationEmail));
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
         return $this->em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement');

    }
}
