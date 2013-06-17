<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

class Request
{
    const HEADER_PACKAGES = 'X_MY_PACKAGES';
    const HEADER_PJAX = 'X_PJAX';
    const HEADER_NEEDREDIRECT = 'X_MY_NEEDREDIRECT';

    protected $loader;

    public function __construct(
        ScriptLoader $loader
    ) {
        $this->loader = $loader;
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $list = $request->server->get('HTTP_'.static::HEADER_PACKAGES);
        if ($list) {
            $list = json_decode($list);
            foreach ($list as $name) {
                $this->loader->addPackage($name, 'page');
            }
        }
    }
}
