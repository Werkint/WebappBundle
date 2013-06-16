<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

class Template
{
    const BLOCK_PREFIX = 'break_';

    protected $loader;

    public function __construct(
        ScriptLoader $loader
    ) {
        $this->handler = $loader;
    }

    public function templateDisplayPost(TemplateEvent $e)
    {
        $this->loader->attachViewRelated($e->templatePath);
    }

    public function templateBlockStart(TemplateEvent $e)
    {
        $name = $e->getBlockName();
        if (strpos($name, static::BLOCK_PREFIX) === 0) {
            $tpl = substr($name, strlen(static::BLOCK_PREFIX));
            $this->handler->blockStart($tpl);
        }
    }

    public function templateBlockEnd(TemplateEvent $e)
    {
        $name = $e->getBlockName();
        if (strpos($name, static::BLOCK_PREFIX) === 0) {
            $tpl = substr($name, strlen(static::BLOCK_PREFIX));
            $this->handler->blockEnd($tpl);
        }
    }
}
