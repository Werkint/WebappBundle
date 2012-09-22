<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

class ScriptLoader {

	/**
	 * @var ScriptHandler
	 */
	protected $handler;
	protected $resdir;

	public function __construct($handler, $resdir) {
		$this->handler = $handler;
		$this->resdir = $resdir;
	}

	/**
	 * Подключает библиотеку
	 * @param $name
	 * @throws \Exception
	 */
	public function attach($name) {
		if (!$this->scriptExists($name)) {
			throw new \Exception('Скрипт не существует: ' . $name);
		}
		if ($this->handler->wasLoaded($name)) {
			// Уже загружен
			return;
		}

		// Зависимости
		$deps_loaded = $this->scriptAttachDeps($name);

		$path = $this->pathScripts() . '/' . $name;
		if (is_dir($path)) {
			$model = new ScriptLoadHelper($this, $path, $name);
			$model->load();
		} else {
			$this->attachFile($path, $deps_loaded);
		}

		// Загружен
		$this->handler->setLoaded($name);
	}

	/**
	 * Подключает один файл
	 * @param string $path
	 * @return bool
	 */
	public function attachFile($path, $ignore_check = false) {
		if (!file_exists($path)) {
			if (!$ignore_check) {
				throw new \Exception('Файл не найден: ' . $path);
			} else {
				return;
			}
		}
		$this->handler->appendFile($path);
	}

	public function attachRelated($path) {
		$dir = pathinfo($path, PATHINFO_DIRNAME);
		$name = $dir . '/_all';
		$this->attachFile($name . '.scss', true);
		$this->attachFile($name . '.js', true);
		$name = $dir . '/' . pathinfo($path, PATHINFO_FILENAME);
		$this->attachFile($name . '.scss', true);
		$this->attachFile($name . '.js', true);
	}

	protected function scriptAttachDeps($name) {
		$deps = $this->getDeps();
		$list = trim($deps[$name]);
		if ($list != '.root') {
			$list = explode(',', $list);
			foreach ($list as $dep) {
				$this->attach($dep);
			}
			return true;
		}
		return false;
	}

	protected function scriptExists($name) {
		$deps = $this->getDeps();
		return isset($deps);
	}

	// -- Service ---------------------------------------

	protected $deps;

	protected function getDeps() {
		if (!$this->deps) {
			$this->deps = parse_ini_file($this->pathDeps());
		}
		return $this->deps;
	}

	protected function pathDeps() {
		return realpath(__DIR__ . '/../Resources/config/scripts.ini');
	}

	protected function pathScripts() {
		return realpath(__DIR__ . '/../Resources/scripts');
	}

	// -- Статические ресурсы ---------------------------------------

	private $staticRes = array();

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

}
