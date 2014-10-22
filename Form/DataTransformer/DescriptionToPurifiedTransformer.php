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
    public function __construct($purifierConfig = array())
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('AutoFormat.Linkify', $purifierConfig['linkify']);
        $config->set('HTML.Allowed', $purifierConfig['allowedhtml']);
        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * Transforms purified description string to its original string.
     *
     * @param string $description
     *
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

        return $this->purifier->purify(strip_tags($description));
    }
}
