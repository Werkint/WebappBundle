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
    const VAR_PREFIX = 'CONST';
    const STRICT_MODE = '"use strict"';

    protected $processor;
    protected $strictMode;

    /**
     * @param DefaultProcessor $processor
     * @param bool             $strictMode
     */
    public function __construct(
        DefaultProcessor $processor,
        $strictMode = false
    ) {
        $this->processor = $processor;
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
        $data = [];
        if ($this->strictMode) {
            $data[] = static::STRICT_MODE;
        }

        $prefix = $block && $block != ScriptLoader::ROOT_BLOCK ? $block : '';
        if ($prefix) {
            $data[] = static::VAR_PREFIX . '.' . $prefix . '={}';
            $prefix .= '.';
        }

        foreach ($vars as $name => $value) {
            $value = json_encode($value);
            $data[] = static::VAR_PREFIX . '.' . $prefix . str_replace('-', '_', $name) . '=' . $value;
        }
        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new \InvalidArgumentException('File not found: ' . $file);
            }
            $data[] = file_get_contents($file);
        }
        $data = join(";\n", $data);

        $data = $this->processor->process($data);
        file_put_contents($filepath, $data);
        touch($filepath);

        return true;
    }

}
