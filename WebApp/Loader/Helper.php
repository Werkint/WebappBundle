<?php
namespace Werkint\Bundle\WebAppBundle\WebApp\Loader;

class Helper {

	private $path = '';
	private $name = '';

	public function attach($name) {
		Loader::get()->attachFile($this->path . '/' . $name);
	}

	public function loadRes($path, $name = null) {
		if (!$name) {
			$name = $path;
		}
		Loader::get()->loadRes($this->path . '/' . $path, $name, $this->name);
	}

	public function __construct($path, $name) {
		$this->path = $path;
		$this->name = $name;
	}

	public function load() {
		include_once($this->path . '/init.php');
	}
}