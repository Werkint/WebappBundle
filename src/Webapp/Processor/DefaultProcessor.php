<?php
namespace Werkint\Bundle\WebappBundle\Webapp\Processor;

/**
 * DefaultProcessor.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
class DefaultProcessor
{
    protected $isDebug;

    /**
     * @param bool $isDebug
     */
    public function __construct(
        $isDebug = false
    ) {
        $this->isDebug = $isDebug;
    }

    /**
     * @param string $data
     * @return string
     */
    public function process($data)
    {
        return $data;
    }

    /**
     * @return bool
     */
    public function getIsDebug()
    {
        return $this->isDebug;
    }
} 