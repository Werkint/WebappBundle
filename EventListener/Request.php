<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoaderInterface;

/**
 * Request.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Request
{
    const HEADER_PACKAGES = 'X_MY_PACKAGES';
    const HEADER_PJAX = 'X_PJAX';
    const HEADER_NEEDREDIRECT = 'X_MY_NEEDREDIRECT';
    const DEFAULT_BLOCK = 'page';

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
     * @param GetResponseEvent $event
     * @return bool
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return false;
        }

        $request = $event->getRequest();
        $list = ['jquery'];

        $listPjax = $request->server->get('HTTP_' . static::HEADER_PACKAGES);
        if ($listPjax) {
            $list = array_merge($list, json_decode($listPjax));
        }

        foreach ($list as $name) {
            $this->loader->addPackage($name, static::DEFAULT_BLOCK);
        }
    }

}
