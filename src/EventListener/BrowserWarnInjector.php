<?php
namespace Werkint\Bundle\WebappBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\EngineInterface;
use Werkint\Bundle\WebappBundle\Webapp\BrowserCheck;
use Werkint\Bundle\WebappBundle\Webapp\Compiler;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * BrowserWarnInjector.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class BrowserWarnInjector
{
    const TEMPLATE = 'WerkintWebappBundle:Templates:browserwarn.twig';
    const TAG_BODY = '</body>';

    protected $templating;
    protected $check;

    /**
     * @param EngineInterface $templating
     * @param BrowserCheck    $check
     */
    public function __construct(
        EngineInterface $templating,
        BrowserCheck $check
    ) {
        $this->templating = $templating;
        $this->check = $check;
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

        if ($event->getRequest()->isXmlHttpRequest()) {
            return false;
        }

        if (!$this->check->isOld()) {
            return false;
        }

        $pos = mb_strrpos($content, static::TAG_BODY);
        if ($pos === false) {
            return false;
        }
        $code = $this->templating->render(static::TEMPLATE, [
            'browser' => $this->check->getBrowserName(),
            'cookie'  => $this->check->getCookieName(),
        ]);
        $code = "\n" . str_replace("\n", '', $code) . "\n";
        $content = mb_substr($content, 0, $pos) . $code . mb_substr($content, $pos);
        $response->setContent($content);
    }

}
