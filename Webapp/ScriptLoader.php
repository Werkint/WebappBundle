<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

class ScriptLoader {
	public function __construct() {
		/*$headLoader = function () {
			foreach ($this->loadedScripts as $name) {
				if (file_exists($name . '.scss')) {
					//$this->view->headStyle($name . '.scss', true);
				}
				if (file_exists($name . '.js')) {
					//$this->view->headScript($name . '.js', true);
				}
			}
		};
		\Closure::bind($headLoader, $this);*/
		//$this->view->events->bind('headRender', $headLoader);
	}

	protected $loadedScripts = array();

	protected function loadScript($name) {
		if (!in_array($name, $this->loadedScripts)) {
			$this->loadedScripts[] = $name;
		}
	}

	public function addScripts($name, $path) {
		$this->loadScript(pathinfo($path, PATHINFO_DIRNAME) . '/_all');
		$this->loadScript($path);
	}

}