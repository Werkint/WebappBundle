<?php
namespace Werkint\Bundle\WebappBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Werkint\Bundle\WebappBundle\DependencyInjection\Compiler\ServiceOverridePass;

/**
 * WerkintWebappBundle.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class WerkintWebappBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ServiceOverridePass());
    }
}
