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

namespace AHS\AdvertsPluginBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use AHS\AdvertsPluginBundle\Entity\Announcement;

class AddAnnouncement
{
    /**
     * @Assert\Type(type="AHS\AdvertsPluginBundle\Entity\Announcement")
     * @Assert\Valid()
     */
    protected $announcement;

    /**
     * @Assert\NotBlank()
     * @Assert\True()
     */
    protected $termsAccepted;

    public function __construct(Announcement $announcement = null, $termsAccepted = false)
    {
        $this->announcement = $announcement;
        $this->termsAccepted = $termsAccepted;
    }

    public function setAnnouncement(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function getAnnouncement()
    {
        return $this->announcement;
    }

    public function getTermsAccepted()
    {
        return $this->termsAccepted;
    }

    public function setTermsAccepted($termsAccepted)
    {
        $this->termsAccepted = (Boolean) $termsAccepted;
    }
}
