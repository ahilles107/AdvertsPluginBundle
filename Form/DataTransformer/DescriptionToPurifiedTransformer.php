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

namespace AHS\AdvertsBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Acme\TaskBundle\Entity\Issue;

class DescriptionToPurifiedTransformer implements DataTransformerInterface
{
    /**
     * HTML Purifier
     * @var HTMLPurifier
     */
    private $purifier;

    /**
     * Construct
     */
    public function __construct()
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('AutoFormat.Linkify', true);
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('HTML.Allowed', 'a[href], p');
        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * Transforms description string to its original string.
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($description)
    {

        if (null === $description) {
            return "";
        }

        return strip_tags($this->purifier->purify($description));
    }

    /**
     * Transforms description string to purified description string.
     *
     * @param string $description
     *
     * @return string
     *
     * @throws TransformationFailedException if $description is null.
     */
    public function reverseTransform($description)
    {
        if (null === $description) {
            throw new TransformationFailedException("Description field is empty!");
        }

        $description = str_replace("  ", "&nbsp;" , $this->purifier->purify($description));

        return nl2br($this->purifier->purify($description));
    }
}
