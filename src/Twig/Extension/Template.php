<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

/**
 * Template.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
abstract class Template extends \Twig_Template
{
    const BLOCK_PREFIX = 'globalhook_';
    const EVENT_DISPLAY = 'werkint.webapp.displaypost';
    const EVENT_BLOCK_PRE = 'werkint.webapp.blockpre';
    const EVENT_BLOCK_POST = 'werkint.webapp.blockpost';

    /**
     * @return Extension
     */
    protected function getWebappExtension()
    {
        return $this
            ->getEnvironment()
            ->getExtension(Extension::EXT_NAME);
    }

    /**
     * @return TemplateEvent
     */
    protected function getEvent()
    {
        return $this
            ->getWebappExtension()
            ->getTemplateEvent();
    }

    // -- Hooks ---------------------------------------

    /**
     * @return bool
     */
    protected function processDisplay()
    {
        $loader = $this->getEnvironment()->getLoader();
        if (!($loader instanceof TwigLoader)) {
            return false;
        }
        // Getting parameters
        $name = $this->getTemplateName();
        $path = $loader->findTemplate(
            $this->getTemplateName()
        );

        // DisplayPost event
        $this->getEvent()
            ->setTemplateName($name)
            ->setTemplatePath($path)
            ->dispatch(static::EVENT_DISPLAY);
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function processBlockPre($name)
    {
        $tpl = null;
        // if block is marked to trigger event
        if (strpos($name, static::BLOCK_PREFIX) === 0) {
            // getting block subname
            $tpl = substr($name, strlen(static::BLOCK_PREFIX));
        }

        if (!$tpl) {
            return false;
        }

        $this->getEvent()
            ->setBlockName($tpl)
            ->dispatch(static::EVENT_BLOCK_PRE);

        return $tpl;
    }

    /**
     * @param string $tpl
     * @return bool
     */
    protected function processBlockPost($tpl)
    {
        if (!$tpl) {
            return false;
        }

        $this->getEvent()
            ->setBlockName($tpl)
            ->dispatch(static::EVENT_BLOCK_POST);
    }

    // -- Overrides ---------------------------------------

    /**
     * {@inheritdoc}
     */
    public function display(
        array $context,
        array $blocks = []
    ) {
        // call parent
        parent::display($context, $blocks);

        // display hook
        return $this->processDisplay();
    }

    /**
     * {@inheritdoc}
     */
    public function displayBlock(
        $name,
        array $context,
        array $blocks = [],
        $useBlocks = true
    ) {
        // hook block pre
        $tpl = $this->processBlockPre($name);

        // call parent
        parent::displayBlock($name, $context, $blocks, $useBlocks);

        // hook block post
        return $this->processBlockPost($tpl);
    }

}
