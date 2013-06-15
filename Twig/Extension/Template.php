<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Twig_Template;

abstract class Template extends Twig_Template
{
    const BLOCK_PREFIX = 'globalhook_';

    public function display(array $context, array $blocks = array())
    {
        parent::display($context, $blocks);
        $name = $this->getTemplateName();
        $path = $this->getEnvironment()->getLoader()->findTemplate(
            $this->getTemplateName()
        );
        TemplateEvent::postConstruct($name, $path);
    }

    public function displayBlock($name, array $context, array $blocks = [])
    {
        if (strpos($name, static::BLOCK_PREFIX) === 0) {
            $tpl = substr($name, strlen(static::BLOCK_PREFIX));
            TemplateEvent::blockPre($tpl);
            parent::displayBlock($name, $context, $blocks);
            TemplateEvent::blockPost($tpl);
        } else {
            parent::displayBlock($name, $context, $blocks);
        }
    }

    public function postConstruct()
    {
    }

}
