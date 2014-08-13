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

use Newscoop\EventDispatcher\Events\GenericEvent;
use AHS\AdvertsPluginBundle\Service\AnnouncementsService;

class ClassifiedsModifiedListener
{
    /**
     * Classifieds service
     * @var AnnouncementsService
     */
    protected $adsService;

    public function __construct(AnnouncementsService $adsService)
    {
        $this->adsService = $adsService;
    }

    /**
     * Trigger actions on Classified event
     *
     * @param ClassifiedsEvent $event
     */
    public function onClassifiedEvent(GenericEvent $event)
    {
        $params = $event->getArguments();
        $announcement = $params['announcement'];
        if (isset($announcement)) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'notification':
                        $this->adsService->sendNotificationEmail($value[0], $value[1], $announcement);
                        break;
                    case 'status':
                        if ($value) {
                            $this->adsService->activateClassified($announcement);
                        } else {
                            $this->adsService->deactivateClassified($announcement);
                        }

                        break;
                    case 'contact':
                        $this->adsService->sendMessageToAuthor($announcement, $value);
                        break;

                    default:
                        break;
                }
            }
        }
    }
}
