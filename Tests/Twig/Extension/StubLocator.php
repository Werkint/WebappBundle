<?php
namespace Werkint\Bundle\WebappBundle\Tests\Twig\Extension;

use Symfony\Component\Config\FileLocatorInterface;

/**
 * StubLocator.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class StubLocator implements
    FileLocatorInterface
{
    protected $ret;

    /**
     * @param string $ret
     */
    public function __construct($ret)
    {
        $this->ret = $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function locate($name, $currentPath = null, $first = true)
    {
        return $name == $this->ret;
    }
} 