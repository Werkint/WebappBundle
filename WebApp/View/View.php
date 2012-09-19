<?php
namespace Werkint\Bundle\WebAppBundle\WebApp\View;
use \Werkint\Bundle\Menu\Menu;
use \Werkint\Toolkit\Hooks;
use \Werkint\Bundle\WebAppBundle\WebApp\Twig;

class View extends AbstractView {

	protected $twig_handler;
	protected $cont;

	public function __construct($twig_handler, $cont) {
		$this->twig_handler = $twig_handler;
		$this->cont = $cont;

		$this->initHead();
		$this->events = new Hooks();
	}

	public $prevpath;
	public $prespath;
	public $presdir;

	public function menu() {
		return Menu::get();
	}

	// Шапка
	use ViewHead;

	public function render() {
		$head = $this->twig_handler->getHeader();
		$headname = '<' . md5('head' . microtime(true)) . ' />';
		$head = str_replace(array(
			'{#ACTIONPATH#}',
			'{#HEADNAME#}'
		), array(
			$this->pathAction(true) . '.twig',
			$headname
		), $head);
		$head = $this->twig_handler->render($head, $this->getTwigData());
		$head = str_replace($headname, $this->headRender(), $head);
		return $head;
	}

	private function getTwigData() {
		$data = array_merge($this->viewData(), array(
			'page' => $this->page
		));
		return $data;
	}

	// -- Page ---------------------------------------

	protected $page;

	public function page() {
		return $this->page;
	}

	protected function initHead() {
		$this->page = new ViewPage($this);
	}

}
