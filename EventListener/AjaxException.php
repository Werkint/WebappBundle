<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class AjaxException
{

    protected $isDebug;

    public function __construct($isDebug)
    {
        $this->isDebug = $isDebug;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (!$event->getRequest()->isXmlHttpRequest() || !$this->isDebug) {
            return;
        }
        $exception = $event->getException();

        $response = new Response();
        $response->setContent(json_encode(array(
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'trace'   => $exception->getTraceAsString(),
        )));
        $event->setResponse($response);
    }
}
