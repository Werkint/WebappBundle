<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;
use Werkint\Bundle\WebappBundle\Webapp\Compiler;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoaderInterface;
use Werkint\Bundle\WebappBundle\Webapp\WebappInterface;

/**
 * ViewInjector.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ViewInjector
{
    const TEMPLATE = 'WerkintWebappBundle:Templates:head.twig';
    const TAG_AJAX = '[[PAGEPATH]]';
    const PREFIX = 'webapp_res_';

    protected $templating;
    protected $loader;
    protected $compiler;
    protected $webapp;
    protected $parameters;

    public function __construct(
        EngineInterface $templating,
        ScriptLoaderInterface $loader,
        Compiler $compiler,
        WebappInterface $webapp,
        array $parameters
    ) {
        $this->templating = $templating;
        $this->loader = $loader;
        $this->compiler = $compiler;
        $this->webapp = $webapp;
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    protected function getTemplateData()
    {
        $blocks = $this->compiler->compile($this->loader);
        return array_merge($this->parameters, [
            'blocks'   => $blocks,
            'packages' => $this->loader->getPackages('page'),
            'prefix'   => static::PREFIX,
        ]);
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

        // TODO: set custom tags for injection
        if ($event->getRequest()->isXmlHttpRequest()) {
            $pos = mb_strrpos($content, static::TAG_AJAX);
            if ($pos === false) {
                return false;
            }
            $data = json_encode($data);
            $content = mb_substr($content, 0, $pos) . $data . mb_substr($content, $pos + strlen(static::TAG_AJAX));
            $response->setContent($content);
        } else {
            $posStr = '<!--webapp-' . $this->webapp->getHash() . '-->';
            $pos = mb_strrpos($content, $posStr);
            if ($pos === false) {
                return false;
            }
            $code = $this->templating->render(static::TEMPLATE, $data);
            $code = "\n" . str_replace("\n", '', $code) . "\n";
            $content = mb_substr($content, 0, $pos) . $code . mb_substr($content, $pos + strlen($posStr));
            $response->setContent($content);
        }
    }

}
