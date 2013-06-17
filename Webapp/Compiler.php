<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

use JsMin;

class Compiler
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
        foreach ($loader->getBlocks() as $block) {
            $blockRev = '_r' . $this->revision . '_' . $block;
            $vars = $loader->getVariables($block);
            $blocks[$block]['imports'] = $loader->getImports($block);

            // Files to compile
            $filesCss = $loader->getFiles($block, 'scss');
            // var_dump($filesCss);die();
            $filesJs = $loader->getFiles($block, 'js');

            // CSS
            $name = $this->getHash($filesCss) . $blockRev;
            $blocks[$block]['css'] = $name;
            $blockPath = $this->targetdir . '/' . $name;
            // Compile, if needed
            if (!$this->isFresh($blockPath . '.css', $filesCss)) {
                $data = $this->loadStyles($vars, $block, $blockPath . '.css', $filesCss, $root);
                file_put_contents($blockPath . '.scss', $data);
            }

            if ($block == '_root') {
                $root = file_get_contents($blockPath . '.scss');
            }

            // JS
            $name = $this->getHash($filesJs) . $blockRev;
            $blocks[$block]['js'] = $name;
            $blockPath = $this->targetdir . '/' . $name;
            // Compile, if needed
            if (!$this->isFresh($blockPath . '.js', $filesJs)) {
                if ($block == 'page') {
                    $vars['_packages'] = $loader->getPackages($block);
                }
                $this->loadScripts($vars, $block, $blockPath . '.js', $filesJs);
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

    protected function loadStyles(array $vars, $block, $filepath, array &$files, $prefixData = null)
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
        $updVars($vars, '$const');
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

    protected function loadScripts(array $vars, $block, $filepath, array &$files)
    {
        $data = [];
        if ($this->strictMode) {
            $data[] = '"use strict"';
        }
        $data[] = 'if(!window.CONST){window.CONST = {};}';

        $prefix = $block != '_root' ? $block : '';
        if ($prefix) {
            $data[] = 'window.CONST.' . $prefix . ' = {}';
            $prefix .= '.';
        }

        foreach ($vars as $name => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            } elseif (is_scalar($value)) {
                $value = '"' . str_replace('"', '\\"', $value) . '"';
            } else {
                throw new \Exception('Wrong variable type: ' . gettype($value));
            }
            $data[] = 'window.CONST.' . $prefix . str_replace('-', '_', $name) . ' = ' . $value;
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