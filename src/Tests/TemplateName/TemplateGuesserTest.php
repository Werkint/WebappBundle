<?php
namespace Werkint\Bundle\WebappBundle\Tests\TemplateName;

use Sensio\Bundle\FrameworkExtraBundle\Tests\Templating\TemplateGuesserTest as BaseClass;
use Sensio\Bundle\FrameworkExtraBundle\Tests\Templating\Fixture;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Werkint\Bundle\WebappBundle\TemplateName\TemplateGuesser;
use Werkint\Bundle\WebappBundle\Tests\FooObject;

/**
 * TemplateGuesserTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class TemplateGuesserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    private $bundles = array();

    public function setUp()
    {
        $this->bundles['FooBundle'] = $this->getBundle('FooBundle', 'Sensio\Bundle\FrameworkExtraBundle\Tests\Templating\Fixture\FooBundle');
        $this->bundles['BarBundle'] = $this->getBundle('BarBundle', 'Sensio\Bundle\FrameworkExtraBundle\Tests\Templating\Fixture\BarBundle', 'FooBundle');
        $this->bundles['FooBarBundle'] = $this->getBundle('FooBarBundle', 'Sensio\Bundle\FrameworkExtraBundle\Tests\Templating\Fixture\FooBarBundle', 'BarBundle');

        $this->kernel = $this->getMock('Symfony\Component\HttpKernel\KernelInterface');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testException1()
    {
        $templateGuesser = new TemplateGuesser($this->kernel);
        $templateGuesser->guessTemplateName(array(
            new FooObject(),
            'indexAction',
        ), new Request());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testException2()
    {
        $templateGuesser = new TemplateGuesser($this->kernel);
        $templateGuesser->guessTemplateName(array(
            new Fixture\FooBundle\Controller\FooController(),
            'foobar',
        ), new Request());
    }

    /**
     * @depends testException1
     * @depends testException2
     */
    public function testGuessTemplateName()
    {
        $this->kernel
            ->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array_values($this->bundles)));

        $templateGuesser = new TemplateGuesser($this->kernel);
        $templateReference = $templateGuesser->guessTemplateName(array(
            new Fixture\FooBundle\Controller\FooController(),
            'indexAction',
        ), new Request());

        $this->assertEquals('FooBundle:Foo:index.twig', (string)$templateReference);;
    }

    /**
     * @param string $name
     * @param string $namespace
     * @param null   $parent
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getBundle($name, $namespace, $parent = null)
    {
        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $bundle
            ->expects($this->any())
            ->method('getNamespace')
            ->will($this->returnValue($namespace));

        $bundle
            ->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue($parent));

        return $bundle;
    }

}
