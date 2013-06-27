<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig_Extension;
use Werkint\Bundle\WebappBundle\Webapp\Webapp;

class Extension extends Twig_Extension
{
    const EXT_NAME = 'werkint.webapp';

    public $dispatcher;
    public $webapp;

    public function __construct(
        Webapp $webapp,
        EventDispatcher $dispatcher
    ) {
        $this->webapp = $webapp;
        $this->dispatcher = $dispatcher;
    }

    public function getTemplateEvent()
    {
        return new TemplateEvent(
            $this->dispatcher
        );
    }

    public function getGlobals()
    {
        return [
            'const' => $this->webapp->getLoader()->getVariables('_root'),
        ];
    }

    public function getName()
    {
        return self::EXT_NAME;
    }
}
