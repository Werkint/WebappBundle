<?php
namespace Werkint\Bundle\WebappBundle\Webapp;

/**
 * Goes through dependency tree, ataches package with dependencies
 */
class ScriptLoader
{

    /** @var ScriptHandler */
    protected $handler;
    /** @var string Cash directory */
    protected $resdir;
    /** @var string Scripts directory */
    protected $scripts;
    /** @var string Subenvironment (for example, language) */
    protected $appmode;

    public function __construct(
        $handler, $resdir, $appmode, $scripts
    ) {
        $this->handler = $handler;
        $this->appmode = $appmode;
        $this->resdir = $resdir;
        $this->scripts = $scripts;

        $this->packages = array();
        foreach (file($this->scripts . '/.packages') as $package) {
            $this->packages[$package] = $package;
        }
    }

    /**
     * Attaches package
     * @param $name
     * @throws \Exception
     */
    public function attach($name)
    {
        if (!isset($this->packages[$name])) {
            throw new \Exception('Package not found: ' . $name);
        }
        if ($this->handler->wasLoaded($name)) {
            // Package already was loaded
            return;
        }
        // Package data
        $path = $this->scripts . '/' . $name;
        $meta = parse_ini_file($path . '/.package.ini');

        // Dependencies
        foreach (explode(',', $meta['deps']) as $dep) {
            if (!($dep = trim($dep))) {
                continue;
            }
            $this->attach($dep);
        }

        // Scripts
        foreach (explode(',', $meta['files']) as $file) {
            if (!($file = trim($file))) {
                continue;
            }
            $this->attachFile($path . '/' . $file);
        }

        // Resources
        foreach (explode(',', $meta['res']) as $file) {
            if (!($file = trim($file))) {
                continue;
            }
            $this->loadRes($path, $file, $name);
        }

        // Loaded successfully
        $this->handler->setLoaded($name);
    }

    /**
     * Attaches one script
     * @param string $pathin
     * @param bool   $ignore_check
     * @throws \Exception
     * @return bool
     */
    public function attachFile($pathin, $ignore_check = false)
    {
        $path = realpath($pathin);
        if (!$path) {
            if (!$ignore_check) {
                throw new \Exception('Script not found: ' . $pathin);
            } else {
                return false;
            }
        }
        $this->handler->appendFile($path);

        // Other language (appmode)
        if ($this->appmode) {
            $path = realpath(preg_replace(
                '!^(.*)(\.[a-z0-9]+)$!', '$1.' . $this->appmode . '$2', $path
            ));
            if ($path) {
                $this->handler->appendFile($path);
            }
        }
        return true;
    }

    /**
     * Attaches related to template files
     * @param string $path
     */
    public function attachRelated($path)
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $name = $dir . '/_all';
        $this->attachFile($name . '.scss', true);
        $this->attachFile($name . '.js', true);
        $name = $dir . '/' . pathinfo($path, PATHINFO_FILENAME);
        $this->attachFile($name . '.scss', true);
        $this->attachFile($name . '.js', true);
    }

    // -- Static resources ---------------------------------------

    protected $staticRes = array();

    public function loadRes($path, $name, $bundle)
    {
        if (!isset($this->staticRes[$bundle])) {
            $this->staticRes[$bundle] = array();
        } else if (isset($this->staticRes[$bundle][$name])) {
            return;
        }
        $this->staticRes[$bundle][$name] = $path;
        $imgpath = $this->resdir . '/' . $bundle;
        if (!file_exists($imgpath)) {
            mkdir($imgpath);
        }
        $imgpath .= '/' . $name;
        if (file_exists($imgpath)) {
            return;
        }
        try {
            symlink($path, $imgpath);
        } catch (\Exception $e) {
            throw new \Exception(
                'Cannot symlink  "' . $path . '" to "' . $imgpath . '"'
            );
        }
    }

}
