<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

class TwigLoader extends FilesystemLoader
{

    public function findTemplate($template)
    {
        return parent::findTemplate($template);
    }

}