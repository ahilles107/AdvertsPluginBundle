<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AHS\AdvertsPluginBundle\TemplateList\AnnouncementCriteria;
use Newscoop\ListResult;

/**
 * AnnouncementRepository
 */
class AnnouncementRepository extends EntityRepository
{
    /**
     * Get list for given criteria
     *
     * @param AHS\AdvertsPluginBundle\TemplateList\AnnouncementCriteria $criteria
     *
     * @return Newscoop\ListResult
     */
    public function getListByCriteria(AnnouncementCriteria $criteria)
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('a, c, i')
            ->andWhere('a.is_active = true')
            ->leftJoin('a.category', 'c')
            ->leftJoin('a.images', 'i');

        foreach ($criteria->perametersOperators as $key => $operator) {
            $qb->andWhere('a.'.$key.' = :'.$key)
                ->setParameter($key, $criteria->$key);
        }

        $metadata = $this->getClassMetadata();
        foreach ($criteria->orderBy as $key => $order) {
            if (array_key_exists($key, $metadata->columnNames)) {
                $key = 'a.' . $key;
            }

            $qb->orderBy($key, $order);
        }
        $query = $qb->getQuery();

        return $query;
    }
}