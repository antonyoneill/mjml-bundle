<?php

namespace NotFloran\MjmlBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Process\ExecutableFinder;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('mjml');

        $finder = new ExecutableFinder();

        $rootNode
            ->children()
                ->variableNode('bin')->defaultValue(function () use ($finder) { return $finder->find('mjml'); })->end()
                ->booleanNode('mimify')->defaultFalse()->end()
                ->booleanNode('useFile')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
