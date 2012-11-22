<?php
namespace mpr;

use \mpr\debug\log;
use \mpr\config;
use \mpr\interfaces;

/**
 * Cache builder
 *
 * Constructs using driver package name
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class cache
implements interfaces\cache
{

    /**
     * Cache driver instances
     *
     * @var array
     */
    protected static $instances = array();

    /**
     * Cache driver backend
     *
     * @see interfaces\cache
     * @var cache
     */
    private $backend;

    /**
     * Factory cache by driver package name
     *
     * @static
     * @param null $driver_packageName
     * @see \mpr\interfaces\cache
     * @return self
     */
    public static function factory($driver_packageName = null)
    {
        if($driver_packageName == null) {
            $driver_packageName = config::getPackageConfig(__CLASS__)['default'];
        }
        if(!isset(self::$instances[$driver_packageName])) {
            self::$instances[$driver_packageName] = new self($driver_packageName);
        }
        return self::$instances[$driver_packageName];
    }

    /**
     * Build cache by driver's package name
     *
     * @param $driver_packageName
     */
    public function __construct($driver_packageName)
    {
        $driver = config::getClassName($driver_packageName);
        $this->backend = new $driver();
    }

    /**
     * Set value by key
     *
     * @param $key
     * @param $value
     * @param int $expire
     * @return mixed
     */
    public function set($key, $value, $expire = 3600)
    {
        return $this->backend->set($key, $value, $expire);
    }

    /**
     * Get value by key
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->backend->get($key);
    }

    /**
     * Check is key exists
     *
     * @param $key
     * @return mixed
     */
    public function exists($key)
    {
        $exists = $this->backend->exists($key);
        return $exists;
    }

    /**
     * Remove record from cache by key
     *
     * @param $key
     * @return mixed
     */
    public function remove($key)
    {
        return $this->backend->remove($key);
    }

    /**
     * WARNING! Clear all cache
     *
     * @return mixed
     */
    public function clear()
    {
        log::put("WARNING! CLEARING ALL CACHE!", config::getPackageName(__CLASS__));
        return $this->backend->clear();
    }

    /**
     * Enable auto commit changes
     *
     * @return mixed
     */
    public function enableAutoCommit()
    {
        log::put("Transaction - autoCommit enabled", config::getPackageName(__CLASS__));
        return $this->backend->enableAutoCommit();
    }

    /**
     * Disable auto commit changes
     *
     * @return mixed
     */
    public function disableAutoCommit()
    {
        log::put("Transaction - autoCommit disabled", config::getPackageName(__CLASS__));
        return $this->backend->disableAutoCommit();
    }

    /**
     * Commit changes
     *
     * @return mixed
     */
    public function commit()
    {
        log::put("Transaction - Commiting...", config::getPackageName(__CLASS__));
        return $this->backend->commit();
    }
}