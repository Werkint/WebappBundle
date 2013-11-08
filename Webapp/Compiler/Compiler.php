<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Compiler;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * Compiler.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Compiler implements
    CacheClearerInterface
{

    protected $targetdir;
    protected $isDebug;
    protected $strictMode = false;
    protected $revision;

    public function __construct($params, $isDebug)
    {
        $this->targetdir = $params['resdir'];
        if (!file_exists($this->targetdir)) {
            throw new \Exception('Directory not found: ' . $this->targetdir);
        }
        $this->isDebug = $isDebug;
        $this->revision = substr(crc32(file_exists($params['revpath']) ?
            file_get_contents($params['revpath']) : ''), 0, 6);
    }

    protected function getHash($data)
    {
        return substr(sha1(serialize($data)), 0, 10);
    }

    public function compile(
        ScriptLoader $loader
    ) {
        $blocks = [];
        $root = null;

        // compilers
        $compilerScript = new ScriptCompiler($this->isDebug, $this->strictMode);
        $compilerStyle = new StyleCompiler($this->isDebug);

        foreach ($loader->getBlocks() as $block) {
            $blockRev = '_r' . $this->revision . '_' . $block;
            $vars = $loader->getVariables($block);
            $varHash = substr($this->getHash($vars), 0, 5);
            $blocks[$block]['imports'] = $loader->getImports($block);

            // Files to compile
            $filesCss = $loader->getFiles($block, 'scss');
            // var_dump($filesCss);die();
            $filesJs = $loader->getFiles($block, 'js');

            // CSS
            $name = $this->getHash($filesCss) . '.' . $varHash . $blockRev;
            $blocks[$block]['css'] = $name;
            $blockPath = $this->targetdir . '/' . $name;
            // Compile, if needed
            if (!$this->isFresh($blockPath . '.css', $filesCss)) {
                $data = $compilerStyle->compile($vars, $block, $blockPath . '.css', $filesCss, $root);
                file_put_contents($blockPath . '.scss', $data);
            }

            if ($block == '_root') {
                $root = file_get_contents($blockPath . '.scss');
            }

            // JS
            $name = $this->getHash($filesJs) . '.' . $varHash . $blockRev;
            $blocks[$block]['js'] = $name;
            $blockPath = $this->targetdir . '/' . $name;
            // Compile, if needed
            if (!$this->isFresh($blockPath . '.js', $filesJs)) {
                $compilerScript->compile($vars, $block, $blockPath . '.js', $filesJs);
            }
        }

        return $blocks;
    }

    protected function isFresh($filepath, &$files)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        $mtime = filemtime($filepath);
        foreach ($files as $file) {
            if (filemtime($file) > $mtime) {
                return false;
            }
        }
        return true;
    }

    // -- CacheClearerInterface ---------------------------------------

    public function clear($cacheDir)
    {
        $fs = new Filesystem();
        $fs->remove($this->targetdir);
        $fs->mkdir($this->targetdir);
    }

}