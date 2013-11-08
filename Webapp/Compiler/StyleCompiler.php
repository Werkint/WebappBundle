<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Compiler;

/**
 * StyleCompiler.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StyleCompiler
{
    protected $isDebug;

    public function __construct($isDebug)
    {
        $this->isDebug = $isDebug;
    }

    public function compile(array $vars, $block, $filepath, array &$files, $prefixData = null)
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

}