<?php
namespace Werkint\Bundle\WebappBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * WerkintWebappExtension.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class WerkintWebappExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(
        array $configs,
        ContainerBuilder $container
    ) {
        $processor = new Processor();
        $config = $processor->processConfiguration(
            new Configuration($this->getAlias()),
            $configs
        );
        $config['scriptsdir'] = __DIR__ . '/../Resources/scripts';
        $container->setParameter(
            $this->getAlias(),
            $config
        );
        $container->setParameter(
            $this->getAlias() . '.browsers',
            $config['browsers']
        );

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }

}
