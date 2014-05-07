<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Werkint\Bundle\WebappBundle\Webapp\Compiler\ScriptsCompiler;
use Werkint\Bundle\WebappBundle\Webapp\Compiler\StylesCompiler;

/**
 * Compiler.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class Compiler implements
    CacheClearerInterface
{
    protected $targetdir;
    protected $scriptsdir;
    protected $project;
    protected $isDebug;
    protected $strictMode = false;
    protected $revision;

    /**
     * @param array                    $params
     * @param Compiler\ScriptsCompiler $scriptsCompiler
     * @param Compiler\StylesCompiler  $stylesCompiler
     * @internal param bool $isDebug
     */
    public function __construct(
        array $params,
        ScriptsCompiler $scriptsCompiler,
        StylesCompiler $stylesCompiler
    ) {
        $this->targetdir = $params['resdir'];
        $this->project = $params['project'];
        $this->scriptsdir = $params['scriptsdir'];
        if (!file_exists($this->targetdir)) {
            if (!mkdir($this->targetdir)) {
                throw new \InvalidArgumentException(
                    'Directory not found: ' . $this->targetdir
                );
            }
        }

        $this->scriptsCompiler = $scriptsCompiler;
        $this->stylesCompiler = $stylesCompiler;

        $this->revision = substr(crc32(file_exists($params['revpath']) ?
            file_get_contents($params['revpath']) : ''), 0, 6);
    }

    /**
     * @param array $data
     * @param bool  $isFiles
     * @return string
     */
    protected function getHash(array $data, $isFiles = false)
    {
        if ($isFiles) {
            $hash = [];
            foreach ($data as $file) {
                $hash[] = $file . filemtime($file);
            }
            return $this->getHash($hash);
        } else {
            return substr(sha1(serialize($data)), 0, 10);
        }
    }

    /**
     * @param ScriptLoaderInterface $loader
     * @return array
     */
    public function compile(
        ScriptLoaderInterface $loader
    ) {
        $blocks = [];
        $root = null;

        // TODO: caching
        // TODO: tags injectioned compilers
        $variables = [];
        foreach ($loader->getBlocks() as $block) {
            $blockRev = '_r' . $this->revision . '_' . $block;
            $vars = $loader->getVariables($block);
            $variables += $vars;
            // TODO: variable hash for css/js different
            $varHash = substr($this->getHash($vars), 0, 5);

            // Files to compile
            $filesCss = $loader->getFiles($block, ScriptLoader::TYPE_CSS);
            $filesJs = $loader->getFiles($block, ScriptLoader::TYPE_JS);

            // CSS
            $name = $this->getHash($filesCss, true);
            $name = $this->getHash([$name, $root]) . '.' . $varHash . $blockRev;
            $blocks[$block]['css'] = $name;
            $blockPath = $this->targetdir . '/' . $name;
            // Compile, if needed
            if (!file_exists($blockPath . '.css')) {
                $data = $this->stylesCompiler->compile($variables, $blockPath . '.css', $filesCss, $root);
                file_put_contents($blockPath . '.scss', $data);
            }

            if ($block == ScriptLoader::ROOT_BLOCK) {
                $root = file_get_contents($blockPath . '.scss');
            }

            // JS
            $name = $this->getHash($filesJs, true) . '.' . $varHash . $blockRev;
            $blocks[$block]['js'] = $name;
            $blockPath = $this->targetdir . '/' . $name;
            // Compile, if needed
            if (!file_exists($blockPath . '.js')) {
                $this->scriptsCompiler->compile($vars, $block, $blockPath . '.js', $filesJs);
            }
        }

        return $blocks;
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
