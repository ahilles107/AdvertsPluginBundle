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
     * Announcements construct
     *
     * @param EntityManager            $em                 Entity Manager
     * @param EmailService             $emailService       Email Service
     * @param SystemPreferencesService $preferencesService System Preferences Service
     */
	public function __construct(EntityManager $em, EmailService $emailService, TemplatesService $templatesService,
        PlaceholdersService $placeholdersService, SystemPreferencesService $preferencesService)
	{
		$this->em = $em;
        $this->emailService = $emailService;
        $this->preferencesService = $preferencesService;
        $this->templatesService = $templatesService;
        $this->placeholdersService = $placeholdersService;
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

    public function sendNotificationEmail(User $user)
    {
        $smarty = $this->templatesService->getSmarty();
        $smarty->assign('user', new \MetaUser($user));

        $message = $this->templatesService->fetchTemplate("email_membership_staff.tpl");
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
