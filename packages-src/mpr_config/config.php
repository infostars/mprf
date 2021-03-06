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
    /**
     * Filename to detect mpr root directory
     */
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
        if(mb_strpos($class, '\\') === 0) {
            $class = mb_substr($class, 1);
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
     * Load user config and init configuration
     *
     * @static
     *
     */
    public static function init()
    {
        static $init_complete = false;
        if(!$init_complete) {
            //echo "Initializing config...", PHP_EOL;
            $localConfigFilePath = self::locateLocalConfigFile();
            //echo "Checking local config {$localConfigFilePath} ...", PHP_EOL;
            if(file_exists($localConfigFilePath)) {
                //echo "Local config {$localConfigFilePath} found, loading...", PHP_EOL;
                require_once $localConfigFilePath;
            }
            self::loadAdditionalConfigs();
            $init_complete = true;
        }
    }

    /**
     * Locate config file
     *
     * @static
     * @return bool
     */
    protected static function locateLocalConfigFile()
    {
        static $localConfigFilePath;
        if($localConfigFilePath == null) {
            $mprRootPath = self::findMprRoot();
            if(!$mprRootPath) {
                return false;
            }
            $rioEnv = toolkit::getInstance()->getInput()->getString('rio-env');
            if ($rioEnv !== null) {
                $localConfigFilePath = "{$mprRootPath}/config.{$rioEnv}.php";
                if (!file_exists($localConfigFilePath)) {
                    error_log("ERROR: Couldn't find {$localConfigFilePath}!");
                    exit(1);
                }
            } else {
                $localConfigFilePath = "{$mprRootPath}/config.local.php";
            }
        }
        return $localConfigFilePath;
    }

    /**
     * Load additional configs
     *
     * @return bool
     */
    protected static function loadAdditionalConfigs()
    {
        $mprRootPath = self::findMprRoot();
        if(!$mprRootPath) {
            return false;
        }
        $additionalConfigsPath = "{$mprRootPath}/config.d/";
        //echo "Loading configs in {$additionalConfigsPath}...", PHP_EOL;
        self::loadConfigsByPath($additionalConfigsPath);
        $envConfigsPath = "{$mprRootPath}/config.{$GLOBALS['APP_ENV']}.d/";
        //echo "Loading configs in {$envConfigsPath}...", PHP_EOL;
        self::loadConfigsByPath($envConfigsPath);
        return true;
    }

    /**
     * Load additional configs in specific path
     *
     * @param string $additionalConfigsPath
     * @return bool
     */
    protected static function loadConfigsByPath($additionalConfigsPath)
    {
        if(!file_exists($additionalConfigsPath)) {
            //echo "Path doesn't exists {$additionalConfigsPath}...", PHP_EOL;
            return false;
        }
        foreach(scandir($additionalConfigsPath) as $file) {
            if(mb_substr($file, -4, 4) != '.php') {
                //echo "Skipping file {$file} ...", PHP_EOL;
                continue;
            }
            $pathToLoad = "{$additionalConfigsPath}/{$file}";
            //echo "Loading file {$pathToLoad} ...", PHP_EOL;
            require_once $pathToLoad;
        }
        return true;
    }

    /**
     * Save config to files
     */
    protected static function save()
    {
        usleep(50000);
        //@todo implement this
    }
}
