<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

/**
 * WebappInterface.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
interface WebappInterface
{
    /**
     * @return ScriptLoader
     */
    public function getLoader();

    /**
     * @param string $name
     */
    public function attachFile($name);

    /**
     * @param string $url
     */
    public function addImportCss($url);

    /**
     * @param string $url
     */
    public function addImportJs($url);

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function addVar($name, $value);

    /**
     * @param bool $flag
     */
    public function setIsSplit($flag);

    /**
     * @param array $vars
     */
    public function addVars(array $vars);
} 