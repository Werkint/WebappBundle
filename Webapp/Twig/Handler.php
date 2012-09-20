<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Twig;

class Handler {

	public function __construct($cachedir, $isdebug, &$cont) {
		$this->baseExt = $cont->get('twig.extension.werkint.twig.base');

		$this->twig = new \Twig_Environment(null, array(
			'cache' => $cachedir,
			'debug' => $isdebug
		));

		$this->twig->setLoader($cont->get('twig.loader'));

		foreach ($cont->findTaggedServiceIds('twig.extension') as $id => $attrs) {
			$this->twig()->addExtension($cont->get($id));
		}
		$this->twig()->addExtension(new \Twig_Extension_Debug());
	}

	protected $baseExt;

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

	public function render($code, $params) {
		$ret = $this->twig->render($code, $params);
		return $ret;
	}

	public function getHeader() {
		return file_get_contents($this->resPath() . '/html.twig');
	}

	private function resPath() {
		return __DIR__ . '/../../Resources/templates';
	}

}
