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
 * Category entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="plugin_adverts_category")
 */
class Category 
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
     * @ORM\OneToMany(targetEntity="AHS\AdvertsPluginBundle\Entity\Announcement", mappedBy="category")
     */ 
    private $announcements;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var string
     */
    private $created_at;

    public function __construct() {
        $this->setCreatedAt(new \DateTime());
        $this->announcements = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getSlug()
    {
        return $this->slugify($this->name);
    }


    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }

    public function getAnnouncements()
    {
        return $this->announcements;
    }

    public function setAnnouncement(\AHS\AdvertsPluginBundle\Entity\Announcement $announcement)
    {
        $this->announcements[] = $announcement;
        
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
     * Modifies a string to remove all non ASCII characters and spaces.
     */
    public function slugify($text)
    {
        $char_map = array(
            // Latin symbols
            '©' => '(c)',
            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z', 
            'Ż' => 'Z', 
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',
        );
        // Make custom replacements
        $text = str_replace(array_keys($char_map), $char_map, $text);
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        // trim
        $text = trim($text, '-');
        // transliterate
        if (function_exists('iconv')) {
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }
        // lowercase
        $text = strtolower($text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) {
            return 'n-a';
        }
     
        return $text;
    }
}

