<?php
namespace Werkint\Bundle\WebappBundle\Tests\Processor;

use Werkint\Bundle\WebappBundle\Webapp\Processor\DefaultProcessor;

/**
 * DefaultProcessorTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class DefaultProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testDebug()
    {
        $obj = new DefaultProcessor(true);
        $this->assertTrue($obj->getIsDebug());
        $obj = new DefaultProcessor(false);
        $this->assertFalse($obj->getIsDebug());
    }

    public function testProcess()
    {
        $obj = new DefaultProcessor();
        $str = 'foo_string';
        $this->assertEquals($str, $obj->process($str));
    }

}
