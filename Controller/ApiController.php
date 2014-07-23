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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends FOSRestController
{
    /**
     * @Route("/api/announcements.{_format}", defaults={"_format"="json"}, name="newscoop_gimme_get_latest_anouncements")
     * @Method("GET")
     * @View()
     */
    public function latestAnouncementsAction(Request $request)
    {
        $em = $this->container->get('em');
        
        $publicationService = $this->container->get('newscoop.publication_service');
        $publicationId = $publicationService->getPublication()->getId();

        if ($request->query->has('publication') && is_numeric($request->query->get('publication'))) {
            $publicationId = $request->query->get('publication');
        }

        $latestAnnouncements = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->createQueryBuilder('a')
            ->andWhere('a.is_active = true')
            ->andWhere('a.publication >= :publicationId')
            ->orderBy('a.created_at', 'DESC')
            ->setParameters(
                array(
                    'publicationId' => $publicationId
                )
            )
            ->getQuery();

        $paginator = $this->get('newscoop.paginator.paginator_service');
        $latestAnnouncements = $paginator->paginate(
            $latestAnnouncements,
            array(
                'distinct' => false
            )
        );

        return $latestAnnouncements;
    }
}
