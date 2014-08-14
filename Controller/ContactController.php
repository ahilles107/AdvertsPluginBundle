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

namespace AHS\AdvertsPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AHS\AdvertsPluginBundle\Form\ContactType;
use Newscoop\EventDispatcher\Events\GenericEvent;

class ContactController extends Controller
{
    /**
     * @Route("/classifieds/send-message/{id}", options={"expose"=true})
     * @Method("POST")
     */
    public function indexAction(Request $request, $id)
    {
        $templatesService = $this->get('newscoop.templates.service');
        $emailService = $this->get('email');
        $em = $this->get('em');
        $status = false;

        try {
            $form = $this->createForm(new ContactType(), array());
            $announcement = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')->findOneById($id);
            if ($announcement) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $this->get('dispatcher')->dispatch('classifieds.modified', new GenericEvent($this, array(
                        'announcement' => $announcement,
                        'contact' => $form->getData()
                    )));

                    $status = true;
                }
            }
        } catch (\Exception $e) {
            $status = false;
        }

        return new JsonResponse(array('status' => $status));
    }
}
