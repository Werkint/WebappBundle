<?php
namespace Werkint\Bundle\WebappBundle\EventListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;

class ViewInjector {

	protected $templating;
	protected $webapp;
	protected $parameters;

	public function __construct(TwigEngine $templating, $webapp, $parameters) {
		$this->templating = $templating;
		$this->webapp = $webapp;
		$this->parameters = $parameters;
	}

	public function onKernelResponse(FilterResponseEvent $event) {
		if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
			return;
		}
		// do not capture redirects or modify XML HTTP Requests
		if ($event->getRequest()->isXmlHttpRequest() || $event->getResponse()->isRedirect()) {
			return;
		}

		$response = $event->getResponse();
		$content = $response->getContent();
		if (($pos = mb_strripos($content, '</head>')) !== false) {
			$data = array(
				'hash'    => $this->webapp->compile(),
				'respath' => $this->parameters['respath']
			);
			$code = $this->templating->render(
				'WerkintWebappBundle:Templates:head.twig', $data
			);
			$code = "\n" . str_replace("\n", '', $code) . "\n";
			$content = mb_substr($content, 0, $pos) . $code . mb_substr($content, $pos);
			$response->setContent($content);
		}
	}
}
