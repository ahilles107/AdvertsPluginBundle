<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Paweł Mikołajczuk <mikolajczuk.protected@gmail.com>
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace AHS\AdvertsPluginBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @ORM\Column(type="datetime", name="created_at")
     * @var string
     */
    protected $created_at;

    public function __construct() {
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
}
