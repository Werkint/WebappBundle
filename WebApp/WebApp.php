<?php
namespace Werkint\Bundle\WebAppBundle\WebApp;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\Event;

class WebApp {

	private $cont;
	private $params;

	public function __construct(ContainerBuilder $cont) {
		$this->cont = $cont;
		$this->params = $this->cont->getParameter('werkint_webapp');
		Twig\Handler::get()->init($this->params['cachedir'], $this->params['isdebug'], $cont);
		$this->chunkClass = __NAMESPACE__ . '\\Chunks';

		$ext_tmp = get_class($cont->get('twig.extension.werkint.twig.base'));
		$ext_tmp::$webapp = $this;
	}

	public function createView() {
		$view = View\View::get();
		$view->prespath = $this->params['respath'];
		$view->presdir = $this->params['resdir'];
		$view->prevpath = $this->params['revpath'];
		$view->cont = $this->cont;
		return $view;
	}

	public function templateConstruct(Event $e) {
		ScriptLoader::get()->addScripts($e->templateName);
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