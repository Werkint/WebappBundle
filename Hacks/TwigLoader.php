<?php
namespace Werkint\Bundle\WebappBundle\Hacks;

use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

class TwigLoader extends FilesystemLoader
{

    public function findTemplate($template)
    {
        return parent::findTemplate($template);
    }

}
