<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig_Extension;
use Werkint\Bundle\WebappBundle\Webapp\Webapp;

/**
 * Extension.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Extension extends Twig_Extension
{
    const EXT_NAME = 'werkint.webapp';

    public $dispatcher;
    public $webapp;

    /**
     * @param Webapp          $webapp
     * @param EventDispatcher $dispatcher
     */
    public function __construct(
        Webapp $webapp,
        EventDispatcher $dispatcher
    ) {
        $this->webapp = $webapp;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateEvent()
    {
        return new TemplateEvent(
            $this->dispatcher
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return [
            'const' => $this->webapp->getLoader()->getVariables('_root'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::EXT_NAME;
    }

}
