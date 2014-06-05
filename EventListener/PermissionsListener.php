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
        ));
    }
}
