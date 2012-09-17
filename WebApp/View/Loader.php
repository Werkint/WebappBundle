<?php

namespace Werkint\Bundle\WebAppBundle\WebApp\View;

use \Werkint\Component\Controller\View;
use \Werkint\Component\Routing\Router;
use \Werkint\Bundle\WebApp\Twig;
use \Werkint\Toolkit\Singleton;

class Loader extends Singleton {

	public static function init() {
		Router::get()->viewClass(__NAMESPACE__ . '\\View');
		Twig\Handler::get();
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
		$imgpath = WEBAPP_RES_DIR . '/' . $bundle;
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
			throw new \Exception('Unable to symlink WebApp resource. Source: "' . $path . '", dest: "' . $imgpath . '"');
		}
	}

	public function attachFile($path) {
		$ext = pathinfo($path);
		$path = $ext['dirname'] . '/' . $ext['filename'];
		if ($ext['extension'] == self::EXT_JS) {
			Router::get()->view()->headScript($path . '.' . $ext['extension'], true);
		} else if ($ext['extension'] == self::EXT_CSS) {
			Router::get()->view()->headStyle($path . '.' . $ext['extension'], true);
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
			$this->deps = parse_ini_file($this->scriptPath() . '/scripts.ini');
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
			$model = new LoadHelper($path, $name);
			$model->load();
		} else {
			$this->attachFile($path);
		}
	}

	private function scriptPath() {
		return realpath(dirname(__FILE__) . '/scripts');
	}

	/**
	 * @return Loader
	 */
	public static function get() {
		return parent::get();
	}

	protected function __construct() {
		$this->init();
	}

}
