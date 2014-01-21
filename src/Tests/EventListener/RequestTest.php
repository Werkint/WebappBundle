<?php
namespace Werkint\Bundle\WebappBundle\Tests\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\EventListener\Request;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * RequestTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /** @var Request */
    protected $listener;
    /** @var ScriptLoader */
    protected $loader;

    public function setUp()
    {
        $this->loader = new ScriptLoader(false, 'foo');
        $this->listener = new Request($this->loader);
    }

    public function testSkip()
    {
        $req = $this->listener;
        $event = $this->getEvent();

        $this->assertFalse($req->onRequest($event));
    }

    public function testProcess()
    {
        $req = $this->listener;
        $event = $this->getEvent(true);

        $this->assertNull($req->onRequest($event));
        $list = $this->loader->getPackages(Request::DEFAULT_BLOCK);
        $this->assertGreaterThanOrEqual(2, count($list));
        $this->assertTrue(in_array('foobar', $list));
        $this->assertTrue(in_array('foobar2', $list));
    }

    /**
     * @param bool $real
     * @return GetResponseEvent
     */
    protected function getEvent($real = false)
    {
        $event = $this->getMock(
            'Symfony\Component\HttpKernel\Event\GetResponseEvent',
            [], [], '', null
        );
        $req = new BaseRequest([], [], [], [], [], [
            'HTTP_' . Request::HEADER_PACKAGES => json_encode([
                'foobar', 'foobar2'
            ]),
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

}
