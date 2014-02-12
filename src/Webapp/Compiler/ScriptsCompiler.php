<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Compiler;

use Werkint\Bundle\WebappBundle\Webapp\Processor\DefaultProcessor;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * ScriptsCompiler.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ScriptsCompiler
{
    const VAR_PREFIX = '$webapp';
    const STRICT_MODE = '"use strict"';

    protected $processor;
    protected $project;
    protected $strictMode;

    /**
     * @param DefaultProcessor $processor
     * @param string           $project
     * @param bool             $strictMode
     */
    public function __construct(
        DefaultProcessor $processor,
        $project,
        $strictMode = false
    ) {
        $this->processor = $processor;
        $this->project = $project;
        $this->strictMode = $strictMode;
    }

    /**
     * @param array    $vars
     * @param string   $block
     * @param string   $filepath
     * @param string[] $files
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function compile(
        array $vars,
        $block,
        $filepath,
        array $files
    ) {
        // TODO: better way of compiling
        $data = [];
        $data[] = 'void function(window){';
        if ($this->strictMode) {
            $data[] = static::STRICT_MODE;
        }
        $data[] = 'var definejs = window.define?window.define:function(){}';
        $data[] = 'window.define = null';

        if ($block == ScriptLoader::ROOT_BLOCK) {
            $data[] = static::VAR_PREFIX . '={"var":{}}';
        }
        foreach ($vars as $name => $value) {
            $name = str_replace('-', '_', $name);
            $prefix = explode('_', $name)[0];
            if ($prefix == 'webapp') {
                $name = substr($name, strlen($prefix) + 1);
                $prefix = '';
            } else {
                $prefix = 'var.';
            }
            $data[] = static::VAR_PREFIX . '.' . $prefix . $name . '=' . json_encode($value);
        }

        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new \InvalidArgumentException('File not found: ' . $file);
            }
            $data[] = file_get_contents($file);
        }
        $data[] = 'window.define = definejs';
        $data[] = '}(window)';
        $data = join(";\n", $data);

        $data = $this->processor->process($data);
        file_put_contents($filepath, $data);
        touch($filepath);

        return true;
    }

}
