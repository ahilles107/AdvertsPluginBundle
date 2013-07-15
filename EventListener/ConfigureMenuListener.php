<?php
/**
 * @package Newscoop\ExamplePluginBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;

class ConfigureMenuListener
{
    /**
     * @param \Newscoop\NewscoopBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        $menu[getGS('Plugins')]->addChild(
            getGS('Anouncements'), 
            array('uri' => $event->getRouter()->generate('ahs_advertsplugin_admin_index'))
        );
    }
}