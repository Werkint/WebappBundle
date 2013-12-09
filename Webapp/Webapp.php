<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

/**
 * Webapp.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Webapp implements
    WebappInterface
{
    protected $loader;

    /**
     * @param ScriptLoaderInterface $loader
     */
    public function __construct(
        ScriptLoaderInterface $loader
    ) {
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * {@inheritdoc}
     */
    public function attachFile($name)
    {
        $this->loader->attachFile($name);
    }

    /**
     * {@inheritdoc}
     */
    public function addImportCss($url)
    {
        $this->loader->addImport($url, ScriptLoader::TYPE_CSS);
    }

    /**
     * {@inheritdoc}
     */
    public function addImportJs($url)
    {
        $this->loader->addImport($url, ScriptLoader::TYPE_JS);
    }

    /**
     * {@inheritdoc}
     */
    public function addVar($name, $value)
    {
        $this->loader->addVar($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsSplit($flag)
    {
        $this->loader->setIsSplit($flag);
    }

    /**
     * {@inheritdoc}
     */
    public function addVars(array $vars)
    {
        foreach ($vars as $name => $var) {
            $this->addVar($name, $var);
        }
    }

}
