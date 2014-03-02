<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * AjaxException.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class AjaxException
{
    protected $isDebug;

    /**
     * @param bool $isDebug
     */
    public function __construct(
        $isDebug = false
    ) {
        $this->isDebug = $isDebug;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     * @return bool
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return false;
        }

        if (!$event->getRequest()->isXmlHttpRequest() || !$this->isDebug) {
            return false;
        }
        $exception = $event->getException();

        $response = new Response();
        $response->setContent(json_encode([
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'trace'   => $exception->getTraceAsString(),
        ]));
        $event->setResponse($response);
    }

}