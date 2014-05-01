<?php

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
     * @Route("/api/anouncements.{_format}", defaults={"_format"="json"}, name="newscoop_gimme_get_latest_anouncements")
     * @Method("GET")
     * @View()
     */
    public function latestAnouncementsAction(Request $request)
    {
        $em = $this->container->get('em');

        $validDate = new \DateTime();
        $validDate->modify('-100 days');

        $publicationService = $this->container->get('newscoop.publication_service');
        $publicationId = $publicationService->getPublication()->getId();

        if ($request->query->has('publication') && is_numeric($request->query->get('publication'))) {
            $publicationId = $request->query->get('publication');
        }

        $categories = $this->getCategories();
        $latestAnnouncements = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->createQueryBuilder('a')
            ->andWhere('a.is_active = true')
            ->andWhere('a.created_at >= :validDate')
            ->andWhere('a.publication >= :publicationId')
            ->orderBy('a.created_at', 'DESC')
            ->setParameters(array(
                'validDate' => $validDate,
                'publicationId' => $publicationId
            ))
            ->getQuery();

        $paginator = $this->get('newscoop.paginator.paginator_service');
        $latestAnnouncements = $paginator->paginate($latestAnnouncements, array(
            'distinct' => false
        ));

        return $latestAnnouncements;
    }
}
