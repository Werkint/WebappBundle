<?php
namespace Werkint\Bundle\WebAppBundle\WebApp;
use \Werkint\Toolkit\Singleton;

class ScriptLoader extends Singleton {

	private function init() {
		$headLoader = function () {
			foreach ($this->loadedScripts as $name) {
				if (file_exists($name . '.scss')) {
					View\View::get()->headStyle($name . '.scss', true);
				}
				if (file_exists($name . '.js')) {
					View\View::get()->headScript($name . '.js', true);
				}
			}
		};
		\Closure::bind($headLoader, $this);
		View\View::get()->events->bind('headRender', $headLoader);
	}

	private $loadedScripts = array();

	private function loadScript($name) {
		$name = View\View::get()->path() . '/' . dirname($name) . '/' . pathinfo($name, PATHINFO_FILENAME);
		if (!in_array($name, $this->loadedScripts)) {
			$this->loadedScripts[] = $name;
		}
	}

	public function addScripts($path) {
		$this->loadScript(pathinfo($path, PATHINFO_DIRNAME) . '/../_all');
		$this->loadScript(pathinfo($path, PATHINFO_DIRNAME) . '/_all');
		$this->loadScript($path);
	}

	protected function __construct() {
		$this->init();
	}

}