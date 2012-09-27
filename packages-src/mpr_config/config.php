<?php
namespace mpr;

use \mpr\debug\log;

/**
 * Global config
 *
 * @author greevex
 * @date: 9/19/12 5:37 PM
 */
class config
{
    const MPR_ROOT_FILENAME = ".mprroot";

    /**
     * Packages config array
     *
     * @var array
     */
    public static $package = [];

    /**
     * Common config array
     *
     * @var array
     */
    public static $common = [];

    /**
     * Find root path for local mpr repository
     *
     * @static
     * @return bool|string
     */
    protected static function findMprRoot()
    {
        static $path_to_mpr;
        if($path_to_mpr == null) {
            $current_path = __DIR__;
            $path_to_mpr = false;
            $found = false;
            while($current_path != '/') {
                $found = in_array(self::MPR_ROOT_FILENAME, scandir($current_path));
                if($found) {
                    $path_to_mpr = $current_path;
                }
                $current_path = realpath($current_path . "/..");
            }
            if(!$path_to_mpr) {
                return false;
            }
        }
        return $path_to_mpr;
    }

    /**
     * Get package name by class name
     *
     * @static
     * @param string $class
     * @return string Package name
     */
    public static function getPackageName($class)
    {
        if(strpos($class, '\\') === 0) {
            $class = substr($class, 1);
        }
        return str_replace('\\', '_', $class);
    }

    /**
     * Get full class name by package name
     *
     * @static
     * @param string $packageName
     * @return string Class name with namespace
     */
    public static function getClassName($packageName)
    {
        return "\\" . str_replace("_", "\\", $packageName);
    }

    /**
     * Get package config by class name
     *
     * @static
     * @param $class
     * @return mixed
     * @throws \Exception
     */
    public static function getPackageConfig($class)
    {
        $packageName = self::getPackageName($class);
        if(!isset(self::$package[$packageName])) {
            throw new \Exception("Config file doesn't contains section `{$packageName}`! Fix it, read fucking README of package `{$packageName}` first!");
        }
        return self::$package[$packageName];
    }

    /**
     * Locate config file
     *
     * @static
     * @return bool
     */
    protected static function locateConfigFile()
    {
        static $configFilePath;
        if($configFilePath == null) {
            $mprRootPath = self::findMprRoot();
            if(!$mprRootPath) {
                return false;
            }
            $configFilePath = "{$mprRootPath}/config.php";
        }
        return $configFilePath;
    }

    /**
     * Load user config and init configuration
     *
     * @static
     *
     */
    public static function init()
    {
        $configFilePath = self::locateConfigFile();
        if(!file_exists($configFilePath)) {
            throw new \Exception("Config file not found in {$configFilePath}!");
        }
        require_once $configFilePath;
        log::put("Loading config file {$configFilePath}", self::getPackageName(__CLASS__));
    }
}
