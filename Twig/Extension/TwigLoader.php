<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

/**
 * TwigLoader.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class TwigLoader extends FilesystemLoader
{
    /**
     * {@inheritdoc}
     */
    public function findTemplate($template)
    {
        return parent::findTemplate($template);
    }

}
