<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

class ScriptHandler
{

    public function getDataHash()
    {
        return md5(serialize($this->getVariables()) . serialize($this->getFiles()));
    }

    protected $vars = array();

    public function getVariables()
    {
        return $this->vars;
    }

    public function addVar($name, $value)
    {
        $this->vars[$name] = $value;
    }

    protected $files = array();

    public function appendFile($path)
    {
        $this->files[] = $path;
    }

    public function getFiles($ext = null)
    {
        if (!$ext) {
            return $this->files;
        } else {
            $ret = array();
            foreach ($this->files as $file) {
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

    protected $loaded = array();

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

    protected $cssImports = array();

    public function addCssImport($url)
    {
        $this->cssImports[] = $url;
    }

    public function getImports()
    {
        return $this->cssImports;
    }

}