<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

use Symfony\Component\DependencyInjection\Container;

class Webapp
{

    protected $params;
    protected $loader;
    protected $handler;

    protected $isDebug;

    public function __construct(
        $params, $isDebug, $appmode
    ) {
        $this->params = $params;
        $this->handler = new ScriptHandler();

        $this->loader = new ScriptLoader(
            $this->handler,
            $this->params['resdir'],
            $appmode,
            $this->params['scripts']
        );
        $this->handler->blockStart('_root');

        $this->handler->addVar('webapp-res', $this->params['respath']);
        $this->isDebug = $isDebug;
    }

    public function getLoader()
    {
        return $this->loader;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function attach($name)
    {
        $this->loader->attach($name);
    }

    public function attachFile($name)
    {
        $this->loader->attachFile($name);
    }

    public function addCssImport($url)
    {
        $this->handler->addCssImport($url);
    }

    public function compile($block = null)
    {
        $this->handler->blockEnd();
        $compiler = new Compiler(
            $this->handler, $this->params['resdir'], $this->isDebug
        );
        $revision = substr(crc32(file_exists($this->params['revpath']) ?
            file_get_contents($this->params['revpath']) : ''), 0, 6);
        return $compiler->compile($revision, $block);
    }

    public function getVars()
    {
        return $this->handler->getVariables();
    }

    public function addVar($name, $value)
    {
        $this->handler->addVar($name, $value);
    }

    public function addVars(array $vars)
    {
        foreach ($vars as $name => $var) {
            $this->addVar($name, $var);
        }
    }

}