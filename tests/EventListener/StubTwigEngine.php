<?php
namespace Werkint\Bundle\WebappBundle\Tests\EventListener;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * StubTwigEngine.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StubTwigEngine implements
    EngineInterface
{
    protected $templates;

    /**
     * @param array $templates
     */
    public function __construct(
        array $templates
    ) {
        $this->templates = $templates;
    }

    /**
     * {@inheritdoc}
     */
    public function render(
        $name,
        array $parameters = []
    ) {
        return serialize($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return isset($this->templates[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($name)
    {
        return isset($this->templates[$name]);
    }

} 