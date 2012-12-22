<?php
namespace Werkint\Bundle\WebappBundle\Hacks;


use Symfony\Component\HttpFoundation\Request,
    Doctrine\Common\Util\ClassUtils,
    Sensio\Bundle\FrameworkExtraBundle\Templating\TemplateGuesser as TemplateGuesserRef;

class TemplateGuesser extends TemplateGuesserRef
{

    public function guessTemplateName($controller, Request $request, $engine = 'twig')
    {
        $className = class_exists('Doctrine\Common\Util\ClassUtils') ? ClassUtils::getClass($controller[0]) : get_class($controller[0]);

        if (!preg_match('/Controller\\\(.+)Controller$/', $className, $matchController)) {
            throw new \InvalidArgumentException(sprintf('The "%s" class does not look like a controller class (it must be in a "Controller" sub-namespace and the class name must end with "Controller")', get_class($controller[0])));

        }
        if (!preg_match('/^(.+)Action$/', $controller[1], $matchAction)) {
            throw new \InvalidArgumentException(sprintf('The "%s" method does not look like an action method (it does not end with Action)', $controller[1]));
        }

        $bundle = $this->getBundleForClass($className);

        return new ShortTemplateReference($bundle->getName(), $matchController[1], $matchAction[1], $request->getRequestFormat(), $engine);
    }
}
