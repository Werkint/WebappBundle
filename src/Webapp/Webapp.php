<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\TemplateNameParserInterface;

/**
 * Webapp.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Webapp implements
    WebappInterface
{
    protected $loader;
    protected $nameParser;
    protected $nameLoader;

    /**
     * @param ScriptLoaderInterface       $loader
     * @param TemplateNameParserInterface $nameParser
     * @param LoaderInterface $nameLoader
     */
    public function __construct(
        ScriptLoaderInterface $loader,
        TemplateNameParserInterface $nameParser,
    LoaderInterface $nameLoader
    ) {
        $this->loader = $loader;
        $this->nameParser = $nameParser;
        $this->nameLoader = $nameLoader;
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
    public function attachByTwigAlias($name, $ext)
    {
        $template = $this->nameParser->parse($name);
        $name = $this->nameLoader->load($template);
        $name = explode('.', $name);
        array_pop($name);
        $name[] = $ext;
        $this->loader->attachFile(join('.', $name));
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
    public function addVar($name, $value, $isRoot = false)
    {
        if ($isRoot) {
            $this->loader->blockStart('_root');
        }
        $this->loader->addVar($name, $value);
        if ($isRoot) {
            $this->loader->blockEnd();
        }
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
