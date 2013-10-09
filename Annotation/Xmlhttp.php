<?php
namespace Werkint\Bundle\WebappBundle\Annotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Xmlhttp extends Post
{
    const HEADER_VALUE = 'XMLHttpRequest';
    const KEYNAME = '_x_requested';

    public function __construct(
        array $data
    ) {
        parent::__construct($data);

        $requirements = $this->getRequirements();
        $requirements[static::KEYNAME] = static::HEADER_VALUE;
        $this->setRequirements($requirements);

        $defaults = $this->getDefaults();
        $defaults['_format'] = 'json';
        $this->setDefaults($defaults);
    }
}