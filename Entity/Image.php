<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
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
 */
class Image 
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="filepath")
     * @var string
     */
    private $filepath;

    /**
     * @ORM\Column(type="text", name="description")
     * @var string
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="AHS\AdvertsPluginBundle\Entity\Announcement", inversedBy="images")
     * @ORM\JoinColumn(name="announcement_id", referencedColumnName="id")
     */
    private $announcement;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var string
     */
    private $created_at;

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

    public function getFilepath()
    {
        return $this->filepath;
    }

    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;
        
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        
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

