<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

abstract class Template extends \Twig_Template {

	public function postConstruct() {
		$name = $this->getTemplateName();
		$path = $this->getEnvironment()->getLoader()->findTemplate($this->getTemplateName());
		TemplateEvent::postConstruct($name, $path);
	}

}
