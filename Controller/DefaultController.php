<?php

namespace AHS\AdvertsPluginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/ogloszenia")
     */
    public function indexAction(Request $request)
    {
    	$this->get('dispatcher')->notify('plugin.install', new \Newscoop\EventDispatcher\Events\GenericEvent($this, array(
           'plugin_name' => ''
        )));

        return $this->render('AHSAdvertsPluginBundle:Default:index.html.smarty');
    }
}
