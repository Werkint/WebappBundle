<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

class ScriptLoadHelper {

	/**
	 * @var ScriptLoader
	 */
	protected $loader;

	protected $path;
	protected $name;

	public function __construct($loader, $path, $name) {
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
		require($this->path . '/init.php');
	}
}