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
 * Image entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_adverts_image")
 * @ORM\HasLifecycleCallbacks()
 */
class Image 
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     * @var string
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="newscoop_image_id")
     * @var string
     */
    protected $newscoopImageId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="announcement")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */    
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="AHS\AdvertsPluginBundle\Entity\Announcement", inversedBy="images")
     * @ORM\JoinColumn(name="announcement_id", referencedColumnName="id")
     */
    protected $announcement;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var string
     */
    protected $created_at;

    public function __construct() {
        $this->setCreatedAt(new \DateTime());
    }

    /** 
     * @ORM\PreRemove 
     */
    public function preRemoveHandler() {
        $newscoopImage = new \Image($this->getNewscoopImageId());
        $newscoopImage->delete();
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

    public function getNewscoopImageId()
    {
        return $this->newscoopImageId;
    }

    public function setNewscoopImageId($newscoopImageId)
    {
        $this->newscoopImageId = $newscoopImageId;
        
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

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
        
        return $this;
    }
}

