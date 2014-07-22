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
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
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
    }
}
