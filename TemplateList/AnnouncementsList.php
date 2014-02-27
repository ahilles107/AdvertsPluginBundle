<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 * @copyright 2014 Paweł Mikołajczuk
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\TemplateList;

use Newscoop\ListResult;
use Newscoop\TemplateList\PaginatedBaseList;

/**
 * Announcements List
 */
class AnnouncementsList extends PaginatedBaseList
{
    protected function prepareList($criteria, $parameters)
    {
        $em = \Zend_Registry::get('container')->get('em');
        $queryBuilder = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->getListByCriteria($criteria);
        $list = $this->paginateList($queryBuilder, null, $criteria->maxResults);

        return $list;
    }

    protected function convertParameters($firstResult, $parameters)
    {
        parent::convertParameters($firstResult, $parameters);
    }
}
