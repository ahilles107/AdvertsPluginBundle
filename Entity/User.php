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
 * @author Paweł Mikołajczuk <mikolajczuk.protected@gmail.com>
 */

namespace AHS\AdvertsPluginBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Newscoop\Entity\User as NewscoopUser;

/**
 * User entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_adverts_user")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="newscoop_user_id")
     * @var string
     */
    protected $newscoopUserId;

    /**
     * @ORM\OneToMany(targetEntity="AHS\AdvertsPluginBundle\Entity\Announcement", mappedBy="user")
     */
    protected $announcements;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\User")
     * @ORM\JoinColumn(name="newscoop_user_id", referencedColumnName="Id")
     * @var Newscoop\Entity\User
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var string
     */
    protected $created_at;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getNewscoopUserId()
    {
        return $this->newscoopUserId;
    }

    public function setNewscoopUserId($newscoopUserId)
    {
        $this->newscoopUserId = $newscoopUserId;

        return $this;
    }

    public function getAnnouncement()
    {
        return $this->announcement;
    }

    public function setAnnouncement(\AHS\AdvertsPluginBundle\Entity\Announcement $announcement)
    {
        $this->announcement = $announcement;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Gets the value of user.
     *
     * @return Newscoop\Entity\User
     */
    public function getNewscoopUser()
    {
        return $this->user;
    }

    /**
     * Sets the value of user.
     *
     * @param Newscoop\Entity\User $user the user
     *
     * @return self
     */
    protected function setNewscoopUser(NewscoopUser $user)
    {
        $this->user = $user;

        return $this;
    }
}
