<?php
namespace mpr\pattern;

/**
 * Singleton pattern class
 *
 * @version 1.0
 * @author greevex <greevex@gmail.com>
 */
abstract class singleton
{
    private static $instance;

    /**
     * Get the one and only instance of this object
     *
     * @static
     * @return \mpr\toolkit
     */
    final public static function getInstance()
    {
        if(self::$instance == null) {
            $class = get_called_class();
            self::$instance = new $class();
        }
        return self::$instance;
    }
}