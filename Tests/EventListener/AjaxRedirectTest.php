<?php
namespace Werkint\Bundle\WebappBundle\Tests\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\EventListener\AjaxException;
use Werkint\Bundle\WebappBundle\EventListener\AjaxRedirect;
use Werkint\Bundle\WebappBundle\EventListener\Request as BaseRequest;

/**
 * AjaxRedirectTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class AjaxRedirectTest extends \PHPUnit_Framework_TestCase
{
    const AJAX_TYPE = 'XMLHttpRequest';

    public function testSkip()
    {
        $obj = $this->getObject();
        $event = $this->getEvent(false, true);
        $this->assertFalse($obj->onKernelResponse($event));
        $event = $this->getEvent(true, false);
        $this->assertFalse($obj->onKernelResponse($event));
        $event = $this->getEvent(true, true, false);
        $this->assertFalse($obj->onKernelResponse($event));
    }

    public function testProcess()
    {
        $obj = $this->getObject();
        $event = $this->getEvent(true, true);

        $ret = $event->getResponse();
        $this->assertNotNull($ret->headers->has('Location'));
        $this->assertNull($obj->onKernelResponse($event));
        $this->assertEquals(200, $event->getResponse()->getStatusCode());
        $this->assertFalse($ret->headers->has('Location'));
        $this->assertTrue($ret->headers->has(BaseRequest::HEADER_NEEDREDIRECT));
    }

    /**
     * @param bool $real
     * @param bool $isRedirect
     * @param bool $pjax
     * @return GetResponseEvent
     */
    protected function getEvent($real = false, $isRedirect = true, $pjax = true)
    {
        $event = $this->getMock(
            'Symfony\Component\HttpKernel\Event\FilterResponseEvent',
            [], [], '', null
        );
        $req = new Request([], [], [], [], [], [
            'HTTP_' . BaseRequest::HEADER_PJAX => $pjax ? 'yes' : null,
        ]);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($req));
        $response = new Response('', $isRedirect ? 302 : 200, [
            'Location' => $isRedirect ? 'yes' : null,
        ]);
        $event
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $type = $real ? HttpKernelInterface::MASTER_REQUEST :
            HttpKernelInterface::SUB_REQUEST;
        $event
            ->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue($type));

        return $event;
    }

    /**
     * @return AjaxRedirect
     */
    protected function getObject()
    {
        return new AjaxRedirect();
    }

}
