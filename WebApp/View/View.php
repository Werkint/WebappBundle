<?php
namespace Werkint\Bundle\WebAppBundle\WebApp\View;
use \Werkint\Bundle\Menu\Menu;
use \Werkint\Toolkit\Hooks;
use \Werkint\Bundle\WebAppBundle\WebApp\Twig;

class View extends \Werkint\Component\Controller\View {

	public $prevpath;
	public $prespath;
	public $presdir;
	public $cont;

	public function menu() {
		return Menu::get();
	}

	// Шапка
	use ViewHead;

	public function path($respath = null) {
		if ($respath) {
			Twig\Handler::get()->twigRoot($respath);
		}
		return parent::path($respath);
	}

	public function renderLayout() {
		$head = Twig\Handler::get()->getHeader();
		$headname = '<' . md5('head' . microtime(true)) . ' />';
		$head = str_replace(array(
			'{#ACTIONPATH#}',
			'{#HEADNAME#}'
		), array(
			$this->pathAction(true) . '.twig',
			$headname
		), $head);
		$head = Twig\Handler::get()->render($head, $this->getTwigData());
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

	protected function __construct() {
		$this->initHead();
		$this->events = new Hooks();
		parent::__construct();
	}

}
