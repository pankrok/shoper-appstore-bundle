<?php

namespace PanKrok\ShoperAppstoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('appstore');

        $treeBuilder->getRootNode()
                ->children()
                    ->scalarNode('appId')->end()
                    ->scalarNode('appSecret')->end()
                    ->scalarNode('appstoreSecret')->end()
                    ->scalarNode('username')->end()
                    ->scalarNode('password')->end()
                    ->scalarNode('shopurl')->end()
                    ->booleanNode('debug')->defaultValue(false)->end()
                    ->scalarNode('jssdk')->defaultValue('https://dcsaascdn.net/js/dc-sdk-1.0.5.min.js')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
