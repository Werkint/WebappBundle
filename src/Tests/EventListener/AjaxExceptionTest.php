<?php
namespace Werkint\Bundle\WebappBundle\Tests\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\EventListener\AjaxException;

/**
 * AjaxExceptionTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class AjaxExceptionTest extends \PHPUnit_Framework_TestCase
{
    const AJAX_TYPE = 'XMLHttpRequest';

    public function testSkip()
    {
        $obj = $this->getObject();
        $event = $this->getEvent();
        $this->assertFalse($obj->onKernelException($event));
        $event = $this->getEvent(true, static::AJAX_TYPE);
        $this->assertFalse($obj->onKernelException($event));
        $obj = $this->getObject(true);
        $event = $this->getEvent(true, 'foo_wrong_type');
        $this->assertFalse($obj->onKernelException($event));
    }

    public function testProcess()
    {
        $obj = $this->getObject(true);
        $event = $this->getEvent(true, static::AJAX_TYPE);

        $e = new \Exception('test exception', 666);
        $event
            ->expects($this->any())
            ->method('getException')
            ->will($this->returnValue($e));
        $response = null;
        $event
            ->expects($this->any())
            ->method('setResponse')
            ->will($this->returnCallback(function (Response $ret) use (&$response) {
                $response = $ret;
            }));

        $this->assertNull($obj->onKernelException($event));

        $ret = json_decode($response->getContent(), true);
        $this->assertNotNull($ret, 'json_decode not worked');
        $this->assertArrayHasKey('code', $ret);
        $this->assertEquals(666, $ret['code'], 'exception code is wrong');
    }

    /**
     * @param bool        $real
     * @param string|null $type
     * @return GetResponseEvent
     */
    protected function getEvent($real = false, $type = null)
    {
        $event = $this->getMock(
            'Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent',
            [], [], '', null
        );
        $req = new Request([], [], [], [], [], [
            'HTTP_X-Requested-With' => $type,
        ]);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($req));

        $type = $real ? HttpKernelInterface::MASTER_REQUEST :
            HttpKernelInterface::SUB_REQUEST;
        $event
            ->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue($type));

        return $event;
    }

    /**
     * @param bool $isDebug
     * @return AjaxException
     */
    protected function getObject($isDebug = false)
    {
        return new AjaxException($isDebug);
    }

}
