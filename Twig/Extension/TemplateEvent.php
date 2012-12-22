<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;
use Symfony\Component\EventDispatcher\Event;

class TemplateEvent extends Event
{

    public $templateName;
    public $templatePath;

    public static function postConstruct($name, $path)
    {
        $event = new static();
        $event->templateName = $name;
        $event->templatePath = $path;
        Extension::$dispatcher->dispatch('werkint.webapp.postconstruct', $event);
    }

}