<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\Webapp\Compiler;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

class ViewInjector
{

    protected $templating;
    protected $loader;
    protected $compiler;
    protected $respath;

    public function __construct(
        TwigEngine $templating,
        ScriptLoader $loader,
        Compiler $compiler,
        $parameters
    ) {
        $this->templating = $templating;
        $this->loader = $loader;
        $this->compiler = $compiler;
        $this->respath = $parameters['respath'];
    }

    protected function getTemplateData()
    {
        $blocks = $this->compiler->compile($this->loader);
        return [
            'blocks'  => $blocks,
            'respath' => $this->respath,
            'prefix'  => 'webapp_res_',
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        // do not capture redirects
        if ($response->isRedirect()) {
            if ($request->server->get('HTTP_' . Request::HEADER_PJAX)) {
                $response->setStatusCode(200);
                $response->headers->set(
                    Request::HEADER_NEEDREDIRECT,
                    $response->headers->get('Location')
                );
                $response->headers->remove('Location');
                $response->setContent('<p>Перенаправление...</p>');
            }
            return;
        }

        $content = $response->getContent();
        $data = $this->getTemplateData();

        if ($event->getRequest()->isXmlHttpRequest()) {
            if (($pos = mb_strrpos($content, '[[PAGEPATH]]')) !== false) {
                $data = json_encode($data);
                $content = mb_substr($content, 0, $pos) . $data . mb_substr($content, $pos + strlen('[[PAGEPATH]]'));
                $response->setContent($content);
            }
        } else {
            if (($pos = mb_strrpos($content, '</head>')) !== false) {
                $code = $this->templating->render(
                    'WerkintWebappBundle:Templates:head.twig', $data
                );
                $code = "\n" . str_replace("\n", '', $code) . "\n";
                $content = mb_substr($content, 0, $pos) . $code . mb_substr($content, $pos);
                $response->setContent($content);
            }
        }
    }
}
