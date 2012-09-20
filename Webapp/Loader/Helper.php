<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Loader;

class Helper {

	private $path = '';
	private $name = '';
	protected $loader;

	public function __construct(&$loader, $path, $name) {
		$this->loader = $loader;
		$this->path = $path;
		$this->name = $name;
	}

	public function attach($name) {
		$this->loader->attachFile($this->path . '/' . $name);
	}

	public function loadRes($path, $name = null) {
		if (!$name) {
			$name = $path;
		}
		$this->loader->loadRes($this->path . '/' . $path, $name, $this->name);
	}

	public function load() {
		include_once($this->path . '/init.php');
	}
}