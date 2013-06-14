<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

use JsMin;
use MinifyCSS;
use SassParser;

class Compiler
{

    /**
     * @var ScriptHandler
     */
    protected $handler;
    protected $targetdir;

    protected $isDebug;

    protected $strictMode = false;

    public function __construct($handler, $targetdir, $isDebug)
    {
        // TODO: to service
        if (!file_exists($targetdir)) {
            throw new \Exception('Directory not found: ' . $targetdir);
        }
        $this->handler = $handler;
        $this->targetdir = $targetdir;
        $this->isDebug = $isDebug;
    }

    public function compile($revision)
    {
        $hash = $this->handler->getDataHash() . '_rev' . $revision;
        $filepath = $this->targetdir . '/' . $hash;

        // Compile, if needed
        $files = $this->handler->getFiles('scss');
        if (!$this->isFresh($filepath . '.css', $files)) {
            $this->loadStyles($filepath . '.css', $files);
        }
        $files = $this->handler->getFiles('js');
        if (!$this->isFresh($filepath . '.js', $files)) {
            $this->loadScripts($filepath . '.js', $files);
        }

        // Return hash
        return $hash;
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

    protected function loadStyles($filepath, &$files)
    {
        $data = array();
        $updVars = function ($vars, $prefix) use (&$data, &$updVars) {
            foreach ($vars as $name => $value) {
                $pr = $prefix . '-' . str_replace('_', '-', $name);
                if (is_array($value)) {
                    $updVars($value, $pr);
                }
                if (!is_scalar($value)) {
                    continue;
                }
                $data[] = $pr . ': "' . str_replace('"', '\\"', $value) . '";';
            }
        };
        $updVars($this->handler->getVariables(), '$const');
        foreach ($files as $file) {
            $data[] = file_get_contents($file);
        }
        $data = join("\n", $data);

        $parser = new SassParser(array(
            'style'  => 'nested',
            'cache'  => false,
            'syntax' => 'scss',
            'debug'  => $this->isDebug,
        ));
        try {
            $data = $parser->toCss($data, false);

            $imports = array();
            foreach ($this->handler->getImports() as $url) {
                $imports[] = '@import url("' . $url . '")';
            }
            $data = join('', $imports) . $data;
        } catch (\Exception $e) {
            throw new \Exception(
                'SCSS compiler error: ' . $e->getMessage() . ', loaded files: ' . print_r($files, true)
            );
        }
        file_put_contents($filepath, $data);
    }

    protected function loadScripts($filepath, &$files)
    {
        $data = array();
        if ($this->strictMode) {
            $data[] = '"use strict"';
        }
        $data[] = 'window.CONST = {}';
        foreach ($this->handler->getVariables() as $name => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            } elseif (is_scalar($value)) {
                $value = '"' . str_replace('"', '\\"', $value) . '"';
            } else {
                throw new \Exception('Wrong variable type: ' . gettype($value));
            }
            $data[] = 'window.CONST.' . str_replace('-', '_', $name) . ' = ' . $value;
        }
        foreach ($files as $file) {
            $data[] = file_get_contents($file);
        }
        $data = join(";\n", $data);
        if (!$this->isDebug) {
            \JsMin\Minify::minify($data);
        }
        file_put_contents($filepath, $data);
    }

}