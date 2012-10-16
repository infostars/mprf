<?php
namespace mpr;

use \mpr\debug\log;
use \mpr\config;
use \mpr\cache\cache_interface;

/**
 * Cache abstract factory class
 *
 * It uses drivers like mpr_cache_file or mpr_cache_memcached
 *
 * @author GreeveX <greevex@gmail.com>
 */
class cache
implements cache_interface
{

    /**
     * Driver instances by config names
     *
     * @var array Instances
     * @static
     */
    protected static $instances = array();

    /**
     * @var self
     * @see \mpr\cache\cache_interface
     */
    private $backend;

    /**
     * @static
     * @param string $driver_packageName
     * @see \mpr\cache\cache_interface
     * @return self
     */
    public static function factory($driver_packageName = 'default')
    {
        if($driver_packageName == 'default') {
            $driver_packageName = config::getPackageConfig(__CLASS__)[$driver_packageName];
        }
        log::put("Cache factory {$driver_packageName}", config::getPackageName(__CLASS__));
        if(!isset(self::$instances[$driver_packageName])) {
            self::$instances[$driver_packageName] = new self($driver_packageName);
        }
        return self::$instances[$driver_packageName];
    }

    /**
     * Build new cache object using driver cache config name
     *
     * @param string $driver_packageName Config name of driver to use for
     */
    public function __construct($driver_packageName)
    {
        $driver = config::getClassName($driver_packageName);
        $this->backend = new $driver();
    }

    /**
     * Set value by key and expire time
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return mixed Result
     */
    public function set($key, $value, $expire = 3600)
    {
        log::put("Set {$key}", config::getPackageName(__CLASS__));
        return $this->backend->set($key, $value, $expire);
    }

    /**
     * Get value by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        log::put("Get {$key}", config::getPackageName(__CLASS__));
        return $this->backend->get($key);
    }

    /**
     * Checks is key exists
     *
     * @param string $key
     * @return mixed
     */
    public function exists($key)
    {
        $exists = $this->backend->exists($key);
        log::put("Exists {$key}: " . ($exists ? 'true' : 'false'), config::getPackageName(__CLASS__));
        return $exists;
    }

    /**
     * Remove record by key
     *
     * @param string $key
     * @return mixed
     */
    public function remove($key)
    {
        log::put("Remove {$key}", config::getPackageName(__CLASS__));
        return $this->backend->remove($key);
    }

    /**
     * Flush all
     *
     * @return mixed
     */
    public function clear()
    {
        log::put("WARNING! CLEARING ALL CACHE!", config::getPackageName(__CLASS__));
        return $this->backend->clear();
    }

    /**
     * Enable auto-commit (no-transaction mode)
     *
     * @return mixed result
     */
    public function enableAutoCommit()
    {
        log::put("Transaction - autoCommit enabled", config::getPackageName(__CLASS__));
        return $this->backend->enableAutoCommit();
    }

    /**
     * Disable auto-commit (transaction mode)
     *
     * @return mixed result
     */
    public function disableAutoCommit()
    {
        log::put("Transaction - autoCommit disabled", config::getPackageName(__CLASS__));
        return $this->backend->disableAutoCommit();
    }

    /**
     * Commit changes (end transaction)
     *
     * @return mixed result
     */
    public function commit()
    {
        log::put("Transaction - Commiting...", config::getPackageName(__CLASS__));
        return $this->backend->commit();
    }
}