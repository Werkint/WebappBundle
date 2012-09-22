<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Loader;

class Loader {

	protected $resdir;

	public function __construct($resdir) {
		$this->resdir = $resdir;
	}

	const EXT_JS = 'js';
	const EXT_CSS = 'scss';

	private $deps = array();
	private $staticRes = array();
	private $loaded = array();

	public function loadRes($path, $name, $bundle) {
		if (!isset($this->staticRes[$bundle])) {
			$this->staticRes[$bundle] = array();
		} else if (isset($this->staticRes[$bundle][$name])) {
			return;
		}
		$this->staticRes[$bundle][$name] = $path;
		$imgpath = $this->resdir . '/' . $bundle;
		if (!file_exists($imgpath)) {
			mkdir($imgpath);
		}
		$imgpath .= '/' . $name;
		if (file_exists($imgpath)) {
			return;
		}
		try {
			symlink($path, $imgpath);
		} catch (\Exception $e) {
			throw new \Exception('Ошибка создания ссылки. Источник: "' . $path . '", цель: "' . $imgpath . '"');
		}
	}

	public function attachFile($path) {
		$ext = pathinfo($path);
		$path = $ext['dirname'] . '/' . $ext['filename'];
		if ($ext['extension'] == self::EXT_JS) {
			//$this->view->headScript($path . '.' . $ext['extension'], true);
		} else if ($ext['extension'] == self::EXT_CSS) {
			//$this->view->headStyle($path . '.' . $ext['extension'], true);
		} else {
			return false;
		}
	}

	public function attach($name) {
		if (isset($this->loaded[$name])) {
			return;
		}
		$this->loaded[$name] = $name;

		// Dependencies
		if (!$this->deps) {
			$this->deps = parse_ini_file($this->scriptPath() . '/../config/scripts.ini');
		}
		if (!isset($this->deps[$name])) {
			return;
		}
		$deps = trim($this->deps[$name]);
		if ($deps != '.root') {
			$deps = explode(',', $deps);
			foreach ($deps as $dep) {
				$this->attach($dep);
			}
		}

		$path = $this->scriptPath() . '/' . $name;
		if (is_dir($path)) {
			$model = new Helper($this, $path, $name);
			$model->load();
		} else {
			$this->attachFile($path);
		}
	}

	private function scriptPath() {
		return realpath(__DIR__ . '/../../Resources/scripts');
	}

}
