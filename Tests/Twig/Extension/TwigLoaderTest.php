<?php
namespace Werkint\Bundle\WebappBundle\Tests\Twig\Extension;

use Symfony\Bundle\FrameworkBundle\Templating\Loader\TemplateLocator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Templating\TemplateNameParser;
use Werkint\Bundle\WebappBundle\Twig\Extension\TwigLoader;

/**
 * TwigLoaderTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class TwigLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Twig_Error_Loader
     */
    public function testClass()
    {
        $obj = new TwigLoader(
            new TemplateLocator(new FileLocator()),
            new TemplateNameParser()
        );
        $obj->findTemplate('foo_wrong_name');
    }

}
