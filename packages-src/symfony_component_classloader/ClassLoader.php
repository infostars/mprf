<?php
namespace symfony\component;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use \mpr\config;

/**
 * Symfony class loader loader :)))
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class ClassLoader
{
    /**
     * Initialize class loader and register autoloader
     */
    public function init()
    {
        $loader = new UniversalClassLoader('apc.classloader.');
        $loader->useIncludePath(true);
        $loader->registerNamespaces(config::getPackageConfig(__CLASS__)['register']);
        $loader->register();
    }
}