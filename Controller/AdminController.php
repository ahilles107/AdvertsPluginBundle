<?php

namespace AHS\AdvertsPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * TODO:
 */
class AdminController extends Controller
{
    /**
     * @Route("/admin/anouncements")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->container->get('em');

        $validDate = new \DateTime();
        $validDate->modify('-14 days');

        $categories = $this->getCategories();
        $latestAnnouncements = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->createQueryBuilder('a')
            ->andWhere('a.is_active = true')
            ->andWhere('a.created_at >= :validDate')
            ->orderBy('a.created_at', 'DESC')
            ->setParameters(array('validDate' => $validDate))
            ->getQuery();

        $paginatorService = $this->container->get('newscoop.paginator.paginator_service');

        $paginator  = $this->container->get('knp_paginator');
        $latestAnnouncements = $paginator->paginate(
            $latestAnnouncements,
            $this->get('request')->get('knp_page', 1),
            10
        );

        return array(
            'latestAnnouncements' => $latestAnnouncements
        );
    }

    private function getCategories()
    {
        $em = $this->container->get('em');

        return $latestAnnouncements = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')
            ->createQueryBuilder('c')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
