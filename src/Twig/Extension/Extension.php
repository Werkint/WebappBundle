<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoaderInterface;

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
    public $loader;

    /**
     * @param ScriptLoaderInterface    $loader
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ScriptLoaderInterface $loader,
        EventDispatcherInterface $dispatcher
    ) {
        $this->loader = $loader;
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
    public function getName()
    {
        return self::EXT_NAME;
    }

}
