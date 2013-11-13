<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Processor;

/**
 * StylesProcessor.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StylesProcessor extends DefaultProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
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
