<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

class Chunks {

	protected $loaders = array();

	public function addLoader($namespace, $path, $loader) {
		if (!isset($this->loaders[$namespace])) {
			$this->loaders[$namespace] = array();
		}
		$this->loaders[$namespace][$path] = $loader;
	}

	public function triggerLoader($namespace, $path, &$data = null) {
		if (isset($this->loaders[$namespace]) && isset($this->loaders[$namespace][$path])) {
			$loader = &$this->loaders[$namespace][$path];
			$loader($data);
		}
	}

}
