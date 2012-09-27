<?php
namespace mpr\helper;

use \mpr\cache;
use \mpr\config;
use \mpr\debug\log;

class locker
{
    /**
     * Generate cache key
     *
     * @param string $key
     * @return string
     */
    protected static function getCacheKey($key)
    {
        return "locked:{$key}";
    }

    /**
     * Lock method
     *
     * @param string $method
     * @return mixed
     */
    public static function lock($method)
    {
        $method = self::getCacheKey($method);
        return cache\factory::factory()->set("{$method}:lock", true, 10);
    }

    /**
     * Unlock method
     *
     * @param string $method
     * @return mixed
     */
    public static function unlock($method)
    {
        $method = self::getCacheKey($method);
        return cache\factory::factory()->set("{$method}:lock", false);
    }

    /**
     * Check is method locked
     *
     * @param string $method
     * @return bool
     */
    public static function locked($method)
    {
        $method = self::getCacheKey($method);
        return cache\factory::factory()->get("{$method}:lock") == true;
    }

    /**
     * Call some function or closure with params as strict locked function (ala semaphore)
     * Result may be cached
     * Checks for lock every microsecond
     *
     * @param callable $callable closure, function or method
     * @param mixed|null $input params
     * @param string $method_name name of locked function
     * @param bool $cached Cache call result
     * @param int $cache_expire Cache expire seconds
     * @return mixed
     */
    public static function strictLocked($callable, $input, $method_name, $cached = false, $cache_expire = 5)
    {
        $cache_key = self::getCacheKey($method_name);
        $data = $cached ? cache\factory::factory()->get($cache_key) : null;
        if(!$cached || $cached && $data == null) {
            while(self::locked($method_name));
            self::lock($method_name);
            $data = call_user_func($callable, $input);
            if($cached) {
                cache\factory::factory()->set($cache_key, $data, $cache_expire);
            }
            self::unlock($method_name);
        }
        return $data;
    }

    /**
     * Call some function or closure with params as locked function (ala semaphore)
     * Result will be cached
     * Checks for lock every 100 microseconds
     *
     * @param callable $callable closure, function or method
     * @param mixed|null $input params
     * @param string $method_name name of locked function
     * @return mixed
     */
    public static function cachedLockedFunction($callable, $input, $method_name, $cache_expire = 5)
    {
        $cache_key = self::getCacheKey($method_name);
        $data = cache\factory::factory()->get($cache_key);
        if($data == null) {
            while(self::locked($method_name)) {
                usleep(100);
            }
            self::lock($method_name);

            $data = call_user_func($callable, $input);
            cache\factory::factory()->set($cache_key, $data, $cache_expire);

            self::unlock($method_name);
        }
        return $data;
    }

    /**
     * Call some function or closure with params as locked function (ala semaphore)
     *
     * @param callable $callable closure, function or method
     * @param mixed|null $input params
     * @param string $method_name name of locked function
     * @return mixed
     */
    public static function lockedFunction($callable, $input, $method_name)
    {
        while(self::locked($method_name)) {
            usleep(100);
        }
        self::lock($method_name);

        $data = call_user_func($callable, $input);

        self::unlock($method_name);

        return $data;
    }
}