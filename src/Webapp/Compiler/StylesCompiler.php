<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Compiler;

use Assetic\Asset\StringAsset;
use Assetic\Filter\Sass\SassFilter;
use Symfony\Bundle\AsseticBundle\FilterManager;

/**
 * StylesCompiler.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StylesCompiler
{
    protected $filterManager;
    protected $project;

    public function __construct(
        FilterManager $filterManager,
        $project
    ) {
        $this->filterManager = $filterManager;
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

        $filter = $this->filterManager->get('scss');
        /** @var SassFilter $filter */
        $asset = new StringAsset(
            $data,
            [$filter]
        );
        $asset->load();
        $data = $asset->getContent();
        if ($prefixData) {
            $data = substr($data, strpos($data, $hr));
        }
        file_put_contents($filepath, $data);

        return $retdata;
    }

}
