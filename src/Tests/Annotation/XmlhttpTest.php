<?php
namespace Werkint\Bundle\WebappBundle\Tests\Annotation;

use Werkint\Bundle\WebappBundle\Annotation\Xmlhttp;

/**
 * XmlhttpTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class XmlhttpTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        new Xmlhttp([]);
    }
}