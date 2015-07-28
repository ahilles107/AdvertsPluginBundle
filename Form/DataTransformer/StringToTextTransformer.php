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

class StringToTextTransformer implements DataTransformerInterface
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
        $config->set('Cache.SerializerPath', $purifierConfig['cachepath']);
        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * Transforms string to normal text without html tags.
     *
     * @param string $string
     *
     * @return string
     */
    public function transform($string)
    {
        if (null === $string) {
            return "";
        }

        return strip_tags($this->purifier->purify($string));
    }

    /**
     * Transforms string to purified string.
     *
     * @param string $string
     *
     * @return string
     *
     * @throws TransformationFailedException if $string is null.
     */
    public function reverseTransform($string)
    {
        if (null === $string) {
            throw new TransformationFailedException("Field is empty!");
        }

        return strip_tags($this->purifier->purify($string));
    }
}
