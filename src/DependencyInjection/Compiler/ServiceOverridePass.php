<?php
namespace Werkint\Bundle\WebappBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * ServiceOverridePass.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ServiceOverridePass implements
    CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(
        ContainerBuilder $container
    ) {
        // TODO: убер-костыль
        $srvs = [
            'sensio_framework_extra.view.guesser' => 'Werkint\Bundle\WebappBundle\TemplateName\TemplateGuesser',
            'templating.name_parser'              => 'Werkint\Bundle\WebappBundle\TemplateName\NameParser',
            'twig.loader.filesystem'              => 'Werkint\Bundle\WebappBundle\Twig\Extension\TwigLoader',
        ];
        foreach ($srvs as $srv => $class) {
            $parent = $container->getDefinition($srv);
            $container->setDefinition(
                $srv . '.old',
                $parent
            );
            $def = new DefinitionDecorator($srv . '.old');
            $def->setClass($class);
            $container->setDefinition(
                $srv,
                $def
            );
        }
    }
} 