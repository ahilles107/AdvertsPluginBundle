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

namespace AHS\AdvertsPluginBundle\EventListener;

use Newscoop\EventDispatcher\Events\CollectObjectsDataEvent;

class ListObjectsListener
{
    /**
     * Register plugin list objects in Newscoop
     *
     * @param  CollectObjectsDataEvent $event
     */
    public function registerObjects(CollectObjectsDataEvent $event)
    {
        $event->registerListObject('ahs\advertspluginbundle\templatelist\announcements', array(
            'class' => 'AHS\AdvertsPluginBundle\TemplateList\Announcements',
            'list' => 'announcements',
            'url_id' => 'ann',
        ));

        $event->registerObjectTypes('announcement', array(
            'class' => '\AHS\AdvertsPluginBundle\Entity\Announcement'
        ));

        $event->registerListObject('ahs\advertspluginbundle\templatelist\categories', array(
            'class' => 'AHS\AdvertsPluginBundle\TemplateList\Categories',
            'list' => 'categories',
            'url_id' => 'ctgr',
        ));

        $event->registerObjectTypes('anouncements_category', array(
            'class' => '\AHS\AdvertsPluginBundle\Entity\Category'
        ));
    }
}
