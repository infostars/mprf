<?php
namespace mpr;

use \mpr\debug\log;
use \mpr\config;
use \mpr\interfaces\cache as cache_interface;

class cache
implements cache_interface
{

    protected static $instances = array();

    /**
     * @var cache_interface
     */
    private $backend;

    /**
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
        log::put("Cache factory {$driver_packageName}", config::getPackageName(__CLASS__));
        if(!isset(self::$instances[$driver_packageName])) {
            self::$instances[$driver_packageName] = new self($driver_packageName);
        }
        return self::$instances[$driver_packageName];
    }

    public function __construct($driver_packageName)
    {
        $driver = config::getClassName($driver_packageName);
        $this->backend = new $driver();
    }

    public function set($key, $value, $expire = 3600)
    {
        log::put("Set {$key}", config::getPackageName(__CLASS__));
        return $this->backend->set($key, $value, $expire);
    }

    public function get($key)
    {
        log::put("Get {$key}", config::getPackageName(__CLASS__));
        return $this->backend->get($key);
    }

    public function exists($key)
    {
        $exists = $this->backend->exists($key);
        log::put("Exists {$key}: " . ($exists ? 'true' : 'false'), config::getPackageName(__CLASS__));
        return $exists;
    }

    public function remove($key)
    {
        log::put("Remove {$key}", config::getPackageName(__CLASS__));
        return $this->backend->remove($key);
    }

    public function clear()
    {
        log::put("WARNING! CLEARING ALL CACHE!", config::getPackageName(__CLASS__));
        return $this->backend->clear();
    }

    public function enableAutoCommit()
    {
        log::put("Transaction - autoCommit enabled", config::getPackageName(__CLASS__));
        return $this->backend->enableAutoCommit();
    }

    public function disableAutoCommit()
    {
        log::put("Transaction - autoCommit disabled", config::getPackageName(__CLASS__));
        return $this->backend->disableAutoCommit();
    }

    public function commit()
    {
        log::put("Transaction - Commiting...", config::getPackageName(__CLASS__));
        return $this->backend->commit();
    }
}