<?php
namespace Werkint\Bundle\WebappBundle\Tests\Webapp;

use Werkint\Bundle\WebappBundle\Webapp\Webapp;


/**
 * WebappTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class WebappTest extends \PHPUnit_Framework_TestCase
{
    public function testLoader()
    {
        $loader = $this->getMock('Werkint\Bundle\WebappBundle\Webapp\ScriptLoader');
        $loader
            ->expects($this->once())
            ->method('setIsSplit')
            ->will($this->returnValue(null));
        $obj = new Webapp($loader);

        $this->assertEquals($loader, $obj->getLoader());
        $obj->setIsSplit(true);
    }

    public function testVars()
    {
        $loader = $this->getMock('Werkint\Bundle\WebappBundle\Webapp\ScriptLoader');
        $loader
            ->expects($this->exactly(3))
            ->method('addVar')
            ->will($this->returnValue(null));
        $obj = new Webapp($loader);

        $obj->addVar('foo', 1);
        $obj->addVars([
            'foo1' => 1,
            'foo2' => 1,
        ]);
    }

    public function testImports()
    {
        $loader = $this->getMock('Werkint\Bundle\WebappBundle\Webapp\ScriptLoader');
        $loader
            ->expects($this->exactly(2))
            ->method('addImport')
            ->will($this->returnValue(null));
        $obj = new Webapp($loader);

        $obj->addImportCss('foo');
        $obj->addImportJs('foo');
    }

    public function testAttach()
    {
        $loader = $this->getMock('Werkint\Bundle\WebappBundle\Webapp\ScriptLoader');
        $loader
            ->expects($this->once())
            ->method('attachFile')
            ->with($this->equalTo('foobar'))
            ->will($this->returnValue(null));
        $obj = new Webapp($loader);

        $obj->attachFile('foobar');
    }

}
