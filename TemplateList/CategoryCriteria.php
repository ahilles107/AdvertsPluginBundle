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
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 */

namespace AHS\AdvertsPluginBundle\TemplateList;

use Newscoop\Criteria;

/**
 * Available criteria for categories listing.
 */
class CategoryCriteria extends Criteria
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
     * @var \DateTime
     */
    public $created_at;

    /**
     * @var array
     */
    public $orderBy = array('created_at' => 'desc');
}
