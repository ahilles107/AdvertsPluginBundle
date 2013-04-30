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
 * Announcement entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_adverts_announcement")
 */
class Announcement 
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     * @var string
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="name")
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(type="text", name="description")
     * @var string
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="announcement")
     */ 
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="announcement")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */  
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="announcement")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */    
    private $user;

    /**
     * @ORM\Column(type="float", name="price")
     * @var string
     */
    private $price;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var string
     */
    private $created_at;

    public function __construct() {
        $this->setCreatedAt(new \DateTime());
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        
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

