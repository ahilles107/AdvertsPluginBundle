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
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 */

namespace AHS\AdvertsPluginBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AHS\AdvertsPluginBundle\TemplateList\CategoryCriteria;
use Newscoop\ListResult;

/**
 * CategoriesRepository
 */
class CategoriesRepository extends EntityRepository
{
    /**
     * Get list for given criteria
     *
     * @param AHS\AdvertsPluginBundle\TemplateList\CategoryCriteria $criteria
     *
     * @return Newscoop\ListResult
     */
    public function getListByCriteria(CategoryCriteria $criteria, $showResults = true)
    {
        $qb = $this->createQueryBuilder('c');
        $list = new ListResult();

        foreach ($criteria->perametersOperators as $key => $operator) {
            $qb->andWhere('c.'.$key.' '.$operator.' :'.$key)
                ->setParameter($key, $criteria->$key);
        }

        $countQb = clone $qb;
        $list->count = (int) $countQb->select('COUNT(DISTINCT c)')->getQuery()->getSingleScalarResult();

        if ($criteria->firstResult != 0) {
            $qb->setFirstResult($criteria->firstResult);
        }

        if ($criteria->maxResults != 0) {
            $qb->setMaxResults($criteria->maxResults);
        }

        $metadata = $this->getClassMetadata();
        foreach ($criteria->orderBy as $key => $order) {
            if (array_key_exists($key, $metadata->columnNames)) {
                $key = 'c.' . $key;
            }

            $qb->orderBy($key, $order);
        }

        if (!$showResults) {
            return $qb->getQuery();
        }

        $list->items = $qb->getQuery()->getResult();

        return $list;
    }
}
