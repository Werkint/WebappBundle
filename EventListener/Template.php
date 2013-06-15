<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent;
use Werkint\Bundle\WebappBundle\Webapp\Webapp;

class Template
{
    const BLOCK_PREFIX = 'break_';

    protected $handler;
    protected $loader;

    public function __construct(
        Webapp $webapp
    ) {
        $this->handler = $webapp->getHandler();
        $this->loader = $webapp->getLoader();
    }

    public function templateConstruct(TemplateEvent $e)
    {
        $this->loader->attachRelated($e->templatePath);
    }

    public function templateBlockStart(TemplateEvent $e)
    {
        $name = $e->getData();
        if (strpos($name, static::BLOCK_PREFIX) === 0) {
            $tpl = substr($name, strlen(static::BLOCK_PREFIX));
            $this->handler->blockStart($tpl);
        }
    }

    public function templateBlockEnd(TemplateEvent $e)
    {
        $name = $e->getData();
        if (strpos($name, static::BLOCK_PREFIX) === 0) {
            $this->handler->blockEnd();
        }
    }
}
