<?php
namespace mpr;

$init_config_file = str_replace("phar://", '', dirname(__DIR__) . '/init.config.php');

if(!file_exists($init_config_file)) {
    print "Unable to load file {$init_config_file}!\n";
    exit(1);
}
require_once $init_config_file;
if(!defined('APP_ROOT')) {
    print "Constant `APP_ROOT` is not defined! Invalid `{$init_config_file}` file!\n";
    exit(1);
}
if(!defined('APP_ENV_PROD')) {
    print "Constant `APP_ENV_PROD` is not defined! Invalid `{$init_config_file}` file!\n";
    exit(1);
}

/**
 * Определение с какими пакетами работаем.
 * Либо с phar-арихивами, либо с исходными кодами.
 * prod = phar
 * dev = src
 */
spl_autoload_register(function ($package) {
    static $loading_cache;
    static $cache;

    // env
    $env = APP_ENV_PROD === true ? 'prod' : 'dev';

    // normalize package name
    if(strpos($package, '\\') === 0) {
        $package = mb_substr($package, 1);
    }
    $package = str_replace("\\", "_", $package);

    $server_uniq = crc32(__DIR__);

    // build cache key
    $cache_key = "init:{$env}:{$package}:{$server_uniq}";

    // load cache
    if(!$loading_cache && $cache == null) {
        $loading_cache = 1;
        $cache = cache::factory();
        $loading_cache = 0;
    }

    // get path from cache or search it
    if(!$loading_cache && isset($cache) && $cache->exists($cache_key)) {
        $packagePath = $cache->get($cache_key);
    } else {
        switch(strtolower($env)) {
            case "prod":
                $packageArray = explode("_", $package);
                array_pop($packageArray);
                $packagePath = APP_ROOT . "/" . implode("/", $packageArray) . "/{$package}.phar";
                break;
            case "dev":
            default:
                error_reporting(E_ALL);
                ini_set('display_errors', 1);
                $packagePath = APP_ROOT;
                if(!isset($GLOBALS['dev_packages_path']) || !is_array($GLOBALS['dev_packages_path'])) {
                    print "Error loading `dev_packages_path` from init.config.php!\n";
                }
                foreach($GLOBALS['dev_packages_path'] as $dev_packages_path) {
                    $packagePath = $dev_packages_path . "/" . strtolower($package);
                    $manifest_file = "{$packagePath}/manifest.mpr.json";
                    if(file_exists($manifest_file)) {
                        $initFile = json_decode(file_get_contents($manifest_file), 1)['package']['init'];
                        $packagePath .= "/{$initFile}";
                        break;
                    }
                }
        }
        if(isset($cache)) {
            $cache->set($cache_key, $packagePath, 3600);
        }
    }
    if(file_exists($packagePath)) {
        require_once $packagePath;
        \mpr\debug\log::put("Loaded {$package}", "init");
    } else {
        \mpr\debug\log::put("Skipped {$package}", "init");
    }
});