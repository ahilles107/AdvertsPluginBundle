<?php
/**
 * @package Newscoop\ExamplePluginBundle
 * @author Paweł Mikołajczuk <pawel.mikolajczuk@sourcefabric.org>
 * @copyright 2012 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Newscoop\EventDispatcher\Events\GenericEvent;

/**
 * Event lifecycle management
 */
class LifecycleSubscriber implements EventSubscriberInterface
{
    private $em;

    private $pluginsService;

    public function __construct($em, $pluginsService)
    {
        $this->em = $em;
        $this->pluginsService = $pluginsService;
    }

    public function install(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
        $this->setPermissions();
    }

    public function update(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->updateSchema($this->getClasses(), true);

        $this->em->getProxyFactory()->generateProxyClasses($this->getClasses(), __DIR__ . '/../../../../library/Proxy');
        $this->setPermissions();
    }

    public function remove(GenericEvent $event)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->em);
        $tool->dropSchema($this->getClasses(), true);
    }

    /**
     * Collect plugin permissions
     */
    private function setPermissions()
    {
        $this->pluginsService->savePluginPermissions($this->pluginsService->collectPermissions());
    }

    public static function getSubscribedEvents()
    {
        return array(
            'plugin.install.ahs_adverts_plugin_bundle' => array('install', 1),
            'plugin.update.ahs_adverts_plugin_bundle' => array('update', 1),
            'plugin.remove.ahs_adverts_plugin_bundle' => array('remove', 1),
        );
    }

    private function getClasses()
    {
        return array(
          $this->em->getClassMetadata('AHS\AdvertsPluginBundle\Entity\Announcement'),
          $this->em->getClassMetadata('AHS\AdvertsPluginBundle\Entity\Category'),
          $this->em->getClassMetadata('AHS\AdvertsPluginBundle\Entity\Image'),
          $this->em->getClassMetadata('AHS\AdvertsPluginBundle\Entity\User'),
        );
    }
}
