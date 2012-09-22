<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Extension extends \Twig_Extension {

	/**
	 * @var EventDispatcher
	 */
	public static $dispatcher;

	public function __construct(EventDispatcher $dispatcher) {
		static::$dispatcher = $dispatcher;
	}

	public function initRuntime(\Twig_Environment $environment) {
		$environment->setBaseTemplateClass(__NAMESPACE__ . '\\Template');
		$environment->setLoader(new Loader());
	}

	public function getNodeVisitors() {
		return array(
			new NodeVisitor()
		);
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName() {
		return 'werkint.webapp';
	}
}
