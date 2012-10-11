<?php
namespace Werkint\Bundle\WebappBundle\Webapp;
use \JsMin, \MinifyCSS, \SassParser;

class Compiler {

	/**
	 * @var ScriptHandler
	 */
	protected $handler;
	protected $targetdir;

	public function __construct($handler, $targetdir) {
		if (!file_exists($targetdir)) {
			throw new \Exception('Папка не существует: ' . $targetdir);
		}
		$this->handler = $handler;
		$this->targetdir = $targetdir;
	}

	public function compile($revision) {
		$hash = $this->handler->getDataHash() . '_rev' . $revision;
		$filepath = $this->targetdir . '/' . $hash;

		// Компилируем, если нужно
		$files = $this->handler->getFiles('scss');
		if (!$this->isFresh($filepath . '.css', $files)) {
			$this->loadStyles($filepath . '.css', $files);
		}
		$files = $this->handler->getFiles('js');
		if (!$this->isFresh($filepath . '.js', $files)) {
			$this->loadScripts($filepath . '.js', $files);
		}

		// Возвращаем хеш
		return $hash;
	}

	protected function isFresh($filepath, &$files) {
		if (!file_exists($filepath)) {
			return false;
		}
		$mtime = filemtime($filepath);
		foreach ($files as $file) {
			if (filemtime($file) > $mtime) {
				return false;
			}
		}
		return true;
	}

	protected function loadStyles($filepath, &$files) {
		$data = array();
		foreach ($this->handler->getVariables() as $name => $value) {
			if (!is_scalar($value)) {
				continue;
			}
			$data[] = '$const-' . str_replace('_', '-', $name) . ': "' . str_replace('"', '\\"', $value) . '";';
		}
		foreach ($files as $file) {
			$data[] = file_get_contents($file);
		}
		$data = join("\n", $data);

		$parser = new SassParser(array(
			'style'     => 'nested',
			'cache'     => FALSE,
			'syntax'    => 'scss',
			'debug'     => APP_DEBUG
		));
		try {
			$data = $parser->toCss($data, false);
		} catch (\Exception $e) {
			throw new \Exception('SCSS compiler error: ' . $e->getMessage() . ', loaded files: ' . print_r($files, true));
		}
		file_put_contents($filepath, $data);
	}

	protected function loadScripts($filepath, &$files) {
		$data = array(
			'window.CONST = {};'
		);
		foreach ($this->handler->getVariables() as $name => $value) {
			if (is_array($value)) {
				$value = json_encode($value);
			} else if (is_scalar($value)) {
				$value = '"' . str_replace('"', '\\"', $value) . '"';
			} else {
				throw new \Exception('Неправильный тип: ' . gettype($value));
			}
			$data[] = 'window.CONST.' . str_replace('-', '_', $name) . ' = ' . $value . ';';
		}
		foreach ($files as $file) {
			$data[] = file_get_contents($file);
		}
		$data = join("\n", $data);
		if (!APP_DEBUG) {
			\JsMin\Minify::minify($data);
		}
		file_put_contents($filepath, $data);
	}

}