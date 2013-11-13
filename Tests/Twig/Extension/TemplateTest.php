<?php
namespace Werkint\Bundle\WebappBundle\Tests\Twig\Extension;

use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Templating\TemplateNameParser;
use Werkint\Bundle\WebappBundle\Twig\Extension\Extension;
use Werkint\Bundle\WebappBundle\Twig\Extension\Template;
use Werkint\Bundle\WebappBundle\Twig\Extension\TwigLoader;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * TemplateTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testDisplay()
    {
        $dispatcher = new EventDispatcher();

        $obj = $this->getObject($dispatcher);
        $this->assertFalse($obj->display([]));

        $obj = $this->getObject($dispatcher, true);
        $called = false;
        $dispatcher->addListener(Template::EVENT_DISPLAY, function () use (&$called) {
            $called = true;
        });
        $this->assertNull($obj->display([]));
        $this->assertTrue($called, 'Display event not called');
    }

    public function testBlocks()
    {
        $dispatcher = new EventDispatcher();
        $obj = $this->getObject($dispatcher, true);

        $this->assertFalse($obj->displayBlock('fooblock', []));

        $obj = $this->getObject($dispatcher, true);
        $called1 = $called2 = false;
        $dispatcher->addListener(Template::EVENT_BLOCK_PRE, function () use (&$called1) {
            $called1 = true;
        });
        $dispatcher->addListener(Template::EVENT_BLOCK_POST, function () use (&$called2) {
            $called2 = true;
        });
        $this->assertNull($obj->displayBlock(Template::BLOCK_PREFIX . 'foo', []));
        $this->assertTrue($called1, 'BlockPre event not called');
        $this->assertTrue($called2, 'BlockPost event not called');
    }

    /**
     * @param EventDispatcher $dispatcher
     * @param bool            $real
     * @return Template
     */
    protected function getObject(
        EventDispatcher $dispatcher,
        $real = false
    ) {
        if ($real) {
            $env = new \Twig_Environment(
                new TwigLoader(
                    new TemplateLocator(new StubLocator('footemplate')),
                    new TemplateNameParser()
                )
            );
        } else {
            $env = new \Twig_Environment(
                new \Twig_Loader_Array([])
            );
        }
        $ext = new Extension(new ScriptLoader(), $dispatcher);
        $env->addExtension($ext);
        $obj = $this->getMockForAbstractClass(
            'Werkint\Bundle\WebappBundle\Twig\Extension\Template', [$env]
        );
        if ($real) {
            $obj
                ->expects($this->any())
                ->method('getTemplateName')
                ->will($this->returnValue('footemplate'));
        }

        return $obj;
    }

}
