<?php

namespace PanKrok\ShoperAppstoreBundle\DependencyInjection;

use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ShoperAppstoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');

        if (class_exists(MakerBundle::class)) {
            $loader->load('services_maker.yaml');
        }

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('appstore', $config);
    }
}
