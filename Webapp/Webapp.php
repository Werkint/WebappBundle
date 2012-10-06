<?php
namespace Werkint\Bundle\WebappBundle\Webapp;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;
use Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent;

class Webapp {

	protected $params;
	protected $loader;
	protected $handler;

	public function __construct($params) {
		$this->params = $params;
		$this->handler = new ScriptHandler();
		$this->loader = new ScriptLoader($this->handler, $this->params['resdir']);

		$this->handler->addVar('webapp-res', $this->params['respath']);
	}

	public function templateConstruct(TemplateEvent $e) {
		$this->loader->attachRelated($e->templatePath);
	}

	public function attach($name) {
		$this->loader->attach($name);
	}

	public function attachFile($name) {
		$this->loader->attachFile($name);
	}

	public function compile() {
		$compiler = new Compiler($this->handler, $this->params['resdir']);
		$revision = substr(crc32(file_exists($this->params['revpath']) ? file_get_contents($this->params['revpath']) : ''), 0, 6);
		return $compiler->compile($revision);
	}

	public function getVars() {
		return $this->handler->getVariables();
	}

	public function addVar($name, $value) {
		$this->handler->addVar($name, $value);
	}

}