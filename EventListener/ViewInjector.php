<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\Webapp\Compiler\Compiler;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * ViewInjector.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ViewInjector
{
    protected $templating;
    protected $loader;
    protected $compiler;
    protected $respath;

    /**
     * @param TwigEngine   $templating
     * @param ScriptLoader $loader
     * @param Compiler     $compiler
     * @param array        $parameters
     */
    public function __construct(
        TwigEngine $templating,
        ScriptLoader $loader,
        Compiler $compiler,
        array $parameters
    ) {
        $this->templating = $templating;
        $this->loader = $loader;
        $this->compiler = $compiler;
        $this->respath = $parameters['respath'];
    }

    /**
     * @return array
     */
    protected function getTemplateData()
    {
        $blocks = $this->compiler->compile($this->loader);
        return [
            'blocks'   => $blocks,
            'packages' => $this->loader->getPackages('page'),
            'respath'  => $this->respath,
            'prefix'   => 'webapp_res_',
        ];
    }

    /**
     * @param FilterResponseEvent $event
     * @return bool
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return false;
        }

        $response = $event->getResponse();
        $content = $response->getContent();
        $data = $this->getTemplateData();

        // TODO: add custom tag for injection
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
