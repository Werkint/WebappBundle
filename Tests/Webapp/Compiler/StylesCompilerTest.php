<?php
namespace Werkint\Bundle\WebappBundle\Tests\Webapp\Compiler;

use Symfony\Component\Filesystem\Filesystem;
use Werkint\Bundle\WebappBundle\Webapp\Compiler\StylesCompiler;
use Werkint\Bundle\WebappBundle\Webapp\Processor\DefaultProcessor;

/**
 * StylesCompilerTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StylesCompilerTest extends \PHPUnit_Framework_TestCase
{
    protected $dir;

    public function setUp()
    {
        $this->dir = sys_get_temp_dir() . '/wtest' . sha1(microtime());
        mkdir($this->dir);
    }

    public function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->dir);
    }

    public function testRawPass()
    {
        $obj = new StylesCompiler(
            new DefaultProcessor()
        );
        $file = $this->dir . '/data';
        $this->assertEquals('', $obj->compile([], $file, []));
        $file = file_get_contents($file);
        $this->assertEquals('', $file);
    }

    /**
     * @depends testRawPass
     */
    public function testVariables()
    {
        $obj = new StylesCompiler(
            new DefaultProcessor()
        );
        $file = $this->dir . '/data';
        $str = $obj->compile([
            'foo_1' => new \stdClass(),
            'foo-2' => 'test',
            'foo3'  => 123,
            'foo4'  => [12345],
        ], $file, []);
        $file = file_get_contents($file);
        $this->assertEquals($str, $file);
        $this->assertFalse(strpos($str, '$' . StylesCompiler::VAR_PREFIX . '-foo-1'));
        $this->assertTrue(strpos($str, '$' . StylesCompiler::VAR_PREFIX . '-foo-2:"test"') !== false);
        $this->assertTrue(strpos($str, '$' . StylesCompiler::VAR_PREFIX . '-foo3:"123"') !== false);
        $this->assertTrue(strpos($str, '$' . StylesCompiler::VAR_PREFIX . '-foo4-0:"12345"') !== false);
    }

    /**
     * @depends testVariables
     */
    public function testPrefixes()
    {
        $obj = new StylesCompiler(
            new DefaultProcessor()
        );
        $file = $this->dir . '/data';
        $str = $obj->compile([], $file, [
            __DIR__ . '/stubfile.txt',
        ], 'a{color:black;}');
        $file = file_get_contents($file);
        $this->assertNotEquals($str, $file);
        $this->assertFalse(strpos($file, 'foo_test'));
        $this->assertTrue(strpos($file, 'a{color:black;}') !== false);
    }

    /**
     * @depends testRawPass
     * @expectedException \InvalidArgumentException
     */
    public function testWrongFiles()
    {
        $obj = new StylesCompiler(
            new DefaultProcessor()
        );
        $file = $this->dir . '/data';
        $obj->compile([], $file, [
            __DIR__ . '/stubfile_missing.txt',
        ]);
    }

    /**
     * @depends testWrongFiles
     */
    public function testFiles()
    {
        $obj = new StylesCompiler(
            new DefaultProcessor()
        );
        $file = $this->dir . '/data';
        $str = $obj->compile([], $file, [
            __DIR__ . '/stubfile.txt',
            __DIR__ . '/stubfile.txt',
        ]);
        $file = file_get_contents($file);
        $str2 = 'foo_test' . "\n" . 'foo_test';
        $this->assertTrue(strpos($file, $str2) !== false, 'File not included');
        $this->assertEquals($str, $file);
        $this->assertEquals($str2, $file);
    }

}
