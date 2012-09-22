<?php
namespace Werkint\Bundle\WebappBundle\Webapp;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;
use Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent;

class Webapp {
	protected $params;

	public function __construct($params) {
		$this->params = $params;
	}

	protected $loader;
	protected $scriptLoader;

	protected function initLoaders() {
		$this->loader = new Loader\Loader($this->params['resdir']);
		$this->scriptLoader = new ScriptLoader();
	}

	public function templateConstruct(TemplateEvent $e) {
		$this->scriptLoader->addScripts($e->templateName, $e->templatePath);
	}

	public function attach($name) {
		return $this->loader->attach($name);
	}

}