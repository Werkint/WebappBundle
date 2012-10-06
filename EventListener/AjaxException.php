<?php
namespace Werkint\Bundle\WebappBundle\EventListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;

class AjaxException {

	public function onKernelException(GetResponseForExceptionEvent $event) {
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}

		if (!$event->getRequest()->isXmlHttpRequest() || !APP_DEBUG) {
			return;
		}
		$exception = $event->getException();

		$response = new Response();
		$response->setContent(json_encode(array(
			'code'     => $exception->getCode(),
			'message'  => $exception->getMessage(),
			'trace'    => $exception->getTraceAsString(),
		)));
		$event->setResponse($response);
	}
}