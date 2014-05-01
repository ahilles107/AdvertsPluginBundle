<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 * @copyright 2014 Paweł Mikołajczuk
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\TemplateList;

use Newscoop\Criteria;

/**
 * Available criteria for announcement listing.
 */
class AnnouncementCriteria extends Criteria
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $category;

    /**
     * @var int
     */
    public $user;

    /**
     * @var int
     */
    public $publication;

    /**
     * @var boolean
     */
    public $price;

    /**
     * @var int
     */
    public $reads;

    /**
     * @var \DateTime
     */
    public $created_at;

    /**
     * @var array
     */
    public $orderBy = array('created_at' => 'desc');

    /**
     * @var boolean
     */
    public $withImages;
}
