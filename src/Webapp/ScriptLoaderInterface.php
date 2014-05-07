<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

/**
 * ScriptLoaderInterface.
 *
 * @author Bogdan Yurov <bogdan@yurov.me>
 */
interface ScriptLoaderInterface
{
    const TYPE_JS = 'js';
    const TYPE_CSS = 'css';
    const ROOT_BLOCK = '_root';

    /**
     * @param bool $isSplit
     */
    public function setIsSplit($isSplit);

    /**
     * Attaches one script
     *
     * @param string $path
     * @param bool   $ignore_check
     * @throws \InvalidArgumentException
     * @return bool
     */
    public function attachFile($path, $ignore_check = false);

    /**
     * Attaches related to template files
     *
     * @param string $path
     * @param bool   $ignore_check
     * @throws \InvalidArgumentException
     */
    public function attachViewRelated($path, $ignore_check = false);

    /**
     * @param $name
     * @param $value
     */
    public function addVar($name, $value);

    /**
     * @param string|null $block
     * @return array
     */
    public function getVariables($block = null);

    /**
     * @param string|null $block
     * @param string      $ext
     * @return array
     */
    public function getFiles($block, $ext);


    /**
     * @param string      $name
     * @param string|null $block
     * @return $this
     */
    public function addPackage($name, $block = null);

    /**
     * @param string      $name
     * @param string|null $block
     * @return bool
     */
    public function isPackageLoaded($name, $block = null);

    /**
     * @param string|null $block
     * @return array
     */
    public function getPackages($block = null);

    /**
     * @return array
     */
    public function getLog();

    /**
     * @param string $name
     * @return $this
     */
    public function blockStart($name);

    /**
     * @return $this
     */
    public function blockEnd();

    /**
     * @return array
     */
    public function getBlocks();

    /**
     * @param string $name
     * @return \array[]
     */
    public function createBlock($name);
} 