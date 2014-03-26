<?php

namespace mpr\pattern;

/**
 * Abstract factory pattern trait
 *
 * @version 1.0
 * @author greevex <greevex@gmail.com>
 */
trait abstractFactory
{
    /**
     * @static
     * @var array classes instances by configuration name
     */
    private static $instances;

    /**
     * @static
     *
     * @param string $configName
     *
     * @return static
     */
    final public static function factory($configName = 'default')
    {
        if(!isset(self::$instances[$configName])) {
            self::$instances[$configName] = new self($configName);
        }
        return self::$instances[$configName];
    }
}