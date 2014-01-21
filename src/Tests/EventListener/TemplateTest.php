<?php
namespace Werkint\Bundle\WebappBundle\Tests\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request as BaseRequest;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Werkint\Bundle\WebappBundle\EventListener\Template;

/**
 * TemplateTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testDisplay()
    {
        $loader = $this->getMock(
            'Werkint\Bundle\WebappBundle\Webapp\ScriptLoader',
            [], [], '', false
        );
        $loader
            ->expects($this->once())
            ->method('attachViewRelated')
            ->with($this->equalTo('foobar'))
            ->will($this->returnValue(null));
        $event = $this->getMock(
            'Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent',
            [], [], '', false
        );
        $event
            ->expects($this->once())
            ->method('getTemplatePath')
            ->will($this->returnValue('foobar'));
        $obj = new Template($loader);
        $obj->templateDisplayPost($event);
    }

    public function testNonBreak()
    {
        $loader = $this->getMock(
            'Werkint\Bundle\WebappBundle\Webapp\ScriptLoader',
            [], [], '', false
        );
        $event = $this->getMock(
            'Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent',
            [], [], '', false
        );
        $event
            ->expects($this->exactly(2))
            ->method('getBlockName')
            ->will($this->returnValue('.' . Template::BLOCK_PREFIX));
        $obj = new Template($loader);
        $this->assertNull($obj->templateBlockStart($event));
        $this->assertNull($obj->templateBlockEnd($event));
    }

    public function testBreak()
    {
        $loader = $this->getMock(
            'Werkint\Bundle\WebappBundle\Webapp\ScriptLoader',
            [], [], '', false
        );
        $loader
            ->expects($this->once())
            ->method('blockStart')
            ->with($this->equalTo('foobar'))
            ->will($this->returnValue(null));
        $loader
            ->expects($this->once())
            ->method('blockEnd')
            ->with($this->equalTo('foobar'))
            ->will($this->returnValue(null));
        $event = $this->getMock(
            'Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent',
            [], [], '', false
        );
        $event
            ->expects($this->exactly(2))
            ->method('getBlockName')
            ->will($this->returnValue(Template::BLOCK_PREFIX . 'foobar'));
        $obj = new Template($loader);
        $this->assertTrue($obj->templateBlockStart($event));
        $this->assertTrue($obj->templateBlockEnd($event));
    }

}
