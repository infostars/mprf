<?php
namespace mpr;

use \mpr\config;
use \mpr\debug\log;
use \mpr\interfaces;

/**
 * Locker package
 *
 * Semaphore implementation using driver package name
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 * @author Diomin Piotr <demin@infostars.ru>
 */
class locker
implements interfaces\locker
{
    /**
     * Locker driver instances
     *
     * @static
     * @var array
     */
    protected static $instances = array();

    /**
     * Locker driver backend
     *
     * @see interfaces\locker
     * @var locker
     */
    private $backend;

    /**
     * Factory locker by driver package name
     *
     * @static
     * @param null $configSection
     * @see \mpr\interfaces\locker
     * @return self
     */
    public static function factory($configSection = 'default')
    {
        log::put("Locker factory {$configSection}", config::getPackageName(__CLASS__));
        if(!isset(self::$instances[$configSection])) {
            self::$instances[$configSection] = new self($configSection);
        }
        return self::$instances[$configSection];
    }

    /**
     * Build locker by driver's package name
     *
     * @param $configSection
     */
    public function __construct($configSection)
    {
        $currentConfig = config::getPackageConfig(__CLASS__)[$configSection];
        $driver = config::getClassName($currentConfig['driver']);
        $this->backend = new $driver($currentConfig['backend_section']);
    }

    /**
     * Generate lock key
     *
     * @param string $key
     * @return string
     */
    public function getLockKey($key)
    {
        log::put("Get lock key {$key}", config::getPackageName(__CLASS__));
        return $this->backend->getLockKey($key);
    }

    /**
     * Lock method
     *
     * @param string $method
     * @param int $expire
     * @return mixed
     */
    public function lock($method, $expire = 10)
    {
        log::put("Lock method {$method}", config::getPackageName(__CLASS__));
        return $this->backend->lock($method);
    }

    /**
     * Unlock method
     *
     * @param string $method
     * @return mixed
     */
    public function unlock($method)
    {
        log::put("Unlock method {$method}", config::getPackageName(__CLASS__));
        return $this->backend->unlock($method);
    }

    /**
     * Check is method locked
     *
     * @param string $method
     * @return bool
     */
    public function locked($method)
    {
        log::put("Is locked method {$method}", config::getPackageName(__CLASS__));
        return $this->backend->locked($method);
    }

    /**
     * Get data by lock key
     *
     * @param string $lock_key
     * @return mixed
     */
    public function getLockedData($lock_key)
    {
        log::put("Get data by key {$lock_key}", config::getPackageName(__CLASS__));
        return $this->backend->getLockedData($lock_key);
    }

    /**
     * Store data by lock key
     *
     * @param string $lock_key
     * @param mixed $data
     * @param int $lock_expire
     */
    public function storeLockedData($lock_key, $data, $lock_expire = 10)
    {
        log::put("Store data by key {$lock_key}", config::getPackageName(__CLASS__));
        $this->backend->storeLockedData($lock_key, $data, $lock_expire);
    }

    /**
     * Call some function or closure with params as strict locked function (ala semaphore)
     * Result may be cached
     * Checks for lock every microsecond
     *
     * @param callable $callable closure, function or method
     * @param mixed|null $input params
     * @param string $method_name name of locked function
     * @param bool $store_to_cache cache call result
     * @param int $lock_expire Lock expire seconds
     * @return mixed
     */
    public function strictLocked($callable, &$input, $method_name, $store_to_cache = false, $lock_expire = 5)
    {
        $lock_key = self::getLockKey($method_name);
        $data = $store_to_cache ? self::getLockedData($lock_key) : null;
        if(!$store_to_cache || $store_to_cache && $data == null) {
            while(self::locked($method_name));
            self::lock($method_name);
            $data = call_user_func($callable, $input);
            if($store_to_cache) {
                self::storeLockedData($lock_key, $data, $lock_expire);
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
    public function cachedLockedFunction($callable, &$input, $method_name, $lock_expire = 5)
    {
        $lock_key = self::getLockKey($method_name);
        $data = self::getLockedData($lock_key);
        if($data == null) {
            while(self::locked($method_name)) {
                usleep(100);
            }
            self::lock($method_name);

            $data = call_user_func($callable, $input);
            self::storeLockedData($lock_key, $data, $lock_expire);

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
    public function lockedFunction($callable, &$input, $method_name)
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