<?php
namespace Werkint\Bundle\WebApp\Twig\Extension;

class TemplateData {

	// Хранитель данных
	use \Werkint\Toolkit\Store;

	protected $data = array();

	public function __set($name, $value) {
		$this->data[$name] = $value;
		return $value;
	}

	public function __get($name) {
		if (isset($this->data[$name])) {
			if (is_scalar($this->data[$name])) {
				$property = $this->data[$name];
			} else {
				$property = &$this->data[$name];
			}
			return $property;
		}
		// Возвращаем null, чтобы шаблоны не выбивали ошибки
		return null;
	}

	public function append($data) {
		$this->data = array_merge($this->data, $data);
	}

	public function __construct($data = null) {
		if ($data) {
			$this->append($data);
		}
	}

	public function toArray() {
		return $this->data;
	}

}
