<?php
namespace Werkint\Bundle\WebAppBundle\WebApp;

class ScriptLoader {

	protected $view;

	public function __construct($view) {
		$this->view = $view;
		$headLoader = function () {
			foreach ($this->loadedScripts as $name) {
				if (file_exists($name . '.scss')) {
					$this->view->headStyle($name . '.scss', true);
				}
				if (file_exists($name . '.js')) {
					$this->view->headScript($name . '.js', true);
				}
			}
		};
		\Closure::bind($headLoader, $this);
		$this->view->events->bind('headRender', $headLoader);
	}

	private $loadedScripts = array();

	private function loadScript($name) {
		$name = $this->view->path() . '/' . dirname($name) . '/' . pathinfo($name, PATHINFO_FILENAME);
		if (!in_array($name, $this->loadedScripts)) {
			$this->loadedScripts[] = $name;
		}
	}

	public function addScripts($path) {
		$this->loadScript(pathinfo($path, PATHINFO_DIRNAME) . '/_all');
		$this->loadScript($path);
	}

}