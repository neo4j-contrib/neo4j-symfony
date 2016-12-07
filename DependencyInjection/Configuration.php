<?php

declare(strict_types=1);

namespace Neo4j\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle.
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Whether to use the debug mode.
     *
     * @see https://github.com/doctrine/DoctrineBundle/blob/v1.5.2/DependencyInjection/Configuration.php#L31-L41
     *
     * @var bool
     */
    private $debug;

    /**
     * @param bool $debug
     */
    public function __construct($debug)
    {
        $this->debug = (bool) $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('neo4j');

        $root->children()
            ->arrayNode('profiling')
                ->addDefaultsIfNotSet()
                ->treatFalseLike(['enabled' => false])
                ->treatTrueLike(['enabled' => true])
                ->treatNullLike(['enabled' => $this->debug])
                ->info('Extend the web profiler with information about queries.')
                ->children()
                    ->booleanNode('enabled')
                        ->info('Turn the toolbar on or off. Defaults to kernel debug mode.')
                        ->defaultValue($this->debug)
                    ->end()
                ->end()
            ->end()
            ->arrayNode('clients')
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->addDefaultsIfNotSet()
                ->fixXmlConfig('connection')
                ->children()
                    ->arrayNode('connections')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()->end()
            ->arrayNode('entity_managers')
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('client')->defaultValue('default')->end()
                    ->scalarNode('cache_dir')->defaultNull()->end()
                ->end()
            ->end()->end()
            ->arrayNode('connections')
                ->isRequired()
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->addDefaultsIfNotSet()
                ->children()
                    ->enumNode('schema')->values(['http', 'bolt'])->defaultValue('http')->end()
                    ->scalarNode('host')->defaultValue('localhost')->end()
                    ->integerNode('port')->defaultValue(7474)->end()
                    ->scalarNode('username')->defaultValue('neo4j')->end()
                    ->scalarNode('password')->defaultValue('neo4j')->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
