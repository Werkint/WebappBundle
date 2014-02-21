<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Router;
use Werkint\Bundle\WebappBundle\Annotation\Xmlhttp;

/**
 * AjaxFilter.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class AjaxFilter
{
    const ERROR_CODE = 403;

    protected $router;

    /**
     * @param Router $router
     */
    public function __construct(
        Router $router
    ) {
        $this->router = $router;
    }

    protected $init = false;

    /**
     * @param GetResponseEvent $event
     * @return bool
     * @throws HttpException
     */
    public function onRequest(GetResponseEvent $event)
    {
        if ($this->init) {
            return;
        }
        $this->init = true;

        $request = $event->getRequest();
        $route = $request->get('_route');
        $route = $this->router->getRouteCollection()->get($route);
        if ($route) {
            $reqs = $route->getRequirements();

            if (isset($reqs[Xmlhttp::KEYNAME])) {
                if ($reqs[Xmlhttp::KEYNAME] != $request->headers->get('X-Requested-With')) {
                    throw new HttpException(static::ERROR_CODE, 'Wrong request');
                }
            }
        }
    }

}
