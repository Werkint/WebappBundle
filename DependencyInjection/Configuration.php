<?php
namespace Werkint\Bundle\WebAppBundle\DependencyInjection;

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

		$this->configMisc($rootNode);

		$rootNode->end();
		return $treeBuilder;
	}

	private function configMisc(NodeBuilder $rootNode) {
		$rootNode
			->scalarNode('respath')->end();
		$rootNode
			->scalarNode('resdir')->end();
		$rootNode
			->scalarNode('revpath')->end();
		$rootNode
			->scalarNode('cachedir')->end();
		$rootNode
			->booleanNode('isdebug')->end();
	}
}