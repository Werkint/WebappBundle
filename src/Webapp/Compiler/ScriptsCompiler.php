<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Compiler;

use Assetic\Asset\StringAsset;
use Symfony\Bundle\AsseticBundle\FilterManager;
use Werkint\Bundle\WebappBundle\Webapp\ScriptLoader;

/**
 * ScriptsCompiler.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ScriptsCompiler
{
    const VAR_PREFIX = '$webapp';

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
        //$data[] = 'void function(window){';

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
        //$data[] = '}(window)';
        $data = join(";\n", $data);

        $filters = array_map(function ($name) {
            return $this->filterManager->get($name);
        }, $this->filters);
        $asset = new StringAsset(
            $data,
            $filters
        );
        $data = $asset->dump();

        file_put_contents($filepath, $data);
        touch($filepath);

        return true;
    }

}
