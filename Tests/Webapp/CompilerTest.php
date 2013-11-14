<?php
namespace Werkint\Bundle\WebappBundle\Tests\Webapp;

use Symfony\Component\Filesystem\Filesystem;
use Werkint\Bundle\WebappBundle\Webapp\Compiler;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * CompilerTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class CompilerTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWrongDirectory()
    {
        new Compiler([
            'resdir'  => __DIR__ . '/foo_wrong_path',
            'revpath' => 0,
        ]);
    }

    /**
     * @depends testWrongDirectory
     */
    public function testNewDirectory()
    {
        touch($this->dir . '/foofile');
        $this->assertTrue(file_exists($this->dir . '/foofile'));
        $obj = new Compiler([
            'resdir'  => $this->dir,
            'revpath' => 0,
        ]);
        $obj->clear($this->dir);
        $this->assertFalse(file_exists($this->dir . '/foofile'));
    }

    public function testCompileRaw()
    {
        $obj = new Compiler([
            'resdir'  => $this->dir,
            'revpath' => 0,
        ]);
        $loader = new ScriptLoader();
        $ret = $obj->compile($loader);
        $this->assertArrayHasKey(ScriptLoader::ROOT_BLOCK, $ret);
        $this->assertFileExists($this->dir . '/' . $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_JS] . '.js');
        $this->assertFileExists($this->dir . '/' . $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_CSS] . '.css');
        $this->assertFileExists($this->dir . '/' . $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_CSS] . '.scss');
    }

    /**
     * @depends testCompileRaw
     */
    public function testRevision()
    {
        file_put_contents($this->dir . '/foofile', '1');
        $obj = new Compiler([
            'resdir'  => $this->dir,
            'revpath' => $this->dir . '/foofile',
        ]);
        $loader = new ScriptLoader();
        $ret = $obj->compile($loader);
        $s11 = $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_JS];
        $s12 = $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_CSS];
        file_put_contents($this->dir . '/foofile', '2');
        $obj = new Compiler([
            'resdir'  => $this->dir,
            'revpath' => $this->dir . '/foofile',
        ]);
        $ret = $obj->compile($loader);
        $s21 = $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_JS];
        $s22 = $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_CSS];
        $this->assertNotEquals($s11, $s21);
        $this->assertNotEquals($s12, $s22);
    }

    /**
     * @depends testCompileRaw
     */
    public function testCompileBlocks()
    {
        $obj = new Compiler([
            'resdir'  => $this->dir,
            'revpath' => 0,
        ]);
        $loader = new ScriptLoader();
        $loader->createBlock('fooblock');
        $ret = $obj->compile($loader);
        $this->assertArrayHasKey(ScriptLoader::ROOT_BLOCK, $ret);
        $this->assertArrayHasKey('fooblock', $ret);
        $this->assertFileExists($this->dir . '/' . $ret['fooblock'][ScriptLoader::TYPE_JS] . '.js');
        $this->assertFileExists($this->dir . '/' . $ret['fooblock'][ScriptLoader::TYPE_CSS] . '.css');
        $this->assertFileExists($this->dir . '/' . $ret['fooblock'][ScriptLoader::TYPE_CSS] . '.scss');
    }

    /**
     * @depends      testCompileRaw
     * @dataProvider getScriptTypes
     */
    public function testFreshCheck($type)
    {
        $obj = new Compiler([
            'resdir'  => $this->dir,
            'revpath' => 0,
        ]);
        $loader = new ScriptLoader();
        $loader->createBlock('fooblock');
        $ret = $obj->compile($loader);
        $s1 = $ret[ScriptLoader::ROOT_BLOCK][$type];
        $t1 = filemtime($this->dir . '/' . $s1 . '.' . $type);

        $loader->blockStart('fooblock');
        $loader->addVar('foo', 'bar');
        $loader->blockEnd();
        $ret = $obj->compile($loader);
        $s2 = $ret[ScriptLoader::ROOT_BLOCK][$type];
        $t2 = filemtime($this->dir . '/' . $s2 . '.' . $type);
        $this->assertEquals($s1, $s2);
        $this->assertEquals($t1, $t2);

        touch($this->dir . '/foo.' . $type);
        $loader->attachFile($this->dir . '/foo.' . $type);
        $ret = $obj->compile($loader);
        $s1 = $ret[ScriptLoader::ROOT_BLOCK][$type];
        touch($this->dir . '/' . $s1 . '.' . $type, floor(microtime(true)) - 5);
        $t1 = filemtime($this->dir . '/' . $s1 . '.' . $type);
        touch($this->dir . '/' . $s1 . '.' . $type, floor(microtime(true)) - 5);
        $ret = $obj->compile($loader);
        $s2 = $ret[ScriptLoader::ROOT_BLOCK][$type];
        $t2 = filemtime($this->dir . '/' . $s2 . '.' . $type);

        $this->assertEquals($s1, $s2);
        $this->assertNotEquals($t1, $t2);
    }

    /**
     * @return array
     */
    public function getScriptTypes()
    {
        return [
            [ScriptLoader::TYPE_JS],
            [ScriptLoader::TYPE_CSS],
        ];
    }

    /**
     * @depends testFreshCheck
     */
    public function testVariablesHash()
    {
        $obj = new Compiler([
            'resdir'  => $this->dir,
            'revpath' => 0,
        ]);
        $loader = new ScriptLoader();

        $ret = $obj->compile($loader);
        $s1 = $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_JS];
        $loader->addVar('foo', 'bar');
        $ret = $obj->compile($loader);
        $s2 = $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_JS];
        $s11 = $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_CSS];
        $this->assertNotEquals($s1, $s2);

        $loader->addVar('foo2', new \stdClass());
        $ret = $obj->compile($loader);
        $s2 = $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_JS];
        $s12 = $ret[ScriptLoader::ROOT_BLOCK][ScriptLoader::TYPE_CSS];
        $this->assertNotEquals($s1, $s2);
        //$this->assertEquals($s11, $s12, 'Variable hash is same for JS and CSS');
    }

}
