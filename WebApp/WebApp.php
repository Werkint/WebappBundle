<?php
namespace Werkint\Bundle\WebAppBundle\WebApp;

class WebApp {

	private $cont;
	private $params;

	public function __construct($cont) {
		$this->cont = $cont;
		$this->params = $this->cont->getParameter('werkint_webapp');
		Twig\Handler::get()->init($this->params['cachedir'], $this->params['isdebug'], $cont);
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

}