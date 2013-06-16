<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

class ScriptHandler
{

    protected $log = [];

    protected function log($tag, $msg)
    {
        $this->log[] = $tag . ': ' . $msg;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function getDataHash()
    {
        $hash = serialize($this->getVariables()) . serialize($this->blocks);
        return substr(sha1($hash), 0, 10);
    }

    protected $vars = [];

    public function getVariables()
    {
        return $this->vars;
    }

    public function addVar($name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function appendFile(&$path)
    {
        $this->log('file in [' . $this->blocksStack[0] . ']', $path);
        $this->blocks[$this->blocksStack[0]][] = $path;
    }

    public function getFiles($blocks = null, $ext = null)
    {
        if (!$ext) {
            return $this->blocks[$blocks];
        } else {
            $ret = [];
            foreach ($this->blocks[$blocks] as $file) {
                if (in_array($file, $ret)) {
                    continue;
                }
                if (pathinfo($file, PATHINFO_EXTENSION) == $ext) {
                    $ret[] = $file;
                }
            }
            return $ret;
        }
    }

    protected $loaded = [];

    public function wasLoaded($name)
    {
        return isset($this->loaded[$name]);
    }

    public function setLoaded($name)
    {
        if ($this->wasLoaded($name)) {
            throw new \Exception('Script already was loaded');
        }
        $this->loaded[$name] = true;
    }

    protected $cssImports = [];

    public function addCssImport($url)
    {
        $this->cssImports[] = $url;
    }

    public function getImports()
    {
        return $this->cssImports;
    }

    // -- Blocks ---------------------------------------

    protected $blocks = [];
    protected $blocksStack = [];

    public function blockStart($name)
    {
        $this->log('block start', $name);
        if (!isset($this->blocks[$name])) {
            $this->blocks[$name] = [];
        }
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

}