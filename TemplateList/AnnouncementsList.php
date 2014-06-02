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
            ->getListByCriteria($criteria, false);

        return $this->paginateList($queryBuilder, null, $criteria->maxResults);
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
