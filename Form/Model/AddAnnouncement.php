<?php
/**
 * @package AHS\AdvertsPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2014 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
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
