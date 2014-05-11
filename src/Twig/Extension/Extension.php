<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoaderInterface;
use Werkint\Bundle\WebappBundle\Webapp\WebappInterface;

/**
 * Extension.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Extension extends \Twig_Extension
{
    const EXT_NAME = 'werkint.webapp';
    const VAR_PREFIX = 'const';

    public $dispatcher;
    public $webapp;
    public $loader;

    /**
     * @param ScriptLoaderInterface    $loader
     * @param WebappInterface          $webapp
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ScriptLoaderInterface $loader,
        WebappInterface $webapp,
        EventDispatcherInterface $dispatcher
    ) {
        $this->loader = $loader;
        $this->webapp = $webapp;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return TemplateEvent
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
            static::VAR_PREFIX => $this->loader->getVariables(ScriptLoaderInterface::ROOT_BLOCK),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('webapp_head_init', function () {
                return '<!--webapp-' . $this->webapp->getHash() . '-->';
            }, ['is_safe' => ['html']]),
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
