<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

use JsMin;

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

    public function compile($revision, $blockIn = null)
    {
        $hash = $this->handler->getDataHash() . '_r' . $revision;

        $blocks = [];
        $root = null;
        foreach ($this->handler->getBlocks() as $block) {
            $blocks[$block] = $blockPath = $hash . '_' . $block;
            $blockPath = $this->targetdir . '/' . $blockPath;

            // Compile, if needed
            $files = $this->handler->getFiles($block, 'scss');
            if (!$this->isFresh($blockPath . '.css', $files)) {
                $data = $this->loadStyles($blockPath . '.css', $files, $root);
                file_put_contents($blockPath . '.scss', $data);
            }
            $files = $this->handler->getFiles($block, 'js');
            if (!$this->isFresh($blockPath . '.js', $files)) {
                $this->loadScripts($blockPath . '.js', $files);
            }

            if ($block == '_root') {
                $root = file_get_contents($blockPath . '.scss');
            }
        }

        if ($blockIn) {
            if (!isset($blocks[$blockIn])) {
                throw new \Exception('wrong block: ' . $blockIn);
            } else {
                return $blocks[$blockIn];
            }
        }

        // Return hashes
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

    protected function loadStyles($filepath, &$files, $prefixData = null)
    {
        $data = [];
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

        $parser = new \SassParser([
            'style'  => 'nested',
            'cache'  => false,
            'syntax' => 'scss',
            'debug'  => $this->isDebug,
        ]);
        $retdata = $data;

        $hr = null;
        if ($prefixData) {
            $hr = '.HR' . sha1(microtime(true) . $filepath);
            $data = $prefixData . $hr . '{ display: none; }' . $data;
        }
        try {
            $data = $parser->toCss($data, false);
            if ($prefixData) {
                $data = substr($data, strpos($data, $hr));
            }
        } catch (\Exception $e) {
            throw new \Exception(
                'SCSS compiler error in file "' . $filepath . '": ' . $e->getMessage() . ', loaded files: ' . print_r($files, true)
            );
        }
        file_put_contents($filepath, $data);
        return $retdata;
    }

    protected function loadScripts($filepath, &$files)
    {
        $data = [];
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
            JsMin\Minify::minify($data);
        }
        file_put_contents($filepath, $data);
    }

}