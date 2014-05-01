<?php
/**
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\Serializer;

use JMS\Serializer\JsonSerializationVisitor;

/**
 * Create url for anouncement.
 */
class AnnouncementUrlHandler
{
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    public function serializeToJson(JsonSerializationVisitor $visitor, $data, $type)
    {
        $route  = $this->router->generate('ahs_advertsplugin_default_show', array('id' => $data->getId(), 'slug' => $data->getSlug()), true);

        return $route;
    }
}
