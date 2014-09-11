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
 * Announcements List
 */
class AnnouncementsList extends PaginatedBaseList
{
    protected function prepareList($criteria, $parameters)
    {
        $em = \Zend_Registry::get('container')->get('em');
        // display only activated
        $criteria->status = array(true);
        $queryBuilder = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->getListByCriteria($criteria, false);

        $list = $this->paginateList($queryBuilder, null, $criteria->maxResults);
        foreach ($list->items as $key => $item) {
            // get reference to announcement when user object is empty
            // this will fill user property
            if ($item->getUser()->getId() == 0) {
                $list->items[$key] = $em->getReference('AHS\AdvertsPluginBundle\Entity\Announcement', $item->getId());
            }
        }

        return $list;
    }

    protected function convertParameters($firstResult, $parameters)
    {
        parent::convertParameters($firstResult, $parameters);

        // show only announcements from last x days
        if (array_key_exists('lastDays', $parameters)) {
            if (is_numeric($parameters['lastDays'])) {
                $date = new \DateTime();
                $date->modify('- '.$parameters['lastDays'].' days');
                $this->criteria->perametersOperators['created_at'] = '>=';
                $this->criteria->created_at = $date->format('Y-m-d').' 00:00:00';
            }
        }

        if (array_key_exists('withImages', $parameters)) {
            $this->criteria->withImages = $parameters['withImages'];
        }
    }
}
