<?php
namespace Werkint\Bundle\WebappBundle\Tests\Currency;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Werkint\Bundle\WebappBundle\WerkintWebappBundle;

/**
 * WerkintWebappBundleTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class WerkintWebappBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testPasses()
    {
        $containerBuilderMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $obj = new WerkintWebappBundle();
        $obj->build($containerBuilderMock);
    }
}