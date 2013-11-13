<?php
namespace Werkint\Bundle\WebappBundle\Tests\Twig\Extension;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent;

/**
 * TemplateEventTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class TemplateEventTest extends \PHPUnit_Framework_TestCase
{
    public function testStubs()
    {
        $dispatcher = new EventDispatcher();
        $obj = new TemplateEvent($dispatcher);

        $obj->setBlockName('foobar1');
        $this->assertEquals('foobar1', $obj->getBlockName());
        $obj->setTemplateName('foobar2');
        $this->assertEquals('foobar2', $obj->getTemplateName());
        $obj->setTemplatePath('foobar3');
        $this->assertEquals('foobar3', $obj->getTemplatePath());

        $called = false;
        $dispatcher->addListener('fooevent', function () use (&$called) {
            $called = true;
        });
        $obj->dispatch('fooevent');
        $this->assertTrue($called);
    }

}
