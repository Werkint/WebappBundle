<?php
namespace Werkint\Bundle\WebappBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\Config\Definition\Processor,
    Symfony\Component\Config\FileLocator;

class WerkintWebappExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(
            new Configuration($this->getAlias()), $configs
        );
        $container->setParameter(
            $this->getAlias(), $config
        );
        $loader = new YamlFileLoader(
            $container, new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }

    public function getAlias()
    {
        return 'werkint_webapp';
    }
}
