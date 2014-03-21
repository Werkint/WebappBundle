<?php
namespace Werkint\Bundle\WebappBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Werkint\Bundle\WebappBundle\EventListener\ViewInjector;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * ViewInjectorTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ViewInjectorTest extends \PHPUnit_Framework_TestCase
{
    const AJAX_TYPE = 'XMLHttpRequest';

    public function testSkip()
    {
        $obj = $this->getObject();
        $event = $this->getEvent(false);
        $this->assertFalse($obj->onKernelResponse($event));
        $event = $this->getEvent(true);
        $this->assertFalse($obj->onKernelResponse($event));
        $event = $this->getEvent(true, static::AJAX_TYPE);
        $this->assertFalse($obj->onKernelResponse($event));
    }

    public function testAjax()
    {
        $obj = $this->getObject([
            'param1' => 'test',
        ]);
        $data = ViewInjector::TAG_AJAX;
        $event = $this->getEvent(true, static::AJAX_TYPE, $data);
        $this->assertNull($obj->onKernelResponse($event));

        $ret = $event->getResponse()->getContent();
        $ret = json_decode($ret, true);
        $this->assertNotNull($ret, 'Wrong data passed from injector');
        $this->assertArrayHasKey('param1', $ret);
        $this->assertArrayHasKey('packages', $ret);
        $this->assertTrue(in_array('foo_test', $ret['packages']));
    }

    public function testHead()
    {
        $obj = $this->getObject([
            'param1' => 'test',
        ]);
        $data = ViewInjector::TAG_HEAD;
        $event = $this->getEvent(true, null, $data);
        $this->assertNull($obj->onKernelResponse($event));

        $ret = $event->getResponse()->getContent();
        $ret = substr($ret, 0, strlen($ret) - strlen(ViewInjector::TAG_HEAD));
        $ret = @unserialize(trim($ret));
        $this->assertTrue($ret !== false, 'Wrong data passed from injector');
        $this->assertArrayHasKey('param1', $ret);
        $this->assertArrayHasKey('packages', $ret);
        $this->assertTrue(in_array('foo_test', $ret['packages']));
    }

    /**
     * @param bool        $real
     * @param string|null $type
     * @param string      $data
     * @return FilterResponseEvent
     */
    protected function getEvent($real = false, $type = null, $data = '')
    {
        $event = $this->getMock(
            'Symfony\Component\HttpKernel\Event\FilterResponseEvent',
            [], [], '', null
        );
        $req = new Request([], [], [], [], [], [
            'HTTP_X-Requested-With' => $type,
        ]);
        $event
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($req));
        $event
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue(new Response($data)));

        $type = $real ? HttpKernelInterface::MASTER_REQUEST :
            HttpKernelInterface::SUB_REQUEST;
        $event
            ->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue($type));

        return $event;
    }

    /**
     * @param array $parameters
     * @param array $blocks
     * @return ViewInjector
     */
    protected function getObject(array $parameters = [], array $blocks = [])
    {
        $compiler = $this->getMock(
            'Werkint\Bundle\WebappBundle\Webapp\Compiler',
            [], [], '', false
        );
        $loader = new ScriptLoader();
        $loader->addPackage('foo_test');
        $compiler
            ->expects($this->any())
            ->method('compile')
            ->with($this->equalTo($loader))
            ->will($this->returnValue($blocks));
        return new ViewInjector(
            new StubTwigEngine([
                ViewInjector::TEMPLATE => $this->getTemplatePath(),
            ]),
            $loader,
            $compiler,
            $parameters
        );
    }

    /**
     * @return string
     */
    protected function getTemplatePath()
    {
        return __DIR__ . '../../Resources/views/Templates/head.twig';
    }

}
