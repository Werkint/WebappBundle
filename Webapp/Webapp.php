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

    public function getLoader()
    {
        return $this->loader;
    }

    public function attachFile($name)
    {
        $this->loader->attachFile($name);
    }

    public function addImportCss($url)
    {
        $this->loader->addImport($url, 'css');
    }

    public function addImportJs($url)
    {
        $this->loader->addImport($url, 'js');
    }

    public function addVar($name, $value)
    {
        $this->loader->addVar($name, $value);
    }

    public function setIsSplit($flag)
    {
        $this->loader->setIsSplit($flag);
    }

}