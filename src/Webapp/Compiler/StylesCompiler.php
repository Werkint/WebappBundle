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
     * @param array  $vars
     * @param string $filepath
     * @param array  $files
     * @return boolean
     */
    public function compile(
        array $vars,
        $filepath,
        array $files
    ) {
        $data = array_merge([
            '@charset "utf-8";',
        ], $this->compileVars($vars, $this->project));

        // Подключаем все файлы
        foreach ($files as $file) {
            $data[] = '@import "' . $file . '";';
        }

        $filters = array_map(function ($name) {
            return $this->filterManager->get($name);
        }, $this->filters);
        $asset = new StringAsset(
            join("\n", $data),
            $filters
        );

        file_put_contents($filepath, $asset->dump());

        return true;
    }

    /**
     * @param array $data
     * @param null  $prefixVars
     * @return array|string[]
     */
    protected function compileVars(array $data, $prefixVars = null)
    {
        $ret = [];
        foreach ($data as $name => $value) {
            $name = str_replace('_', '-', $name);
            $top = explode('-', $name)[0];
            if ($top != 'webapp' && $prefixVars) {
                $name = $prefixVars . '-' . $name;
            }
            $parsedValue = $this->compileVarsParse($value);
            if ($parsedValue !== '()') {
                $ret[] = '$' . $name . ':' . $parsedValue . ';';
            } else {
                continue;
            }
        }

        return $ret;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function compileVarsParse($value)
    {
        if (is_array($value)) {
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
                $ret .= '"' . str_replace('"', '\\"', $key) . '":' . $this->compileVarsParse($elem);
            };
            $ret .= ")";
            return $ret;
        }
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
    }
}
