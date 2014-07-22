<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
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
