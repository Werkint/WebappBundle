<?php
namespace Werkint\Bundle\WebappBundle\Tests\Annotation;

use Werkint\Bundle\WebappBundle\Annotation\View;

/**
 * ViewTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        new View([]);
    }
}