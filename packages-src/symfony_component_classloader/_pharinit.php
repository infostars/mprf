<?php
namespace symfony\component;

require_once __DIR__ . '/ClassLoader/UniversalClassLoader.php';
require_once __DIR__ . '/ClassLoader/ApcUniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;
use \mpr\config;

class ClassLoader
{
    public function init()
    {
        $loader = new UniversalClassLoader('apc.classloader.');
        $loader->useIncludePath(true);
        $loader->registerNamespaces(config::getPackageConfig(__CLASS__)['register']);
        $loader->register();
    }
}

$loader = new ClassLoader();
$loader->init();

__halt_compiler();