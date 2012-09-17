<?php
namespace Werkint\Bundle\WebAppBundle\WebApp;

class WebApp {

	private $cont;
	private $params;

	public function __construct($cont) {
		$this->cont = $cont;
		$this->params = $this->cont->getParameter('werkint_webapp');
		Twig\Handler::get()->init($this->params['cachedir'], $this->params['isdebug'], $cont);
		$this->chunkClass = __NAMESPACE__ . '\\Chunks';

		$ext_tmp = get_class($cont->get('twig.extension.werkint.twig.base'));
		$ext_tmp::$webapp = $this;
		$ext_tmp::$postConstructHooks[] = $this;
	}

	public function createView() {
		$view = View\View::get();
		$view->prespath = $this->params['respath'];
		$view->presdir = $this->params['resdir'];
		$view->prevpath = $this->params['revpath'];
		$view->cont = $this->cont;
		return $view;
	}

	public function templateConstruct($templateName) {
		ScriptLoader::get()->addScripts($templateName);
	}

	private $chunkClass;

	public function chunkClass($name) {
		$this->chunkClass = $name;
	}

	public function getChunks() {
		$class = $this->chunkClass;
		return $class::get();
	}

	public function attach($name) {
		return Loader\Loader::get()->attach($name);
	}

	public function twigHandle() {
		return Twig\Handler::get();
	}

}