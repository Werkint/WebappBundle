<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Werkint\Bundle\WebappBundle\Webapp\Compiler\ScriptsCompiler;
use Werkint\Bundle\WebappBundle\Webapp\Compiler\StylesCompiler;
use Werkint\Bundle\WebappBundle\Webapp\Processor\ScriptsProcessor;
use Werkint\Bundle\WebappBundle\Webapp\Processor\StylesProcessor;
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

    /**
     * @param string $params
     * @param bool   $isDebug
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $params,
        $isDebug = false
    ) {
        $this->targetdir = $params['resdir'];
        if (!file_exists($this->targetdir)) {
            throw new \InvalidArgumentException('Directory not found: ' . $this->targetdir);
        }
        $this->isDebug = $isDebug;
        $this->revision = substr(crc32(file_exists($params['revpath']) ?
            file_get_contents($params['revpath']) : ''), 0, 6);
    }

    /**
     * @param string $data
     * @return string
     */
    protected function getHash($data)
    {
        return substr(sha1(serialize($data)), 0, 10);
    }

    /**
     * @param ScriptLoader $loader
     * @return array
     */
    public function compile(
        ScriptLoader $loader
    ) {
        $blocks = [];
        $root = null;

        // compilers
        $compilerScript = new ScriptsCompiler(
            new ScriptsProcessor($this->isDebug),
            $this->strictMode
        );
        $compilerStyle = new StylesCompiler(
            new StylesProcessor($this->isDebug)
        );

        // TODO: caching
        foreach ($loader->getBlocks() as $block) {
            $blockRev = '_r' . $this->revision . '_' . $block;
            $vars = $loader->getVariables($block);
            // TODO: variable hash for css/js different
            $varHash = substr($this->getHash($vars), 0, 5);
            $blocks[$block]['imports'] = $loader->getImports($block);

            // Files to compile
            $filesCss = $loader->getFiles($block, ScriptLoader::TYPE_CSS);
            $filesJs = $loader->getFiles($block, ScriptLoader::TYPE_JS);

            // CSS
            $name = $this->getHash($filesCss) . '.' . $varHash . $blockRev;
            $blocks[$block]['css'] = $name;
            $blockPath = $this->targetdir . '/' . $name;
            // Compile, if needed
            if (!$this->isFresh($blockPath . '.css', $filesCss)) {
                $data = $compilerStyle->compile($vars, $blockPath . '.css', $filesCss, $root);
                file_put_contents($blockPath . '.scss', $data);
            }

            if ($block == ScriptLoader::ROOT_BLOCK) {
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

    /**
     * @param string $filepath
     * @param array  $files
     * @return bool
     */
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

    /**
     * @param string $cacheDir
     */
    public function clear($cacheDir)
    {
        $fs = new Filesystem();
        $fs->remove($this->targetdir);
        $fs->mkdir($this->targetdir);
    }

}
