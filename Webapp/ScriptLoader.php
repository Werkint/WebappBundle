<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

class ScriptLoader
{

    protected $resdir;
    protected $respath;
    protected $appmode;
    protected $isDebug;

    public function __construct(
        $params, $isDebug, $appmode
    ) {
        $this->resdir = $params['resdir'];
        $this->respath = $params['respath'];
        $this->appmode = $appmode;
        $this->isDebug = $isDebug;

        $this->blockStart('_root');
        $this->addVar('webapp-res', $this->respath);
    }

    protected $isSplit;

    public function setIsSplit($isSplit)
    {
        $this->isSplit = (bool)$isSplit;
    }

    /**
     * Attaches one script
     * @param string $pathin
     * @param bool   $ignore_check
     * @throws \Exception
     * @return bool
     */
    public function attachFile($pathin, $ignore_check = false)
    {
        $path = realpath($pathin);
        if (!$path) {
            if (!$ignore_check) {
                throw new \Exception('Script not found: ' . $pathin);
            } else {
                return false;
            }
        }
        $this->log('file in [' . $this->blocksStack[0] . ']', $path);
        $this->getCurrentBlock()['files'][] = $path;

        // Other language (appmode)
        if ($this->appmode) {
            $path = realpath(preg_replace(
                '!^(.*)(\.[a-z0-9]+)$!', '$1.' . $this->appmode . '$2', $path
            ));
            if ($path) {
                $this->log('file in [' . $this->blocksStack[0] . ']', $path);
                $this->getCurrentBlock()['files'][] = $path;
            }
        }
        return true;
    }

    /**
     * Attaches related to template files
     * @param string $path
     */
    public function attachViewRelated($path)
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $name = $dir . '/_all';
        $this->attachFile($name . '.scss', true);
        $this->attachFile($name . '.js', true);
        $name = $dir . '/' . pathinfo($path, PATHINFO_FILENAME);
        $this->attachFile($name . '.scss', true);
        $this->attachFile($name . '.js', true);
    }

    public function addVar($name, $value)
    {
        $this->getCurrentBlock()['vars'][$name] = $value;
    }

    public function addImport($url, $type)
    {
        if (!in_array($type, ['js', 'css'])) {
            throw new \Exception('Wrong import type: ' . $type);
        }
        $this->getCurrentBlock()['imports'][] = [$url, $type, sha1($type . $url)];
    }

    // -- Getters ---------------------------------------

    public function getVariables($block)
    {
        return $this->blocks[$block]['vars'];
    }

    public function getFiles($block, $ext)
    {
        $list = $this->blocks[$block]['files'];

        $ret = [];
        foreach ($list as $file) {
            if (in_array($file, $ret)) {
                continue;
            }
            if (pathinfo($file, PATHINFO_EXTENSION) == $ext) {
                $ret[] = $file;
            }
        }

        return $ret;
    }

    public function getImports($block)
    {
        return $this->blocks[$block]['imports'];
    }

    // -- Packages ---------------------------------------

    protected $packages = [];

    protected function &getPackageList($block = null)
    {
        if ($this->isSplit) {
            $list = & $this->getCurrentBlock($block)['packages'];
        } else {
            $list = & $this->packages;
        }
        return $list;
    }

    public function addPackage($name, $block = null)
    {
        $this->getPackageList($block)[] = $name;
        return $this;
    }

    public function isPackageLoaded($name)
    {
        return in_array($name, $this->getPackageList());
    }

    public function getPackages($block = null)
    {
        if ($this->isSplit) {
            $ret = $this->blocks[$block]['packages'];
        } else {
            $ret = $this->packages;
        }
        return $ret;
    }

    // -- Log ---------------------------------------

    protected $log = [];

    protected function log($tag, $msg)
    {
        $this->log[] = $tag . ': ' . $msg;
    }

    public function getLog()
    {
        return $this->log;
    }

    // -- Blocks ---------------------------------------

    protected $blocks = [];
    protected $blocksStack = [];

    public function blockStart($name)
    {
        $this->log('block start', $name);
        $this->getCurrentBlock($name);
        array_unshift($this->blocksStack, $name);
        $this->log('block', $this->blocksStack[0]);
        return $this;
    }

    public function blockEnd()
    {
        $name = array_shift($this->blocksStack);
        $this->log('block end', $name);
        //
        if (isset($this->blocksStack[0])) {
            $this->log('block', $this->blocksStack[0]);
        }
        return $this;
    }

    public function getBlocks()
    {
        while (count($this->blocksStack)) {
            $this->blockEnd();
        }
        return array_keys($this->blocks);
    }

    protected function &getCurrentBlock($block = null)
    {
        if (!$block) {
            $block = $this->blocksStack[0];
        }
        if (!isset($this->blocks[$block])) {
            $this->blocks[$block] = [
                'files'    => [],
                'vars'     => [],
                'imports'  => [],
                'packages' => [],
            ];
        }
        return $this->blocks[$block];
    }

}
