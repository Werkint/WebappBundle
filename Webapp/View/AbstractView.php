<?php
namespace Werkint\Bundle\WebappBundle\Webapp\View;
use \Werkint\Component\Routing\Route;

abstract class AbstractView {

	// Хранитель данных
	use \Werkint\Toolkit\Store;

	private $target;

	public function target($target = null) {
		if ($target) {
			$this->target = $target;
			return $this;
		} else {
			return $this->target;
		}
	}

	protected $respath;

	public function path($respath = null) {
		if ($respath) {
			$this->respath = $respath;
			return $this;
		} else {
			return $this->respath;
		}
	}

	public function pathAction($noext = false) {
		return '@' . Route::get()->module . '/' . Route::get()->controller . '/' . Route::get()->action . ($noext ? '' : '.phtml');
	}

	abstract function render();

	// -- Данные ---------------------------------------


	/**
	 * Associative array for storing view data
	 * @var type
	 */
	protected $_viewData = array();

	public function viewData($data = null) {
		if ($data) {
			$this->_viewData = $data;
			return $this;
		} else {
			return $this->_viewData;
		}
	}

	/**
	 * Setter for view data
	 *
	 * @param string $name  view variable name
	 * @param mixed  $value Variable data
	 * @return mixed input value
	 */
	public function __set($name, $value) {
		$this->_viewData[$name] = $value;
		return $value;
	}

	/**
	 * Returns view variable or renders view
	 *
	 * @param string $name Input parameter (for example, view varible name)
	 * @return mixed HTML or view variable
	 */
	public function __get($name) {
		if (isset($this->_viewData[$name])) {
			if (is_scalar($this->_viewData[$name])) {
				$property = $this->_viewData[$name];
			} else {
				$property = &$this->_viewData[$name];
			}
			return $property;
		}
		return $this->raiseGet($name);
	}

}
