<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
