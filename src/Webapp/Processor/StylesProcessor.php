<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Processor;

/**
 * StylesProcessor.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StylesProcessor extends DefaultProcessor
{
    protected $gemPath;

    /**
     * @param string $gemPath
     * @return $this
     */
    public function setGemPath($gemPath)
    {
        $this->gemPath = $gemPath;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if ($this->gemPath) {
            return $this->processConsole($data);
        } else {
            return $this->processPhpsass($data);
        }
    }

    protected function processConsole($data)
    {
        $name = '/tmp/' . sha1($data) . '_' . sha1(microtime(true) . 'agf');
        file_put_contents($name, $data);
        putenv('PATH=' . $this->gemPath . '/bin');
        putenv("GEM_PATH=" . $this->gemPath);
        exec('scss --trace ' . $name . ' 2>&1', $var, $ret);
        if ($ret == 1) {
            $row = explode(':', $var[0])[1] - 1;
            $data = explode("\n", $data);
            $row = array_slice($data, $row - 5, 10);
            echo 'Code:<br/><pre>' . join("\n", $row) . '</pre>';
            echo '<pre>' . join("\n", $var) . '</pre>';
            die();
        }
        return join("\n", $var);
    }

    protected function processPhpsass($data)
    {
        $parser = new \SassParser([
            'style'  => \SassRenderer::STYLE_COMPRESSED,
            'cache'  => false,
            'syntax' => 'scss',
            'debug'  => $this->isDebug,
        ]);

        return $parser->toCss($data, false);
    }

}
