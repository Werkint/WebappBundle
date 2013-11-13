<?php
namespace Werkint\Bundle\WebappBundle\Tests\Annotation;

use Werkint\Bundle\WebappBundle\Annotation\Post;

/**
 * PostTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class PostTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        new Post([]);
    }
}