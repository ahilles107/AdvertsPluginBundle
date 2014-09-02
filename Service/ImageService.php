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

namespace AHS\AdvertsPluginBundle\Service;

use Imagine\Image\Box;
use Imagine\Gd\Imagine;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use AHS\AdvertsPluginBundle\Entity\Image;
use Newscoop\Entity\User;

/**
 * Image Service
 */
class ImageService
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $orm;

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var array
     */
    protected $supportedTypes = array(
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
    );

    /**
     * @param array                      $config
     * @param Doctrine\ORM\EntityManager $orm
     * @param Translator                 $translator
     */
    public function __construct(array $config, \Doctrine\ORM\EntityManager $orm, $translator)
    {
        $this->config = $config;
        $this->orm = $orm;
        $this->translator = $translator;
    }

    /**
     * Upload image and create entity
     *
     * @param UploadedFile   $file
     * @param array          $attributes
     * @param ImageInterface $image
     *
     * @return LocalImage
     */
    public function upload(UploadedFile $file, array $attributes, ImageInterface $image = null)
    {
        $filesystem = new Filesystem();
        $imagine = new Imagine();

        $errors = array();
        $mimeType = $file->getClientMimeType();
        if (!in_array($mimeType, $this->supportedTypes)) {
            $errors[] = $this->translator->trans('ads.error.unsupportedType', array('%type%' => $mimeType));
        }

        if (!file_exists($this->config['image_path']) || !is_writable($this->config['image_path'])) {
            $errors[] = $this->translator->trans('ads.error.notwritable', array('%dir%' => '/images/ahs_adverts'));
        }

        if (!file_exists($this->config['thumbnail_path']) || !is_writable($this->config['thumbnail_path'])) {
            $errors[] = $this->translator->trans('ads.error.notwritable', array('%dir%' => '/images/ahs_adverts/thumbnails'));
        }

        if (!empty($errors)) {
            return $errors;
        }

        $attributes = array_merge(array(
            'content_type' => $mimeType,
        ), $attributes);

        $image = new Image($file->getClientOriginalName());
        $this->orm->persist($image);

        $this->fillImage($image, $attributes);
        $this->orm->flush();

        $imagePath = $this->generateImagePath($image->getId(), $file->getClientOriginalExtension());
        $thumbnailPath = $this->generateThumbnailPath($image->getId(), $file->getClientOriginalExtension());

        $image->setBasename($this->generateImagePath($image->getId(), $file->getClientOriginalExtension(), true));
        $image->setThumbnailPath($this->generateThumbnailPath($image->getId(), $file->getClientOriginalExtension(), true));
        $this->orm->flush();

        try {
            $file->move($this->config['image_path'], $this->generateImagePath($image->getId(), $file->getClientOriginalExtension(), true));
            $filesystem->chmod($imagePath, 0644);

            $imagine->open($imagePath)
                ->resize(new Box($this->config['thumbnail_max_size'], $this->config['thumbnail_max_size']))
                ->save($thumbnailPath, array());
            $filesystem->chmod($thumbnailPath, 0644);
        } catch (\Exceptiom $e) {
            $filesystem->remove($imagePath);
            $filesystem->remove($thumbnailPath);
            $this->orm->remove($image);
            $this->orm->flush();

            throw new \Exception($e->getMessage(), $e->getCode());
        }

        return $image;
    }

    /**
     * Generate image for given src
     *
     * @param string $src
     *
     * @return void
     */
    public function generateImageUrl($src)
    {
        $matches = array();
        if (!preg_match('#^([0-9]+)x([0-9]+)/([_a-z0-9]+)/([-_.:~%|a-zA-Z0-9]+)$#', $src, $matches)) {
            return;
        }

        list(, $width, $height, $specs, $imagePath) = $matches;

        $destFolder = rtrim($this->config['cache_path'], '/') . '/' . dirname(ltrim($src, './'));
        if (!realpath($destFolder)) {
            mkdir($destFolder, 0755, true);
        }

        if (!is_dir($destFolder)) {
            throw new \RuntimeException("Can't create folder '$destFolder'.");
        }

        $rendition = new Rendition($width, $height, $specs);
        $image = $rendition->generateImage($this->decodePath($imagePath));
        $image->save($destFolder . '/' . $imagePath);

        return $image;
    }

    /**
     * Generate file path for thumbnail
     *
     * @param int     $imageId
     * @param string  $extension
     * @param boolean $olnyFileName
     *
     * @return string
     */
    private function generateThumbnailPath($imageId, $extension, $olnyFileName = false)
    {
        if ($olnyFileName) {
            return $this->config['thumbnail_prefix'] . sprintf('%09d', $imageId) .'.'. $extension;
        }

        return $this->config['thumbnail_path'] . $this->config['thumbnail_prefix'] . sprintf('%09d', $imageId) .'.'. $extension;
    }

    /**
     * Generate url for thumbnail
     *
     * @param string $name Thumbnail name
     *
     * @return string
     */
    public function getThumbnailUrl($name)
    {
        return $this->config['thumbnail_dir'] . $name;
    }

    /**
     * Generate url for image
     *
     * @param string $name Image name
     *
     * @return string
     */
    public function getImageUrl($name)
    {
        return $this->config['image_dir'] . $name;
    }

    /**
     * Process image
     * @param Image $image Image object
     *
     * @return array
     */
    public function processImage(Image $image)
    {
        $processedPhoto = array(
            'id' => $image->getId(),
            'announcementPhotoId' => $image->getId(),
            'imageUrl' => $this->getImageUrl($image->getBasename()),
            'thumbnailUrl' => $this->getThumbnailUrl($image->getThumbnailPath()),
        );

        return $processedPhoto;
    }

    /**
     * Generate file path for image
     *
     * @param int     $imageId
     * @param string  $extension
     * @param boolean $olnyFileName
     *
     * @return string
     */
    private function generateImagePath($imageId, $extension, $olnyFileName = false)
    {
        if ($olnyFileName) {
            return $this->config['image_prefix'] . sprintf('%09d', $imageId) .'.'. $extension;
        }

        return $this->config['image_path'] . $this->config['image_prefix'] . sprintf('%09d', $imageId) .'.'. $extension;
    }

    /**
     * Fill image with custom/default arttributes
     *
     * @param LocalImage $image
     * @param array      $attributes
     *
     * @return LocalImage
     */
    public function fillImage($image, $attributes)
    {
        $attributes = array_merge(array(
            'user' => null,
        ), $attributes);

        $image->setUser($attributes['user']);

        return $image;
    }

    /**
     * Gets path of local images
     *
     * @return string
     */
    public function getImagePath()
    {
        return $this->config['image_path'];
    }
}
