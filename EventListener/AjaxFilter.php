<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
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

    /**
     * @param FilterControllerEvent $event
     * @throws HttpException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->get('_route');
        $route = $this->router->getRouteCollection()->get($route);
        $reqs = $route->getRequirements();

        if (isset($reqs[Xmlhttp::KEYNAME])) {
            if ($reqs[Xmlhttp::KEYNAME] != $request->headers->get('X-Requested-With')) {
                throw new HttpException(static::ERROR_CODE);
            }
        }
    }

}
