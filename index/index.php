<?php
namespace mpr;

$DIR = strpos(__DIR__, 'phar') === 0 ? realpath(dirname(__DIR__)) : __DIR__;

require_once __DIR__ . '/init.php';

$arguments = (new \mpr\io\input())->export();
$app_path = isset($arguments[0]) ? $arguments[0] : '';
$output = new \mpr\io\output();

if(!empty($app_path)) {
    $app_path = strpos($app_path, '/') === 0 ? $app_path : "{$DIR}/{$app_path}";

    \mpr\loader\fileLoader::load($app_path);

    $app_name = basename($app_path, '.php');

    $classname = "\\mpr\\app\\{$app_name}";
    if(!class_exists($classname, false)) {
        $output->writeLn("File {$app_path} does not contains class {$classname}!");
        return false;
    }
    $app = new $classname();
    $app->run();
}