<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AHS\AdvertsPluginBundle\Form\CategoryType;
use AHS\AdvertsPluginBundle\Entity\Category;

/**
 * Categories controller
 */
class CategoriesController extends Controller
{
    /**
     * @Route("/admin/announcements/categories")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();

        return array(
            'canEdit' => $user->hasPermission('plugin_classifieds_edit'),
            'canDelete' => $user->hasPermission('plugin_classifieds_delete'),
            'canAccessSettings' => $user->hasPermission('plugin_classifieds_settings'),
            'canAdd' => $user->hasPermission('plugin_classifieds_add'),
        );
    }

    /**
     * @Route("admin/announcements/category/edit/{id}", options={"expose"=true})
     * @Template()
     */
    public function editAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        if ($user->hasPermission('plugin_classifieds_edit')) {
            $category = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')
                ->findOneById($id);

            $form = $this->createForm(new CategoryType(), $category);

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('success', $translator->trans('ads.success.saved'));
                }
            }

            return array(
                'form' => $form->createView(),
                'canAccessSettings' => $user->hasPermission('plugin_classifieds_settings'),
            );
        }

        return $this->redirect($this->generateUrl('ahs_advertsplugin_admin_index'));
    }

    /**
     * @Route("admin/announcements/category/add", options={"expose"=true})
     * @Template()
     */
    public function addAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $translator = $this->get('translator');
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        if ($user->hasPermission('plugin_classifieds_add')) {
            $form = $this->createForm(new CategoryType(), array());

            if ($request->isMethod('POST')) {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $data = $form->getData();
                    $category = new Category();
                    $category->setName($data['name']);
                    $em->persist($category);
                    $em->flush();

                    return new JsonResponse(array('status' => true));
                }
            }

            return new JsonResponse(array('status' => false));
        }

        return $this->redirect($this->generateUrl('ahs_advertsplugin_admin_index'));
    }

    /**
     * Process request parameters
     *
     * @param Request $request Request object
     *
     * @return array
     */
    private function processRequest(Request $request)
    {
        $em = $this->get('em');
        $qb = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Category')
            ->createQueryBuilder('c');

        $qbCount = clone $qb;
        $totalCategories = (int) $qbCount->select('count(c)')->getQuery()->getSingleScalarResult();

        if ($request->query->has('sorts')) {
            foreach ($request->get('sorts') as $key => $value) {
                $qb->orderBy('c.'.$key, $value == '-1' ? 'desc' : 'asc');
            }
        }

        if ($request->query->has('queries')) {
            $queries = $request->query->get('queries');

            if (array_key_exists('search', $queries)) {
                $qb->andWhere('c.name LIKE :name')
                    ->setParameter('name', $queries['search']. '%');
            }
        }

        $qb->setMaxResults($request->query->get('perPage', 10));
        if ($request->query->has('offset')) {
            $qb->setFirstResult($request->query->get('offset'));
        }

        return array($qb->getQuery()->getResult(), $totalCategories);
    }

    /**
     * @Route("/admin/announcements/categories/load", options={"expose"=true})
     * @Template()
     */
    public function loadAction(Request $request)
    {
        $em = $this->get('em');
        $categories = $this->processRequest($request);
        $cacheService = $this->get('newscoop.cache');
        $adsService = $this->get('ahs_adverts_plugin.ads_service');
        $zendRouter = $this->get('zend_router');

        $cacheKey = array('classifieds_categories__'.md5(serialize($categories[0])), $categories[1]);

        if ($cacheService->contains($cacheKey)) {
            $responseArray =  $cacheService->fetch($cacheKey);
        } else {
            $pocessed = array();
            foreach ($categories[0] as $category) {
                $pocessed[] = $this->processCategory($category, $zendRouter);
            }

            $responseArray = array(
                'records' => $pocessed,
                'queryRecordCount' => $categories[1],
                'totalRecordCount'=> count($categories[0])
            );

            $cacheService->save($cacheKey, $responseArray);
        }

        return new JsonResponse($responseArray);
    }

    /**
     * @Route("admin/announcements/category/delete/{id}", options={"expose"=true})
     */
    public function deleteAction(Request $request, $id)
    {
        $userService = $this->get('user');
        $user = $userService->getCurrentUser();
        if ($user->hasPermission('plugin_classifieds_delete')) {
            $adsService = $this->get('ahs_adverts_plugin.ads_service');

            return new JsonResponse(array('status' => $adsService->deleteCategory($id)));
        }

        return $this->redirect($this->generateUrl('ahs_advertsplugin_admin_index'));
    }

    /**
     * Process single category
     *
     * @param Category    $category   Category
     * @param Zend_Router $zendRouter Zend Router
     *
     * @return array
     */
    private function processCategory($category, $zendRouter)
    {
        $em = $this->get('em');

        $countByCategory = $em->getRepository('AHS\AdvertsPluginBundle\Entity\Announcement')
            ->createQueryBuilder('c')
            ->select('count(c)')
            ->where('c.category = :category')
            ->setParameter('category', $category->getId())
            ->getQuery()
            ->getSingleScalarResult();

        return array(
            'id' => $category->getId(),
            'name' => $category->getName(),
            'announcements' => $countByCategory,
            'created' => $category->getCreatedAt(),
            'links' => array(
                array(
                    'rel' => 'edit',
                    'href' => ""
                ),
                array(
                    'rel' => 'delete',
                    'href' => ""
                ),
            )
        );
    }
}
