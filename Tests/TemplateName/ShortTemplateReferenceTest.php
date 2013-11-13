<?php
namespace Werkint\Bundle\WebappBundle\Tests\TemplateName;

use Werkint\Bundle\WebappBundle\TemplateName\ShortTemplateReference;

/**
 * ShortTemplateReferenceTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ShortTemplateReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPathWorksWithNamespacedControllers()
    {
        $reference = new ShortTemplateReference('AcmeBlogBundle', 'Admin\Post', 'index', 'html', 'twig');

        $this->assertSame(
            '@AcmeBlogBundle/Resources/views/Admin/Post/index.twig',
            $reference->getPath()
        );
    }

}
