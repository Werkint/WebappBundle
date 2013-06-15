<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Symfony\Component\EventDispatcher\Event;

class TemplateEvent extends Event
{

    public $templateName;
    public $templatePath;
    public $data;

    protected static function getEvent()
    {
        return new static();
    }

    protected function dispatch($eventName)
    {
        Extension::$dispatcher->dispatch(
            $eventName,
            $this
        );
    }

    public static function blockPre($name)
    {
        static::getEvent()
            ->setData($name)
            ->dispatch('werkint.webapp.blockpre');
    }

    public static function blockPost($name)
    {
        static::getEvent()
            ->setData($name)
            ->dispatch('werkint.webapp.blockpost');
    }

    public static function postConstruct($name, $path)
    {
        static::getEvent()
            ->setTemplateName($name)
            ->setTemplatePath($path)
            ->dispatch('werkint.webapp.postconstruct');
    }

    // -- Getters/Setters ---------------------------------------

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
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