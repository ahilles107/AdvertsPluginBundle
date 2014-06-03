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
 * Announcement entity
 *
 * @ORM\Entity(repositoryClass="AHS\AdvertsPluginBundle\Repository\AnnouncementRepository")
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
    protected $id;

    /**
     * @ORM\Column(type="string", name="name")
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="text", name="description")
     * @Assert\NotBlank(message="Musisz podac opis")
     * @var string
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="announcement")
     */
    protected $images;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="announcements")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="announcements")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Newscoop\Entity\Publication")
     * @ORM\JoinColumn(name="IdPublication", referencedColumnName="Id")
     * @var \Newscoop\Entity\Publication
     */
    protected $publication;

    /**
     * @ORM\Column(type="float", name="price")
     * @Assert\NotBlank(message="Musisz podac cenę")
     * @Assert\Range(min = "0", minMessage = "Cena musi być większa od 0")
     * @Assert\Type(type="float", message = "Cena musi być liczbą")
     * @var string
     */
    protected $price;

    /**
     * @ORM\Column(type="integer", name="reads_number", nullable=true)
     * @var integer
     */
    protected $reads;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     * @var string
     */
    protected $created_at;

    /**
     * TODO: 
     * * anonucement type looking/offering
     * * valid date
     * * anonucement status active/disactive
     * * * anouncement result - succesful or notsuccesfull
     * * * fix caching
     */

    /**
     * @ORM\Column(type="boolean", name="is_active", nullable=true)
     * @var string
     */
    protected $is_active;

    public function __construct() {
        $this->setCreatedAt(new \DateTime());
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
        $this->is_active = true;
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

    public function getValidDate()
    {
        $date = clone $this->created_at;
        $date->modify('+14 days');

        return $date;
    }

    public function isStillValid()
    {
        $date = new \DateTime();
        return $date <= $this->getValidDate();
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

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function getFirstImage($showEmpty = null)
    {
        if (!count($this->images) && $showEmpty) {
            return array(
                'id' => null,
                'announcementPhotoId' => null,
                'imageUrl' => '/public/bundles/ahsadvertsplugin/images/empty.jpg',
                'thumbnailUrl' => '/public/bundles/ahsadvertsplugin/images/small_empty.jpg'
            );
        } elseif (!count($this->images)) {
            return null;
        }

        return $this->processImage($this->images[0]);
    }

    public function getFirstImageWithEmpty()
    {
        return $this->getFirstImage(true);
    }

    protected function processImage($image)
    {
        $newscoopImage = new \Image($image->getNewscoopImageId());
        $processedPhoto = array(
            'id' => $newscoopImage->getImageId(),
            'announcementPhotoId' => $image->getId(),
            'imageUrl' => $newscoopImage->getImageUrl(),
            'thumbnailUrl' => $newscoopImage->getThumbnailUrl()
        );

        return $processedPhoto;
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

    public function setCreatedAt(\DateTime $created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getSlug()
    {
        return $this->slugify($this->name);
    }

    public function addRead()
    {
        return $this->reads = $this->reads+1;
    }

    public function getReads()
    {
        return $this->reads;
    }

    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Modifies a string to remove all non ASCII characters and spaces.
     */
    public function slugify($text)
    {
        $charMap = array(
            // Latin symbols
            '©' => '(c)',
            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',
        );
        // Make custom replacements
        $text = str_replace(array_keys($charMap), $charMap, $text);
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

    /**
     * Gets the publication.
     *
     * @return \Newscoop\Entity\Publication
     */
    public function getPublication()
    {
        return $this->publication;
    }

    /**
     * Sets the publication.
     *
     * @param \Newscoop\Entity\Publication $publication
     *
     * @return self
     */
    public function setPublication(\Newscoop\Entity\Publication $publication)
    {
        $this->publication = $publication;

        return $this;
    }

    public function getCategoryView()
    {
        return array(
            'id' => $this->category->getId(),
            'name' => $this->category->getName()
        );
    }

    public function getUrl()
    {
        return clone $this;
    }

    /**
     * Sets the value of is_active.
     *
     * @param string $is_active the is_active
     *
     * @return self
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * Sets the value of reads.
     *
     * @param integer $reads the reads
     *
     * @return self
     */
    public function setReads($reads)
    {
        $this->reads = $reads;

        return $this;
    }
}
