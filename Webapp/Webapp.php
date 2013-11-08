<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

/**
 * Webapp.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
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

    public function addVars(array $vars)
    {
        foreach ($vars as $name => $var) {
            $this->addVar($name, $var);
        }
    }

}