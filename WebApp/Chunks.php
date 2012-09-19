<?php
namespace Werkint\Bundle\WebAppBundle\WebApp;

class Chunks {

	public function __construct() {
		// Загрузчики
		if (method_exists($this, 'initLoaders')) {
			$this->initLoaders();
		}
	}

	// -- Loaders ---------------------------------------

	private $loaders = array();

	public function addLoader($path, $loader) {
		$this->loaders[$path] = $loader;
	}

	public function triggerLoader($path, &$data = null) {
		if (isset($this->loaders[$path])) {
			$loader = &$this->loaders[$path];
			$loader($data);
		}
	}

}
