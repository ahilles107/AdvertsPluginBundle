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
 * @package Newscoop\ExamplePluginBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\EventListener;

use Newscoop\NewscoopBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Translation\Translator;
use Newscoop\Services\UserService;

class ConfigureMenuListener
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var UserService;
     */
    private $userService;

    /**
     * @param Translator $translator
     */
    public function __construct(Translator $translator, UserService $userService)
    {
        $this->translator = $translator;
        $this->userService = $userService;
    }

    /**
     * @param \Newscoop\NewscoopBundle\Event\ConfigureMenuEvent $event
     */
    public function onMenuConfigure(ConfigureMenuEvent $event)
    {
        $user = $this->userService->getCurrentUser();
        if ($user->hasPermission('plugin_classifieds_access')) {
            $menu = $event->getMenu();
            $labelPlugins = $this->translator->trans('Content');
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
}
