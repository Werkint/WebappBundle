<?php
namespace Werkint\Bundle\WebappBundle\Tests\Webapp\Compiler;

use Symfony\Component\Filesystem\Filesystem;
use Werkint\Bundle\WebappBundle\Webapp\Compiler\ScriptsCompiler;
use Werkint\Bundle\WebappBundle\Webapp\Processor\DefaultProcessor;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * ScriptCompilerTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ScriptCompilerTest extends \PHPUnit_Framework_TestCase
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

    public function testStrictMode()
    {
        $obj = new ScriptsCompiler(
            new DefaultProcessor(),
            true
        );
        $file = $this->dir . '/data';
        $obj->compile([], '', $file, []);
        $file = file_get_contents($file);
        $this->assertTrue(strpos($file, ScriptsCompiler::STRICT_MODE) !== false);
    }

    public function testVariables()
    {
        $obj = new ScriptsCompiler(
            new DefaultProcessor()
        );
        $file = $this->dir . '/data';
        $obj->compile([
            'foovar1' => null,
            'foovar2' => true,
            'foovar3' => ['foo' => 'bar'],
        ], '', $file, []);
        $file = file_get_contents($file);

        $this->assertTrue(
            strpos($file, ScriptsCompiler::VAR_PREFIX . '.foovar1=null') !== false,
            'Null variable not found'
        );
        $this->assertTrue(
            strpos($file, ScriptsCompiler::VAR_PREFIX . '.foovar2=true') !== false,
            'Boolean variable not found'
        );
        $this->assertTrue(
            strpos($file, ScriptsCompiler::VAR_PREFIX . '.foovar3={') !== false,
            'Array variable not found'
        );
    }

    /**
     * @depends testVariables
     */
    public function testVariablesPrefix()
    {
        $obj = new ScriptsCompiler(
            new DefaultProcessor()
        );
        $file = $this->dir . '/data';
        $obj->compile([
            'foovar' => true,
        ], ScriptLoader::ROOT_BLOCK, $file, []);
        $file = file_get_contents($file);
        $this->assertTrue(
            strpos($file, ScriptsCompiler::VAR_PREFIX . '.foovar=true') !== false,
            'Unprefixed variable not found'
        );
        $file = $this->dir . '/data';
        $obj->compile([
            'foovar' => true,
        ], 'fooprefix', $file, []);
        $file = file_get_contents($file);
        $this->assertTrue(
            strpos($file, ScriptsCompiler::VAR_PREFIX . '.fooprefix.foovar=true') !== false,
            'Prefixed variable not found'
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongFiles()
    {
        $obj = new ScriptsCompiler(
            new DefaultProcessor()
        );
        $file = $this->dir . '/data';
        $obj->compile([], '', $file, [
            __DIR__ . '/stubfile_missing.txt',
        ]);
    }

    /**
     * @depends testWrongFiles
     */
    public function testFiles()
    {
        $obj = new ScriptsCompiler(
            new DefaultProcessor()
        );
        $file = $this->dir . '/data';
        $obj->compile([], '', $file, [
            __DIR__ . '/stubfile.txt',
        ]);
        $file = file_get_contents($file);

        $this->assertTrue(strpos($file, 'foo_test') !== false, 'File not included');
    }

}
