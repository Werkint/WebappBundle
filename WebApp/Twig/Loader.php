<?php
namespace Werkint\Bundle\WebAppBundle\WebApp\Twig;

class Loader extends \Twig_Loader_Filesystem {

	public function setPaths($paths, $namespace = '__main__') {
		if (!$paths) {
			return false;
		}
		return parent::setPaths($paths, $namespace);
	}

	public static function isRaw($data) {
		return (strpos($data, '{') !== false);
	}

	public function getSource($name) {
		if ($this->isRaw($name)) {
			return $name;
		}
		return parent::getSource($name);
	}

	public function findTemplate($name) {
		if ($this->isRaw($name)) {
			return $name;
		}
		return parent::findTemplate($name);
	}

	public function isFresh($name, $time) {
		if ($this->isRaw($name)) {
			return false;
		}
		return parent::isFresh($name, $time);
	}

}
