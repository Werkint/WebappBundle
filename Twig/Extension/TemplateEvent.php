<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * TemplateEvent.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class TemplateEvent extends Event
{
    protected $dispatcher;

    public function __construct(
        EventDispatcher $dispatcher
    ) {
        $this->dispatcher = $dispatcher;
    }

    public function dispatch($eventName)
    {
        return $this->dispatcher->dispatch(
            $eventName,
            $this
        );
    }

    public $templateName;
    public $templatePath;
    public $blockName;

    // -- Getters/Setters ---------------------------------------

    /**
     * @param mixed $blockName
     * @return $this
     */
    public function setBlockName($blockName)
    {
        $this->blockName = $blockName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBlockName()
    {
        return $this->blockName;
    }

    /**
     * @param mixed $templateName
     * @return $this
     */
    public function setTemplateName($templateName)
    {
        $this->templateName = $templateName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }

    /**
     * @param mixed $templatePath
     * @return $this
     */
    public function setTemplatePath($templatePath)
    {
        $this->templatePath = $templatePath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplatePath()
    {
        return $this->templatePath;
    }


}