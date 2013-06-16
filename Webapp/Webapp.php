<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

class Webapp
{

    protected $loader;

    public function __construct(
        ScriptLoader $loader
    ) {
        $this->loader = $loader;
    }

    public function attachFile($name)
    {
        $this->loader->attachFile($name);
    }

    public function addCssImport($url)
    {
        $this->loader->addCssImport($url);
    }

    public function addVar($name, $value)
    {
        $this->loader->addVar($name, $value);
    }

}