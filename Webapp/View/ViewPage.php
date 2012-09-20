<?php
namespace Werkint\Bundle\WebappBundle\Webapp\View;
use \Werkint\Component\Routing\Route;
use \Werkint\Bundle\Menu\Menu;

class ViewPage {

	// Хранитель данных
	use \Werkint\Toolkit\Store;

	/**
	 * @var View
	 */
	private $view;

	public function __construct($view) {
		$this->view = $view;
	}

	private $title;

	public function title($name = null) {
		if ($name) {
			$this->title = $name;
			return $this;
		} else {
			return $this->title;
		}
	}

	private $data;

	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function __get($name) {
		switch ($name) {
			case 'menu':
				return Menu::get();
			case 'module':
				return Route::get()->module;
			case 'controller':
				return Route::get()->controller;
			case 'action':
				return Route::get()->action;
			case 'user':
				$user = \Werkint\Bundle\Acl\Acl::get()->user();
				return $user ? $user->row() : null;
			case 'title':
				return $this->title();
		}
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}
		return $this->raiseGet($name);
	}

}
