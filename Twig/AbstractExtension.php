<?php
namespace Werkint\Bundle\WebappBundle\Twig;

use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

/**
 * AbstractExtension.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
abstract class AbstractExtension extends Twig_Extension
{
    const EXT_NAME = 'undefined';

    /**
     * @return string
     */
    public function getName()
    {
        return static::EXT_NAME;
    }

    /** @var Twig_SimpleFilter[] */
    protected $filters = [];

    /**
     * @param string   $name
     * @param bool     $isSafe
     * @param callable $callable
     */
    public function addFilter(
        $name,
        $isSafe,
        callable $callable
    ) {
        $safe = ['is_safe' => ['all']];
        $this->filters[$name] = new Twig_SimpleFilter($name, $callable, $isSafe ? $safe : []);
    }

    /**
     * @param string $name
     * @return callable
     */
    public function getFilter($name)
    {
        return $this->filters[$name]->getCallable();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /** @var Twig_SimpleFunction[] */
    protected $functions = [];

    /**
     * @param string   $name
     * @param bool     $isSafe
     * @param callable $callable
     */
    public function addFunction(
        $name,
        $isSafe,
        callable $callable
    ) {
        $safe = ['is_safe' => ['all']];
        $this->functions[$name] = new Twig_SimpleFunction($name, $callable, $isSafe ? $safe : []);
    }

    /**
     * @param string $name
     * @return callable
     */
    public function getFunction($name)
    {
        return $this->functions[$name]->getCallable();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    protected $globals = [];

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return $this->globals;
    }

}
