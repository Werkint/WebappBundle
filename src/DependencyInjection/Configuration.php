<?php
namespace Werkint\Bundle\WebappBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Configuration implements
    ConfigurationInterface
{
    protected $alias;

    /**
     * @param string $alias
     */
    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        // @formatter:off
        $treeBuilder
            ->root($this->alias)
            ->children()
                ->scalarNode('force_root_block')->defaultFalse()->end()
                ->scalarNode('respath')->isRequired()->end()
                ->scalarNode('resdir')->isRequired()->end()
                ->scalarNode('revpath')->isRequired()->end()
                ->scalarNode('project')->isRequired()->end()
                ->arrayNode('filters')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('js')
                            ->defaultValue([])
                            ->prototype('scalar')
                            ->end()
                        ->end()
                        ->arrayNode('css')
                            ->defaultValue(['scss'])
                            ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('browsers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('warn')->defaultValue(false)->end()
                        ->arrayNode('modern')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('msie')->defaultValue('10')->end()
                                ->scalarNode('opera')->defaultValue('15')->end()
                                ->scalarNode('firefox')->defaultValue('20')->end()
                                ->scalarNode('chrome')->defaultValue('25')->end()
                                ->scalarNode('safari')->defaultValue('6')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
        // @formatter:on

        return $treeBuilder;
    }

}