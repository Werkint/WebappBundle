<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

/**
 * ScriptLoader.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ScriptLoader
{
    const TYPE_JS = 'js';
    const TYPE_CSS = 'css';
    const ROOT_BLOCK = '_root';

    protected $appmode;
    protected $isDebug;

    /**
     * @param bool        $isDebug
     * @param string|null $appmode
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $isDebug = false,
        $appmode = null
    ) {
        $this->appmode = $appmode;
        $this->isDebug = $isDebug;

        $this->blockStart(static::ROOT_BLOCK);
    }

    protected $isSplit;

    /**
     * @param bool $isSplit
     */
    public function setIsSplit($isSplit)
    {
        $this->isSplit = (bool)$isSplit;
    }

    /**
     * Attaches one script
     *
     * @param string $path
     * @param bool   $ignore_check
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function attachFile($path, $ignore_check = false)
    {
        if (!file_exists($path)) {
            if (!$ignore_check) {
                throw new \InvalidArgumentException('Script not found: ' . $path);
            }
        } else {
            $path = realpath($path);
            $this->log('file in [' . $this->blocksStack[0] . ']', $path);
            $this->getCurrentBlock()['files'][] = $path;
        }

        // Other appmode
        if ($this->appmode) {
            // TODO: preg_match
            if (strpos($path, '.' . $this->appmode . '.') === false) {
                $path = realpath(preg_replace(
                    '!^(.*)(\.[a-z0-9]+)$!', '$1.' . $this->appmode . '$2', $path
                ));
                if ($path) {
                    $this->log('file in [' . $this->blocksStack[0] . ']', $path);
                    $this->getCurrentBlock()['files'][] = $path;
                }
            }
        }
        return true;
    }

    /**
     * Attaches related to template files
     *
     * @param string $path
     * @param bool   $ignore_check
     * @throws \InvalidArgumentException
     */
    public function attachViewRelated($path, $ignore_check = false)
    {
        if (!file_exists($path) && !$ignore_check) {
            throw new \InvalidArgumentException('Wrong filename');
        }
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        // TODO: caching
        $name = $dir . '/_all';
        $this->attachFile($name . '.css', true);
        $this->attachFile($name . '.scss', true);
        $this->attachFile($name . '.js', true);
        $name = $dir . '/' . pathinfo($path, PATHINFO_FILENAME);
        $this->attachFile($name . '.css', true);
        $this->attachFile($name . '.scss', true);
        $this->attachFile($name . '.js', true);
    }

    /**
     * @param $name
     * @param $value
     */
    public function addVar($name, $value)
    {
        $this->getCurrentBlock()['vars'][$name] = $value;
    }

    /**
     * @param string $url
     * @param string $type
     * @throws \InvalidArgumentException
     */
    public function addImport($url, $type)
    {
        if (!in_array($type, [static::TYPE_JS, static::TYPE_CSS])) {
            throw new \InvalidArgumentException('Wrong import type: ' . $type);
        }
        $this->getCurrentBlock()['imports'][] = [$url, $type, sha1($type . $url)];
    }

    // -- Getters ---------------------------------------

    /**
     * @param string|null $block
     * @return array
     */
    public function getVariables($block = null)
    {
        return $this->getCurrentBlock($block)['vars'];
    }

    /**
     * @param string|null $block
     * @param string      $ext
     * @return array
     */
    public function getFiles($block, $ext)
    {
        $list = $this->getCurrentBlock($block)['files'];

        $ret = [];
        // TODO: cache
        foreach ($list as $file) {
            $file_ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($file_ext == 'scss') {
                $file_ext = static::TYPE_CSS;
            }
            if ($file_ext == $ext) {
                $ret[] = $file;
            }
        }

        return $ret;
    }

    /**
     * @param string|null $block
     * @return array[]
     */
    public function getImports($block = null)
    {
        return $this->getCurrentBlock($block)['imports'];
    }

    // -- Packages ---------------------------------------

    protected $packages = [];

    /**
     * @param string|null $block
     * @return array
     */
    protected function &getPackageList($block = null)
    {
        if ($this->isSplit) {
            $list = & $this->getCurrentBlock($block)['packages'];
        } else {
            $list = & $this->packages;
        }
        return $list;
    }

    /**
     * @param string      $name
     * @param string|null $block
     * @return $this
     */
    public function addPackage($name, $block = null)
    {
        $this->getPackageList($block)[] = $name;
        return $this;
    }

    /**
     * @param string      $name
     * @param string|null $block
     * @return bool
     */
    public function isPackageLoaded($name, $block = null)
    {
        return in_array($name, $this->getPackageList($block));
    }

    /**
     * @param string|null $block
     * @return array
     */
    public function getPackages($block = null)
    {
        if ($this->isSplit) {
            $ret = $this->getCurrentBlock($block)['packages'];
        } else {
            $ret = $this->packages;
        }
        return array_unique($ret);
    }

    // -- Log ---------------------------------------

    protected $log = [];

    /**
     * @param string $tag
     * @param string $msg
     */
    protected function log($tag, $msg)
    {
        $this->log[] = $tag . ': ' . $msg;
    }

    /**
     * @return array
     */
    public function getLog()
    {
        return $this->log;
    }

    // -- Blocks ---------------------------------------

    protected $blocks = [];
    protected $blocksStack = [];

    /**
     * @param string $name
     * @return $this
     */
    public function blockStart($name)
    {
        $this->log('block start', $name);
        $this->createBlock($name);
        array_unshift($this->blocksStack, $name);
        $this->log('block', $this->blocksStack[0]);
        return $this;
    }

    /**
     * @return $this
     */
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

    /**
     * @return array
     */
    public function getBlocks()
    {
        while (count($this->blocksStack)) {
            $this->blockEnd();
        }
        return array_keys($this->blocks);
    }

    /**
     * @param string $name
     * @return \array[]
     */
    public function createBlock($name)
    {
        return $this->getCurrentBlock($name, true);
    }

    /**
     * @param string|null $block
     * @param bool        $createBlock
     * @return array[]
     * @throws \InvalidArgumentException
     */
    protected function &getCurrentBlock($block = null, $createBlock = false)
    {
        if (!$block) {
            $block = $this->blocksStack[0];
        }
        if (!isset($this->blocks[$block])) {
            if (!$createBlock) {
                throw new \InvalidArgumentException('Wrong block name');
            }
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
