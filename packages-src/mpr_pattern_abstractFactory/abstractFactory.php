<?php
namespace mpr\pattern;

/**
 * Singleton pattern class
 *
 * @version 1.0
 * @author greevex <greevex@gmail.com>
 */
abstract class abstractFactory
{
    private static $instances;
    /**
     * @static
     */
    final public static function factory($configName = 'default')
    {
        if(!isset(self::$instances[$configName])) {
            $class = get_called_class();
            self::$instances[$configName] = new $class($configName);
        }
        return self::$instances[$configName];
    }
}