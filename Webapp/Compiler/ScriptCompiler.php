<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Compiler;

use JsMin;

class ScriptCompiler
{
    protected $strictMode;
    protected $isDebug;

    public function __construct($isDebug, $strictMode)
    {
        $this->isDebug = $isDebug;
        $this->strictMode = $strictMode;
    }

    public function compile(array $vars, $block, $filepath, array &$files)
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