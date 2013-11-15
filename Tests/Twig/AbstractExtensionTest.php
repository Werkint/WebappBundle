<?php
namespace Werkint\Bundle\WebappBundle\Tests\Twig;

use Werkint\Bundle\WebappBundle\Twig\AbstractExtension;

/**
 * AbstractExtensionTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class AbstractExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractExtension */
    protected $ext;

    public function setUp()
    {
        $this->ext = $this->getMockForAbstractClass('Werkint\Bundle\WebappBundle\Twig\AbstractExtension');
    }

    public function testName()
    {
        $this->assertEquals('undefined', $this->ext->getName());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongFilter()
    {
        $this->ext->getFilter('foowrongfilter');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongFunction()
    {
        $this->ext->getFilter('foowrongfilter');
    }

    /**
     * @depends testWrongFilter
     */
    public function testFilters()
    {
        $fn = function () {
        };

        $this->assertEquals(0, count($this->ext->getFilters()));
        $this->ext->addFilter('test1', true, $fn);
        $this->ext->addFilter('test2', false, $fn);
        $this->assertEquals(2, count($this->ext->getFilters()));

        $this->assertEquals($fn, $this->ext->getFilter('test1'));
        $this->assertEquals($fn, $this->ext->getFilter('test2'));

        $this->assertEquals('all', $this->ext->getFilters()['test1']->getSafe(new \Twig_Node())[0]);
        $this->assertNull($this->ext->getFilters()['test2']->getSafe(new \Twig_Node()));
    }

    /**
     * @depends testWrongFunction
     */
    public function testFunctions()
    {
        $fn = function () {
        };

        $this->assertEquals(0, count($this->ext->getFunctions()));
        $this->ext->addFunction('test1', true, $fn);
        $this->ext->addFunction('test2', false, $fn);
        $this->assertEquals(2, count($this->ext->getFunctions()));

        $this->assertEquals($fn, $this->ext->getFunction('test1'));
        $this->assertEquals($fn, $this->ext->getFunction('test2'));

        $this->assertEquals('all', $this->ext->getFunctions()['test1']->getSafe(new \Twig_Node())[0]);
        $this->assertEquals([], $this->ext->getFunctions()['test2']->getSafe(new \Twig_Node()));
    }

    public function testGlobals()
    {
        $this->assertEquals(0, count($this->ext->getGlobals()));
    }

}
