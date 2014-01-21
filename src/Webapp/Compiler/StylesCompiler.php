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
    protected $processor;
    protected $project;

    /**
     * @param DefaultProcessor $processor
     * @param string           $project
     */
    public function __construct(
        DefaultProcessor $processor,
        $project
    ) {
        $this->processor = $processor;
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
        $data = [];
        $updVars = function ($vars, $project) use (&$data, &$updVars) {
            foreach ($vars as $name => $value) {
                $name = str_replace('_', '-', $name);
                $top = explode('-', $name)[0];
                if ($top != 'webapp') {
                    $name = $project . '-' . $name;
                }
                if (is_array($value)) {
                    $updVars($value, $name);
                }
                if (!is_scalar($value)) {
                    // Compilation only possible for variables
                    continue;
                }
                $data[] = '$' . $name . ':"' . str_replace('"', '\\"', $value) . '";';
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

        $data = $this->processor->process($data);
        if ($prefixData) {
            $data = substr($data, strpos($data, $hr));
        }
        file_put_contents($filepath, $data);

        return $retdata;
    }

}
