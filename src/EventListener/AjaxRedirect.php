<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * AjaxRedirect.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class AjaxRedirect
{
    /**
     * @param FilterResponseEvent $event
     * @return bool
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return false;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        //  capture redirects
        if (!$response->isRedirect()) {
            return false;
        }
        if (!$request->server->get('HTTP_' . Request::HEADER_PJAX)) {
            return false;
        }
        $response->setStatusCode(200);
        $response->headers->set(
            Request::HEADER_NEEDREDIRECT,
            $response->headers->get('Location')
        );
        $response->headers->remove('Location');
        $response->setContent('<p>Перенаправление...</p>');
    }

}
