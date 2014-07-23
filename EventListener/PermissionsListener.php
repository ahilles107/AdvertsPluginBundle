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
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 */

namespace AHS\AdvertsPluginBundle\EventListener;

use Newscoop\EventDispatcher\Events\PluginPermissionsEvent;
use Symfony\Component\Translation\Translator;

class PermissionsListener
{
    /**
     * Translator
     * @var Translator
     */
    protected $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Register plugin permissions in Newscoop ACL
     *
     * @param PluginPermissionsEvent $event
     */
    public function registerPermissions(PluginPermissionsEvent $event)
    {
        $event->registerPermissions($this->translator->trans('ads.menu.name'), array(
            'plugin_classifieds_edit' => $this->translator->trans('ads.permissions.edit'),
            'plugin_classifieds_settings' => $this->translator->trans('ads.permissions.settings'),
            'plugin_classifieds_activate' => $this->translator->trans('ads.permissions.activate'),
            'plugin_classifieds_deactivate' => $this->translator->trans('ads.permissions.deactivate'),
            'plugin_classifieds_delete' => $this->translator->trans('ads.permissions.delete'),
            'plugin_classifieds_add' => $this->translator->trans('ads.permissions.add'),
            'plugin_classifieds_access' => $this->translator->trans('ads.permissions.access'),
        ));
    }
}
