<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

class ScriptHandler {

	public function getDataHash() {
		return md5(serialize($this->getVariables()) . serialize($this->getFiles()));
	}

	protected $vars = array();

	public function getVariables() {
		return $this->vars;
	}

	public function addVar($name, $value) {
		$this->vars[$name] = $value;
	}

	protected $files = array();

	public function appendFile($path) {
		$this->files[] = $path;
	}

	public function getFiles($ext = null) {
		if (!$ext) {
			return $this->files;
		} else {
			$ret = array();
			foreach ($this->files as $file) {
				if (pathinfo($file, PATHINFO_EXTENSION) == $ext) {
					$ret[] = $file;
				}
			}
			return $ret;
		}
	}

	protected $loaded = array();

	public function wasLoaded($name) {
		return isset($this->loaded[$name]);
	}

	public function setLoaded($name) {
		if ($this->wasLoaded($name)) {
			throw new \Exception('Скрипт уже загружался');
		}
		$this->loaded[$name] = true;
	}

}