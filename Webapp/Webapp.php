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

    /**
     * @param ScriptLoader $loader
     */
    public function __construct(
        ScriptLoader $loader
    ) {
        $this->loader = $loader;
    }

    /**
     * @return ScriptLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @param string $name
     */
    public function attachFile($name)
    {
        $this->loader->attachFile($name);
    }

    /**
     * @param string $url
     */
    public function addImportCss($url)
    {
        $this->loader->addImport($url, 'css');
    }

    /**
     * @param string $url
     */
    public function addImportJs($url)
    {
        $this->loader->addImport($url, 'js');
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function addVar($name, $value)
    {
        $this->loader->addVar($name, $value);
    }

    /**
     * @param bool $flag
     */
    public function setIsSplit($flag)
    {
        $this->loader->setIsSplit($flag);
    }

    /**
     * @param array $vars
     */
    public function addVars(array $vars)
    {
        foreach ($vars as $name => $var) {
            $this->addVar($name, $var);
        }
    }

}
