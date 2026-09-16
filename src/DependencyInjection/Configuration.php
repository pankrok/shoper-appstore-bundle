<?php

namespace PanKrok\ShoperAppstoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
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
                    ->arrayNode('rateLimit')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->integerNode('maxRetries')
                                ->info('Automatic retries after a 429 "Too many requests" response (Retry-After is honoured).')
                                ->min(0)->defaultValue(3)
                            ->end()
                            ->booleanNode('throttle')
                                ->info('Wait before sending when the X-SHOP-API-* leaky bucket reported by the shop is full.')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
