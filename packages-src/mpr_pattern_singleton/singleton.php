<?php

namespace mpr\pattern;

/**
 * Singleton pattern trait
 *
 * @version 1.1
 * @author greevex <greevex@gmail.com>
 * @author Borovikov Maxim <maxim.mahi@gmail.com>
 */
trait singleton
{
    /**
     * Class instance
     *
     * @static
     * @var object class instance
     */
    private static $instance;

    /**
     * Get the one and only instance of this object
     *
     * @static
     * @return static
     */
    final public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}