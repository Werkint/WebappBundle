<?php
namespace Werkint\Bundle\WebappBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Werkint\Bundle\WebappBundle\DependencyInjection\WerkintWebappExtension;

/**
 * WerkintWebappExtensionTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class WerkintWebappExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testRequiredConfig()
    {
        $this->loadContainer([]);
    }

    public function testConfig()
    {
        $container = $this->loadContainer([
            'scripts' => '',
            'respath' => '',
            'resdir'  => '',
            'revpath' => '',
        ]);

        $this->assertTrue($container->hasParameter('werkint_webapp'));
    }

    public function testServices()
    {
        $container = $this->loadContainer([
            'scripts' => '',
            'respath' => '',
            'resdir'  => '',
            'revpath' => '',
        ]);

        $this->assertTrue(
            $container->hasDefinition('werkint.webapp'),
            'Main service is loaded'
        );
    }

    /**
     * @param array $config
     * @return ContainerBuilder
     */
    protected function loadContainer(array $config)
    {
        $container = new ContainerBuilder();
        $loader = new WerkintWebappExtension();
        $loader->load([$config], $container);
        return $container;
    }
}
