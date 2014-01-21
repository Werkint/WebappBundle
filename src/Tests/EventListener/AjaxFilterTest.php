<?php
namespace Werkint\Bundle\WebappBundle\Tests\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Route;
use Werkint\Bundle\WebappBundle\Annotation\Xmlhttp;
use Werkint\Bundle\WebappBundle\EventListener\AjaxFilter;

/**
 * AjaxFilterTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class AjaxFilterTest extends \PHPUnit_Framework_TestCase
{
    /** @var AjaxFilter */
    protected $listener;

    public function setUp()
    {
        $router = $this->getMock(
            'Symfony\Component\Routing\Router',
            [], [], '', false
        );
        $router
            ->expects($this->any())
            ->method('getRouteCollection')
            ->will($this->returnValue(new ParameterBag([
                'fooroute1' => $this->getRoute('fooreq'),
                'fooroute2' => $this->getRoute('fooreq_wrong'),
            ])));
        $this->listener = new AjaxFilter($router);
    }

    public function testSkip()
    {
        $req = $this->listener;

        $event = $this->getEvent();
        $this->assertFalse($req->onKernelController($event));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testProcessException()
    {
        $req = $this->listener;

        $event = $this->getEvent(true, 'fooroute2');
        $this->assertNull($req->onKernelController($event));
    }

    /**
     * @depends testProcessException
     */
    public function testProcess()
    {
        $req = $this->listener;

        $event = $this->getEvent(true, 'fooroute1');
        $this->assertNull($req->onKernelController($event));
    }

    /**
     * @param bool        $real
     * @param string|null $route
     * @return GetResponseEvent
     */
    protected function getEvent($real = false, $route = null)
    {
        $event = $this->getMock(
            'Symfony\Component\HttpKernel\Event\FilterControllerEvent',
            [], [], '', false
        );
        $req = new BaseRequest([
            '_route' => $route,
        ], [], [], [], [], [
            'HTTP_X-Requested-With' => 'fooreq',
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
     * @param string $reqtype
     * @return Route
     */
    protected function getRoute($reqtype)
    {
        $route = $this->getMock(
            'Symfony\Component\Routing\Route',
            [], [], '', false
        );
        $route
            ->expects($this->any())
            ->method('getRequirements')
            ->will($this->returnValue([
                Xmlhttp::KEYNAME => $reqtype,
            ]));
        return $route;
    }

}
