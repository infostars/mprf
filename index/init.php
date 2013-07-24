<?php
namespace mpr;

$init_config_file = str_replace("phar://", '', dirname(__DIR__) . '/config.php');

if (!file_exists($init_config_file)) {
    print "Unable to load file {$init_config_file}!\n";
    exit(1);
}
require_once $init_config_file;
if (!defined('APP_ROOT')) {
    print "Error loading `APP_ROOT` from {$init_config_file}!\n";
    exit(1);
}
if (!isset($GLOBALS['APP_ENV'])) {
    print "Error loading `APP_ENV` from {$init_config_file}!\n";
    exit(1);
}
if (!isset($GLOBALS['PACKAGES_TYPE'])) {
    print "Error loading `PACKAGES_TYPE` from {$init_config_file}!\n";
    exit(1);
}
if (!isset($GLOBALS['PACKAGES_CACHE_EXPIRE'])) {
    print "Error loading `PACKAGES_CACHE_EXPIRE` from {$init_config_file}!\n";
    exit(1);
}
if (!isset($GLOBALS['PACKAGES_PATH']) || !is_array($GLOBALS['PACKAGES_PATH'])) {
    print "Error loading `PACKAGES_PATH` from {$init_config_file}!\n";
    exit(1);
}

spl_autoload_register(function ($package_name) {

    // normalize package name
    if (strpos($package_name, '\\') === 0) {
        $package_name = mb_substr($package_name, 1);
    }
    $package_name = str_replace("\\", "_", $package_name);

    static $server_uniq;
    if (!isset($server_uniq)) {
        $server_uniq = crc32(__DIR__);
    }

    switch (strtolower($GLOBALS['PACKAGES_TYPE'])) {
        case "phar":
            $packageArray = explode("_", $package_name);
            array_pop($packageArray);
            $packagePath = APP_ROOT . "/" . implode("/", $packageArray) . "/{$package_name}.phar";
            break;
        case "src":
        default:
            $packagePath = APP_ROOT;
            foreach ($GLOBALS['PACKAGES_PATH'] as $dev_packages_path) {
                $packagePath = $dev_packages_path . "/" . $package_name;
                $manifest_file = "{$packagePath}/manifest.mpr.json";
                if(class_exists('\mpr\config', true) && class_exists('\mpr\system\daemonization', true)) {
                    \mpr\system\daemonization::check();
                }
                if(class_exists('\mpr\debug\log', false)) {
                    \mpr\debug\log::put("Searching {$manifest_file}", "init");
                }
                if (file_exists($manifest_file)) {
                    $initFile = json_decode(file_get_contents($manifest_file), 1)['package']['init'];
                    $packagePath .= "/{$initFile}";
                    break;
                }
            }
    }
    if (file_exists($packagePath)) {
        require_once $packagePath;
    } else {
        \mpr\debug\log::put("ERROR LOADING {$package_name}", "init");
    }
});

\mpr\config::init();