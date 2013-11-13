<?php
namespace Werkint\Bundle\WebappBundle\Tests\Twig\Extension;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Werkint\Bundle\WebappBundle\Twig\Extension\Extension;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * ExtensionTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testStubs()
    {
        $loader = new ScriptLoader();
        $obj = new Extension(
            $loader,
            new EventDispatcher()
        );

        $cls = 'Werkint\Bundle\WebappBundle\Twig\Extension\TemplateEvent';
        $this->assertInstanceOf($cls, $obj->getTemplateEvent());
        $this->assertEquals(Extension::EXT_NAME, $obj->getName());
        $this->assertEquals(0, count($obj->getGlobals()[Extension::VAR_PREFIX]));
        $loader->addVar('foo','bar');
        $this->assertEquals(1, count($obj->getGlobals()[Extension::VAR_PREFIX]));
    }

}
