<?php

/*
 * This file is part of the Adverts Plugin.
 *
 * (c) Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AHS\AdvertsPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AHS\AdvertsPluginBundle\Entity\Announcement;
use AHS\AdvertsPluginBundle\Form\AnnouncementType;
use AHS\AdvertsPluginBundle\Form\FrontAnnouncementType;
use AHS\AdvertsPluginBundle\Entity\User;
use AHS\AdvertsPluginBundle\Entity\Image;

class DefaultController extends Controller
{
    /**
     * @Route("/contact/send-message/{id}")
     */
    public function indexAction(Request $request)
    {
        $templatesService = $this->get('newscoop.templates.service');
        $emailService = $this->get('email');

        return new JsonResponse(array());
    }
}
