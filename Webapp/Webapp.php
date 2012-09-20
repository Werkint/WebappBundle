<?php
namespace Werkint\Bundle\WebappBundle\Webapp;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\Event;

class Webapp {

	protected $cont;
	protected $params;

	public function __construct(ContainerBuilder $cont) {
		$this->cont = $cont;
		$this->params = $this->cont->getParameter('werkint_webapp');

		$ext_tmp = get_class($cont->get('twig.extension.werkint.twig.base'));
		$ext_tmp::$webapp = $this;
	}

	protected $view;

	public function getView() {
		if (!$this->view) {
			$view = new View\View($this->twigHandle(), $this->cont);
			$view->prespath = $this->params['respath'];
			$view->presdir = $this->params['resdir'];
			$view->prevpath = $this->params['revpath'];
			$this->view = $view;
		}
		return $this->view;
	}

	protected $loader;

	private function getLoader() {
		if (!$this->loader) {
			$this->loader = new Loader\Loader($this->getView());
		}
		return $this->loader;
	}

	protected $scriptLoader;

	private function getScriptLoader() {
		if (!$this->scriptLoader) {
			$this->scriptLoader = new ScriptLoader($this->getView(), $this->twigHandle()->twig()->getLoader());
		}
		return $this->scriptLoader;
	}

	public function templateConstruct(Event $e) {
		$this->getScriptLoader()->addScripts($e->templateName);
	}

	protected $chunks;

	public function getChunks() {
		if (!$this->chunks) {
			$this->chunks = new Chunks();
		}
		return $this->chunks;
	}

	public function attach($name) {
		return $this->getLoader()->attach($name);
	}

	protected $twig_handler;

	public function twigHandle() {
		if (!$this->twig_handler) {
			$this->twig_handler = new Twig\Handler($this->params['cachedir'], $this->params['isdebug'], $this->cont);
		}
		return $this->twig_handler;
	}

}