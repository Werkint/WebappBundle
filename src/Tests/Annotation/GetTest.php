<?php
namespace Werkint\Bundle\WebappBundle\Tests\Annotation;

use Werkint\Bundle\WebappBundle\Annotation\Get;

/**
 * GetTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class GetTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        new Get([]);
    }
}