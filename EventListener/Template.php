<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * Template.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Template
{
    const BLOCK_PREFIX = 'break_';

    protected $loader;

    public function __construct(
        ScriptLoader $loader
    ) {
        $this->loader = $loader;
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
            $this->loader->blockStart($tpl);
        }
    }

    public function templateBlockEnd(TemplateEvent $e)
    {
        $name = $e->getBlockName();
        if (strpos($name, static::BLOCK_PREFIX) === 0) {
            $tpl = substr($name, strlen(static::BLOCK_PREFIX));
            $this->loader->blockEnd($tpl);
        }
    }
}
