<?php
namespace Werkint\Bundle\WebappBundle\Tests\Processor;

use Werkint\Bundle\WebappBundle\Webapp\Processor\StylesProcessor;

/**
 * StylesProcessorTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StylesProcessorTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $obj = new StylesProcessor(false);
        $str = '$a=1em;a{font-size:$a}';
        $this->assertEquals('a{font-size:1em;}', $obj->process($str));
    }

}
