<?php

namespace AHS\AdvertsPluginBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AHSAdvertsPluginBundle extends Bundle
{
	public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // register extensions that do not follow the conventions manually
        $container->registerExtension(new \AHS\AdvertsPluginBundle\DependencyInjection\AHSAdvertsPluginExtension());
    }
}