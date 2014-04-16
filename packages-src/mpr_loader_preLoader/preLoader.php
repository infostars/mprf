<?php
namespace mpr\loader;

use \mpr\debug\log;
use \mpr\config;

/**
 * PreLoader class
 *
 * Loading classes before usage
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class preLoader
{

    /**
     * Classes to load
     *
     * @var array
     */
    private $classes = [];

    /**
     * Add package name to load
     *
     * @param string $package package name
     * @return bool load result
     */
    public function addPackage($package)
    {
        $class = config::getClassName($package);
        return $this->addClass($class);
    }

    /**
     * Get list of classes selected for preloading
     *
     * @return array
     */
    public function getPreloadList()
    {
        return array_keys($this->classes);
    }

    /**
     * Add class path to load
     *
     * @param $class
     * @return bool
     */
    public function addClass($class)
    {
        if(!isset($this->classes[$class])) {
            MPR_DEBUG && log::put("Add to preload list class `{$class}`", config::getPackageName(__CLASS__));
            $this->classes[$class] = 1;
            return true;
        }
        return false;
    }

    /**
     * Load selected packages and classes
     */
    public function load()
    {
        MPR_DEBUG && log::put("Starting preload", config::getPackageName(__CLASS__));
        foreach($this->classes as $class => $v) {
            class_exists($class);
            unset($v, $this->classes[$class]);
        }
        MPR_DEBUG && log::put("Preloading success!", config::getPackageName(__CLASS__));
    }

}