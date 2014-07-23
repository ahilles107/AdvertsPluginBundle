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

namespace AHS\AdvertsPluginBundle\TemplateList;

use Newscoop\TemplateList\PaginatedBaseList;

/**
 * Categories List
 */
class CategoriesList extends PaginatedBaseList
{
    protected function prepareList($criteria, $parameters)
    {
        $em = \Zend_Registry::get('container')->get('em');
        // display only activated
        $criteria->status = array(true);
        $queryBuilder = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')
            ->getListByCriteria($criteria, false);

        return $this->paginateList($queryBuilder, null, $criteria->maxResults);
    }
}
