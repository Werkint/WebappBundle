<?php
namespace Werkint\Bundle\WebappBundle\Tests\Webapp;

use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * ScriptLoaderTest.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ScriptLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testBlocksAndLog()
    {
        $obj = $this->getObject();

        $this->assertEquals(1, count($obj->getBlocks()));
        $this->assertTrue(in_array(ScriptLoader::ROOT_BLOCK, $obj->getBlocks()));
        $obj->blockStart('root');
        $obj->blockEnd();
        $this->assertEquals(2, count($obj->getBlocks()));
        $obj->createBlock('foo_block');
        $this->assertEquals(3, count($obj->getBlocks()));

        $this->assertGreaterThan(0, count($obj->getLog()));
    }

    public function testPackages()
    {
        $obj = $this->getObject();

        $this->assertEquals(0, count($obj->getPackages()));
        $obj->addPackage('test');
        $this->assertEquals(1, count($obj->getPackages()));

        $this->assertFalse($obj->isPackageLoaded('test2'));
        $obj->addPackage('test2');
        $this->assertTrue($obj->isPackageLoaded('test2'));
    }

    /**
     * @depends testBlocksAndLog
     * @depends testPackages
     * @expectedException \InvalidArgumentException
     */
    public function testPackagesWrongBlock()
    {
        $obj = $this->getObject();
        $obj->setIsSplit(true);

        $obj->addPackage('test', 'foo_wrong_block');
    }

    /**
     * @depends testPackagesWrongBlock
     */
    public function testPackagesBlocks()
    {
        $obj = $this->getObject();
        $obj->setIsSplit(true);

        $obj->addPackage('test');
        $obj->blockStart('root');
        $obj->addPackage('test');
        $obj->addPackage('test3');
        $obj->blockEnd();
        $obj->addPackage('test');
        $obj->addPackage('test2');

        $this->assertEquals(2, count($obj->getPackages()));
        $this->assertEquals(2, count($obj->getPackages('root')));

        $this->assertTrue($obj->isPackageLoaded('test', 'root'));
        $this->assertTrue($obj->isPackageLoaded('test3', 'root'));
        $this->assertFalse($obj->isPackageLoaded('test2', 'root'));
        $this->assertTrue($obj->isPackageLoaded('test'));
        $this->assertTrue($obj->isPackageLoaded('test2'));
        $this->assertFalse($obj->isPackageLoaded('test3'));

        $obj->blockStart('root');
        $this->assertTrue($obj->isPackageLoaded('test3'));
        $this->assertFalse($obj->isPackageLoaded('test2'));
        $obj->blockEnd();

        $obj = $this->getObject();
        $obj->setIsSplit(false);

        $obj->addPackage('test');
        $obj->blockStart('root');
        $obj->addPackage('test');
        $obj->blockEnd();
        $obj->addPackage('test');

        $this->assertEquals(1, count($obj->getPackages()));
        $this->assertEquals(1, count($obj->getPackages('root')));
    }

    /**
     * @depends testPackagesBlocks
     */
    public function testVariables()
    {
        $obj = $this->getObject();

        $this->assertEquals(0, count($obj->getVariables()));
        $obj->addVar('test1', 1);
        $obj->blockStart('foo');
        $obj->addVar('test1', 1);
        $obj->addVar('test2', 1);
        $obj->blockEnd();
        $this->assertEquals(1, count($obj->getVariables()));
        $this->assertEquals(2, count($obj->getVariables('foo')));

        $obj->blockStart('foo2');
        $obj->addVar('test', 'foobar');
        $this->assertArrayHasKey('test', $obj->getVariables());
        $obj->blockEnd();
    }


    /**
     * @depends testBlocksAndLog
     * @expectedException \InvalidArgumentException
     */
    public function testAttachWrongFile()
    {
        $obj = $this->getObject();
        $views = __DIR__ . '/views';

        $obj->attachFile($views . '/foo_wrong_filename');
    }

    /**
     * @depends testAttachWrongFile
     */
    public function testAttachFile()
    {
        $obj = $this->getObject();
        $views = __DIR__ . '/views';

        // Ignore exception
        $obj->attachFile($views . '/foo_wrong_filename.js', true);
        $obj->attachFile($views . '/foo.css');
        $this->assertEquals(0, count($obj->getFiles(null, ScriptLoader::TYPE_JS)));
        $this->assertEquals(1, count($obj->getFiles(null, ScriptLoader::TYPE_CSS)));

        $obj = $this->getObject('bar');
        $obj->attachFile($views . '/foo.css');
        $this->assertEquals(2, count($obj->getFiles(null, ScriptLoader::TYPE_CSS)));
        $obj->attachFile($views . '/foo2.scss');
        $this->assertEquals(3, count($obj->getFiles(null, ScriptLoader::TYPE_CSS)));
        $obj->attachFile($views . '/foo2.js');
        $this->assertEquals(1, count($obj->getFiles(null, ScriptLoader::TYPE_JS)));
        $obj->attachFile($views . '/foo_wrong_filename.js', true);
        $this->assertEquals(2, count($obj->getFiles(null, ScriptLoader::TYPE_JS)));
    }

    /**
     * @depends testAttachFile
     * @expectedException \InvalidArgumentException
     */
    public function testWrongViewRelated()
    {
        $obj = $this->getObject();
        $views = __DIR__ . '/views';

        $obj->attachViewRelated($views . '/foo_wrong_filename');
    }

    /**
     * @depends testWrongViewRelated
     */
    public function testViewRelated()
    {
        $obj = $this->getObject();
        $views = __DIR__ . '/views';

        $obj->attachViewRelated($views . '/foo_wrong_filename', true);
        $obj->attachViewRelated($views . '/foo.twig');
        $this->assertEquals(1, count($obj->getFiles(null, ScriptLoader::TYPE_CSS)));
        $this->assertEquals(0, count($obj->getFiles(null, ScriptLoader::TYPE_JS)));

        $obj = $this->getObject('bar');
        $obj->attachViewRelated($views . '/foo.twig');
        $this->assertEquals(2, count($obj->getFiles(null, ScriptLoader::TYPE_CSS)));
        $this->assertEquals(1, count($obj->getFiles(null, ScriptLoader::TYPE_JS)));
        $obj->attachViewRelated($views . '/foo2.twig');
        $this->assertEquals(3, count($obj->getFiles(null, ScriptLoader::TYPE_CSS)));
        $this->assertEquals(2, count($obj->getFiles(null, ScriptLoader::TYPE_JS)));
    }

    /**
     * @param string|null $mode
     * @return ScriptLoader
     */
    protected function getObject($mode = null)
    {
        return new ScriptLoader(false, $mode);
    }

}
