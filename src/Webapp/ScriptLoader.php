<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

/**
 * ScriptLoader.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ScriptLoader implements
    ScriptLoaderInterface
{
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
     * {@inheritdoc}
     */
    public function setIsSplit($isSplit)
    {
        $this->isSplit = (bool)$isSplit;
    }

    protected $jsList = [];

    /**
     * {@inheritdoc}
     */
    public function attachFile($path, $ignore_check = false)
    {
        if (!file_exists($path)) {
            if (!$ignore_check) {
                throw new \InvalidArgumentException('Script not found: ' . $path);
            }
        } else {
            $path = realpath($path);

            if (preg_match('!\.js$!', $path)) {
                if (in_array($path, $this->jsList)) {
                    unset($this->jsList[array_search($path, $this->jsList)]);
                }
                $this->jsList[] = $path;
            }

            $this->log('file in [' . $this->blocksStack[0] . ']', $path);

            if (in_array($path, $this->getCurrentBlock()['files'])) {
                unset($this->getCurrentBlock()['files'][array_search($path, $this->getCurrentBlock()['files'])]);
            }
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function addVar($name, $value)
    {
        $this->getCurrentBlock()['vars'][$name] = $value;
    }

    // -- Getters ---------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getVariables($block = null)
    {
        return $this->getCurrentBlock($block)['vars'];
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function addPackage($name, $block = null)
    {
        $this->getPackageList($block)[] = $name;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPackageLoaded($name, $block = null)
    {
        return in_array($name, $this->getPackageList($block));
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getLog()
    {
        // TODO: PSR-3
        return $this->log;
    }

    // -- Blocks ---------------------------------------

    protected $blocks = [];
    protected $blocksStack = [];

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getBlocks()
    {
        return array_keys($this->blocks);
    }

    /**
     * {@inheritdoc}
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
                'packages' => [],
            ];
        }
        return $this->blocks[$block];
    }

}
