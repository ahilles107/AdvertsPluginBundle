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
	 * Announcements construct
	 *
	 * @param EntityManager $em Entity Manager
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
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
