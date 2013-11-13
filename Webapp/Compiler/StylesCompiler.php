<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Compiler;

use Werkint\Bundle\WebappBundle\Webapp\Processor\DefaultProcessor;

/**
 * StylesCompiler.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StylesCompiler
{
    const VAR_PREFIX = 'const';

    protected $processor;

    /**
     * @param DefaultProcessor $processor
     */
    public function __construct(
        DefaultProcessor $processor
    ) {
        $this->processor = $processor;
    }

    /**
     * @param array       $vars
     * @param string      $filepath
     * @param array       $files
     * @param string|null $prefixData
     * @return string
     * @throws \Exception
     */
    public function compile(
        array $vars,
        $filepath,
        array $files,
        $prefixData = null
    ) {
        $data = [];
        $updVars = function ($vars, $prefix) use (&$data, &$updVars) {
            foreach ($vars as $name => $value) {
                $pr = $prefix . '-' . str_replace('_', '-', $name);
                if (is_array($value)) {
                    $updVars($value, $pr);
                }
                if (!is_scalar($value)) {
                    // Compiling only possible variables
                    continue;
                }
                $data[] = '$' . $pr . ':"' . str_replace('"', '\\"', $value) . '";';
            }
        };
        $updVars($vars, static::VAR_PREFIX);
        foreach ($files as $file) {
            if (!file_exists($file)) {
                throw new \InvalidArgumentException('File not found: ' . $file);
            }
            $data[] = file_get_contents($file);
        }
        $data = join("\n", $data);

        $retdata = $data;

        $hr = null;
        if ($prefixData) {
            $hr = '.HR' . sha1(microtime(true) . $filepath);
            $data = $prefixData . $hr . '{ display: none; }' . $data;
        }

        $data = $this->processor->process($data);
        if ($prefixData) {
            $data = substr($data, 0, strpos($data, $hr));
        }
        file_put_contents($filepath, $data);

        return $retdata;
    }

}
