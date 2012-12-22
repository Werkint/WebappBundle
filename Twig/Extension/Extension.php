<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Symfony\Component\EventDispatcher\EventDispatcher,
    Werkint\Bundle\WebappBundle\Webapp\Webapp,
    \Twig_Extension;

class Extension extends Twig_Extension
{

    /**
     * @var EventDispatcher
     */
    public static $dispatcher;
    public $webapp;

    public function __construct(Webapp $webapp, EventDispatcher $dispatcher)
    {
        $this->webapp = $webapp;
        static::$dispatcher = $dispatcher;
    }

    public function getNodeVisitors()
    {
        return array(
            new NodeVisitor()
        );
    }

    public function getGlobals()
    {
        return array(
            'const' => $this->webapp->getVars()
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'werkint.webapp';
    }
}
