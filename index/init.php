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
    $env = APP_ENV_PROD === true ? 'prod' : 'dev';
    if(strpos($package, '\\') === 0) {
        $package = mb_substr($package, 1);
    }
    $package = str_replace("\\", "_", $package);
    switch(strtolower($env)) {
        case "prod":
            $packageArray = explode("_", $package);
            array_pop($packageArray);
            $packagePath = APP_ROOT . "/" . implode("/", $packageArray) . "/{$package}.phar";
            break;
        case "dev":
        default:
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
    if(file_exists($packagePath)) {
        require_once $packagePath;
        \mpr\debug\log::put("Loaded {$package} ({$packagePath})", "init");
    } else {
        \mpr\debug\log::put("Skipped {$package} ({$packagePath})", "init");
    }
});

__halt_compiler();