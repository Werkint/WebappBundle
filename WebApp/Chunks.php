<?php
namespace Werkint\Bundle\WebAppBundle\WebApp;
use \Werkint\Toolkit\Singleton;

class Chunks extends Singleton {

	private function init() {
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

	/**
	 * @return Chunks
	 */
	public static function get() {
		return parent::get();
	}

	protected function __construct() {
		$this->init();
	}

}
