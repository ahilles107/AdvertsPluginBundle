<?php
/**
 * @package Newscoop\ExamplePluginBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Translation\Translator;

class ConfigureMenuListener
{
    private $translator;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param \Newscoop\NewscoopBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();
        $labelPlugins = $this->translator->trans('Plugins');
        $labelPluginName = $this->translator->trans('ads.menu.name');
        $menu[$labelPlugins]->addChild(
            $this->translator->trans('ads.menu.name'),
            array('uri' => $event->getRouter()->generate('ahs_advertsplugin_admin_index'))
        );

        $menu[$labelPlugins][$labelPluginName]->addChild(
            $this->translator->trans('ads.menu.settings'),
            array('uri' => $event->getRouter()->generate('ahs_advertsplugin_admin_settings')
        ));
        $menu[$labelPlugins][$labelPluginName][$this->translator->trans('ads.menu.settings')]->setDisplay(false);

        $menu[$labelPlugins][$labelPluginName]->addChild(
            $this->translator->trans('ads.menu.categories'),
            array('uri' => $event->getRouter()->generate('ahs_advertsplugin_categories_index')
        ));
        $menu[$labelPlugins][$labelPluginName][$this->translator->trans('ads.menu.categories')]->setDisplay(false);
    }
}