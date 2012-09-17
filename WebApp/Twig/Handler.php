<?php
namespace Werkint\Bundle\WebAppBundle\WebApp\Twig;
use \Werkint\Toolkit\Singleton;

class Handler extends Singleton {

	protected $baseExt;

	public $chunkClass = '\\Werkint\\Bundle\\WebApp\\Twig\\Chunks';

	public function ext() {
		return $this->baseExt;
	}

	/**
	 * @var \Twig_Environment
	 */
	private $twig;

	public function twig() {
		return $this->twig;
	}

	public function twigRoot($path) {
		$loaderClass = $this->baseExt->getLoaderName();
		$loader = new $loaderClass($path);
		$this->twig->setLoader($loader);
	}

	public function render($code, $params) {
		$ret = $this->twig->render($code, $params);
		return $ret;
	}

	public function init($cachedir, $isdebug, &$cont) {
		$this->baseExt = $cont->get('twig.extension.werkint.twig.base');
		\Twig_Autoloader::register();
		$this->twig = new \Twig_Environment(null, array(
			'cache' => $cachedir,
			'debug' => $isdebug
		));

		$this->baseExt->addFilter(
			'rusDate', '\\Werkint\\Toolkit\\Fn::rusDate'
		);
		$this->baseExt->addFilter(
			'monthByNum', '\\Werkint\\Toolkit\\Fn::monthByNum'
		);

		$this->twig()->addExtension($this->baseExt);
		$this->twig()->addExtension(new \Twig_Extension_Debug());
	}

	public function getHeader() {
		return file_get_contents($this->resPath() . '/header.twig');
	}

	private function resPath() {
		return __DIR__ . '/../../Resources/views';
	}

	/**
	 * @return Handler
	 */
	public static function get() {
		return parent::get();
	}

}
