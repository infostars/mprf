<?php
namespace mpr\system;

use \mpr\debug\log;
use \mpr\config;

/**
 * Description of core
 *
 * @author GreeveX <greevex@gmail.com>
 */
class core
{
    private static $loaded = false;

    public static function isLoaded()
    {
        return self::$loaded;
    }

    public static function run()
    {
        if(self::$loaded) {
            throw new \Exception("Attempt to load core for two times!");
        }

        log::put("Core loaded...", config::getPackageName(__CLASS__), 5);
        self::$loaded = true;
    }
}