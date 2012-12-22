<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use \Twig_NodeVisitorInterface,
    \Twig_Node_Module,
    \Twig_Environment,
    \Twig_NodeInterface;

class NodeVisitor implements Twig_NodeVisitorInterface
{

    function enterNode(Twig_NodeInterface $node, Twig_Environment $env)
    {
        if ($node instanceof Twig_Node_Module) {
            /** @var Twig_Node_Module $node */
            $node = NodeModule::nest($node);
        }

        return $node;
    }

    function leaveNode(Twig_NodeInterface $node, Twig_Environment $env)
    {
        return $node;
    }

    function getPriority()
    {
        return 10;
    }

}
