<?php
namespace Werkint\Bundle\WebappBundle\Tests\Annotation;

use Werkint\Bundle\WebappBundle\Annotation\Route;

/**
 * RouteTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class RouteTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        new Route([]);
    }
}