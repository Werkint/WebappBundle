<?php
namespace Werkint\Bundle\WebApp\Twig\Extension;
use \Werkint\Bundle\WebApp\Twig\Handler;
use \Werkint\Bundle\WebApp\Twig\Chunks;
use \Werkint\Component\Routing\Router;
use \Werkint\Toolkit\NsLookup;

abstract class Template extends \Twig_Template {

	private function getChunksTmp() {
		return \Werkint\Bundle\WebAppBundle\WebApp\Chunks::get();
	}

	public function display(array $context, array $blocks = array()) {
		$context = $this->env->mergeGlobals($context);
		$context = new TemplateData($context);
		if (!Loader::isRaw($this->getTemplateName())) {
			if ($this->getChunksTmp()->triggerLoader($this->getTemplateName(), $context) === false) {
				return false;
			}
		}
		$context = $context->toArray();
		return parent::display($context, $blocks);
	}

}
