<?php
namespace Werkint\Bundle\WebappBundle\Twig\Extension;

use Twig_Template;

/**
 * Template.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
abstract class Template extends Twig_Template
{
    const BLOCK_PREFIX = 'globalhook_';

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
     * {@inheritdoc}
     */
    protected function getEvent()
    {
        return $this
            ->getWebappExtension()
            ->getTemplateEvent();
    }

    /**
     * {@inheritdoc}
     */
    public function display(
        array $context,
        array $blocks = []
    ) {
        // call parent
        parent::display($context, $blocks);

        // Getting parameters
        $name = $this->getTemplateName();
        $path = $this->getEnvironment()->getLoader()->findTemplate(
            $this->getTemplateName()
        );

        // DisplayPost event
        $this->getEvent()
            ->setTemplateName($name)
            ->setTemplatePath($path)
            ->dispatch('werkint.webapp.displaypost');
    }

    /**
     * {@inheritdoc}
     */
    public function displayBlock(
        $name,
        array $context,
        array $blocks = []
    ) {
        $tpl = null;
        // if block is marked to trigger event
        if (strpos($name, static::BLOCK_PREFIX) === 0) {
            // getting block subname
            $tpl = substr($name, strlen(static::BLOCK_PREFIX));
        }

        // BlockPre event
        if ($tpl) {
            $this->getEvent()
                ->setBlockName($tpl)
                ->dispatch('werkint.webapp.blockpre');
        }

        // call parent
        parent::displayBlock($name, $context, $blocks);

        // BlockPost event
        if ($tpl) {
            $this->getEvent()
                ->setBlockName($tpl)
                ->dispatch('werkint.webapp.blockpost');
        }
    }

}
