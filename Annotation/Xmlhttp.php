<?php
namespace Werkint\Bundle\WebappBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Xmlhttp extends Post
{
    const HEADER_VALUE = 'XMLHttpRequest';

    public function __construct(
        array $data
    ) {
        parent::__construct($data);

        $requirements = $this->getRequirements();
        $requirements['_x_requested'] = static::HEADER_VALUE;
        $this->setRequirements($requirements);

        $defaults = $this->getDefaults();
        $defaults['_format'] = 'json';
        $this->setDefaults($defaults);
    }
}