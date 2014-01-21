<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Processor;

use JsMin\Minify;

/**
 * ScriptsProcessor.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class ScriptsProcessor extends DefaultProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if ($this->isDebug) {
            return $data;
        }
        return Minify::minify($data);
    }

}
