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
     * @param string $name
     * @param string $ext
     */
    public function attachByTwigAlias($name, $ext);

    /**
     * @param string $name
     * @param mixed  $value
     * @param bool   $isRoot
     * @return
     */
    public function addVar($name, $value, $isRoot = false);

    /**
     * @param bool $flag
     */
    public function setIsSplit($flag);

    /**
     * @param array $vars
     */
    public function addVars(array $vars);
} 