<?php
namespace Werkint\Bundle\WebappBundle\Twig;

use \Twig_Extension,
    \Twig_SimpleFunction,
    \Twig_SimpleFilter;

abstract class AbstractExtension extends Twig_Extension
{
    const EXT_NAME = 'undefined';

    public function getName()
    {
        return static::EXT_NAME;
    }

    protected $filters = [];

    public function addFilter($name, $isSafe, callable $callable)
    {
        $safe = ['is_safe' => ['all']];
        $this->filters[$name] = new Twig_SimpleFilter($name, $callable, $isSafe ? $safe : []);
    }

    /**
     * @param $name
     * @return callable
     */
    public function getFilter($name)
    {
        $filter = $this->filters[$name];
        /** @var Twig_SimpleFilter $filter */
        return $filter->getCallable();
    }

    public function getFilters()
    {
        return $this->filters;
    }

    protected $functions = [];

    public function addFunction($name, $isSafe, callable $callable)
    {
        $safe = ['is_safe' => ['all']];
        $this->functions[$name] = new Twig_SimpleFunction($name, $callable, $isSafe ? $safe : []);
    }

    public function getFunctions()
    {
        $this->functions;
    }

    protected $globals = [];

    public function getGlobals()
    {
        return $this->globals;
    }

}
