<?php

declare(strict_types=1);

namespace Prokl\ArgumentResolversBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Prokl\ArgumentResolversBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('argument_resolvers');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('defaults')
                    ->useAttributeAsKey('name')
                    ->prototype('boolean')->end()
                    ->defaultValue(['enabled' => true])
                ->end()

                ->arrayNode('resolvers')
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
