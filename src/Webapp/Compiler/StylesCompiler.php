<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Compiler;

use Assetic\Asset\StringAsset;
use Symfony\Bundle\AsseticBundle\FilterManager;

/**
 * StylesCompiler.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StylesCompiler
{
    protected $filterManager;
    protected $filters;
    protected $project;

    public function __construct(
        FilterManager $filterManager,
        array $filters,
        $project
    ) {
        $this->filterManager = $filterManager;
        $this->filters = $filters;
        $this->project = $project;
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
        $data = [
            '@charset "utf-8";',
        ];
        $parseValue = function ($value) use (&$parseValue) {
            if (!is_array($value)) {
                if (is_int($value)) {
                    return $value;
                }
                if (is_bool($value)) {
                    return $value ? 'true' : 'false';
                }
                if (is_null($value)) {
                    return 'null';
                }
                return '"' . str_replace('"', '\\"', $value) . '"';
            } else {
                $ret = "(";
                foreach ($value as $key => $elem) {
                    if (!is_array($value)) {
                        if (!is_scalar($elem)) {
                            continue;
                        }
                    }
                    if (!(substr($ret, -1) === '(')) {
                        $ret .= ',';
                    };
                    $ret .= '"' . str_replace('"', '\\"', $key) . '":' . $parseValue($elem);
                };
                $ret .= ")";
                return $ret;
            }
        };
        $updVars = function ($vars, $project) use (&$data, &$parseValue) {
            foreach ($vars as $name => $value) {
                $name = str_replace('_', '-', $name);
                $top = explode('-', $name)[0];
                if ($top != 'webapp') {
                    $name = $project . '-' . $name;
                }
                $parsedValue = $parseValue($value);
                if ($parsedValue !== '()') {
                    $data[] = '$' . $name . ':' . $parsedValue . ';';
                } else {
                    continue;
                }
            }
        };
        $updVars($vars, $this->project);
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
            $data = $prefixData . $hr . '{ display: none; };' . $data;
        }

        $filters = array_map(function ($name) {
            return $this->filterManager->get($name);
        }, $this->filters);
        $asset = new StringAsset(
            $data,
            $filters
        );
        $data = $asset->dump();
        if ($prefixData) {
            $data = substr($data, strpos($data, $hr));
        }
        file_put_contents($filepath, $data);

        return $retdata;
    }

}
