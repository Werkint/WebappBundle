<?php
namespace Werkint\Bundle\WebappBundle\Hacks;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

class ShortTemplateReference extends TemplateReference
{
    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        $controller = str_replace('\\', '/', $this->get('controller'));

        $path = (empty($controller) ? '' : $controller . '/') . $this->get('name') . '.' . $this->get('engine');

        return empty($this->parameters['bundle']) ? 'views/' . $path : '@' . $this->get('bundle') . '/Resources/views/' . $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogicalName()
    {
        return sprintf('%s:%s:%s.%s', $this->parameters['bundle'], $this->parameters['controller'], $this->parameters['name'], $this->parameters['engine']);
    }
}
