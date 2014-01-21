<?php
namespace Werkint\Bundle\WebappBundle\Tests\Processor;

use Werkint\Bundle\WebappBundle\Webapp\Processor\ScriptsProcessor;

/**
 * ScriptsProcessorTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ScriptsProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testDebug()
    {
        $obj = new ScriptsProcessor(true);
        $str = 'foo_string';
        $this->assertEquals($str, $obj->process($str));
    }

    public function testProcess()
    {
        $obj = new ScriptsProcessor(false);
        $str = '/** test */a=b;';
        $this->assertEquals('a=b;', $obj->process($str));
    }

}
