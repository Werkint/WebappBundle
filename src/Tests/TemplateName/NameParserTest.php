<?php
namespace Werkint\Bundle\WebappBundle\Tests\TemplateName;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Werkint\Bundle\WebappBundle\TemplateName\NameParser;
use Werkint\Bundle\WebappBundle\TemplateName\ShortTemplateReference;

/**
 * NameParserTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class NameParserTest extends \PHPUnit_Framework_TestCase
{
    protected $parser;

    protected function setUp()
    {
        $kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
        $kernel
            ->expects($this->any())
            ->method('getBundle')
            ->will($this->returnCallback(function ($bundle) {
                if (in_array($bundle, array('SensioFooBundle', 'SensioCmsFooBundle', 'FooBundle'))) {
                    return true;
                }

                throw new \InvalidArgumentException();
            }));
        $this->parser = new NameParser($kernel);
    }

    protected function tearDown()
    {
        $this->parser = null;
    }

    /**
     * @dataProvider getLogicalNameToTemplateProvider
     */
    public function testParse($name, $ref)
    {
        $template = $this->parser->parse($name);

        $this->assertEquals($template->getLogicalName(), $ref->getLogicalName());
        $this->assertEquals($template->getLogicalName(), $name);
    }

    public function testCache()
    {
        $this->parser->parse('::index.twig');
        $this->parser->parse('::index.twig');
        $this->parser->parse(new ShortTemplateReference('', '', 'index', 'html', 'twig'));
    }

    /**
     * @return array
     */
    public function getLogicalNameToTemplateProvider()
    {
        return array(
            // TemplateReference
            array('FooBundle:Post:index.html.php', new TemplateReference('FooBundle', 'Post', 'index', 'html', 'php')),
            array('FooBundle:Post:index.html.twig', new TemplateReference('FooBundle', 'Post', 'index', 'html', 'twig')),
            array('FooBundle:Post:index.xml.php', new TemplateReference('FooBundle', 'Post', 'index', 'xml', 'php')),
            array('SensioFooBundle:Post:index.html.php', new TemplateReference('SensioFooBundle', 'Post', 'index', 'html', 'php')),
            array('SensioCmsFooBundle:Post:index.html.php', new TemplateReference('SensioCmsFooBundle', 'Post', 'index', 'html', 'php')),
            array(':Post:index.html.php', new TemplateReference('', 'Post', 'index', 'html', 'php')),
            array('::index.html.php', new TemplateReference('', '', 'index', 'html', 'php')),
            array('FooBundle:Post:foo.bar.index.html.php', new TemplateReference('FooBundle', 'Post', 'foo.bar.index', 'html', 'php')),
            // TemplateReference
            array('FooBundle:Post:index.twig', new ShortTemplateReference('FooBundle', 'Post', 'index', 'html', 'twig')),
            array('SensioFooBundle:Post:index.twig', new ShortTemplateReference('SensioFooBundle', 'Post', 'index', 'html', 'twig')),
            array(':Post:index.twig', new ShortTemplateReference('', 'Post', 'index', 'html', 'twig')),
            array('::index.twig', new ShortTemplateReference('', '', 'index', 'html', 'twig')),
        );
    }

    /**
     * @dataProvider      getInvalidLogicalNameProvider
     * @expectedException \InvalidArgumentException
     */
    public function testParseInvalidName($name)
    {
        $this->parser->parse($name);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidCharacters()
    {
        $this->parser->parse('foo..bar');
    }

    /**
     * @return array
     */
    public function getInvalidLogicalNameProvider()
    {
        return array(
            array('BarBundle:Post:index.html.php'),
            array('FooBundle:Post:index'),
            array('FooBundle:Post'),
            array('FooBundle:Post'),
            array('FooBundle:Post:foo:bar'),
        );
    }

}
