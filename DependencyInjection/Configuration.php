<?php
namespace Werkint\Bundle\WebappBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

class Configuration implements ConfigurationInterface {

	private $alias;

	public function __construct($alias) {
		$this->alias = $alias;
	}

	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root($this->alias)->children();

		$rootNode
			->scalarNode('respath')->end();
		$rootNode
			->scalarNode('resdir')->end();
		$rootNode
			->scalarNode('revpath')->end();

		$rootNode->end();
		return $treeBuilder;
	}
}