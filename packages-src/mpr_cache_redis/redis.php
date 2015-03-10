<?php
namespace mpr\cache;

use \mpr\config;
use \mpr\interfaces;

/**
 * Redis driver wrapper for mpr_cache package
 *
 * @author GreeveX <greevex@gmail.com>
 */
class redis
extends \mpr\cache
implements interfaces\cache
{

    /**
     * Instance of native Redis driver
     *
     * @var \Redis
     */
    protected $instance;

    /**
     * Commit changes
     *
     * @not implemented for this driver!
     * @throws \Exception
     */
    public function commit()
    {
        throw new \Exception("Transactions not implemented in package " . config::getPackageName(__CLASS__));
    }

    /**
     * Enable auto commit changes
     *
     * @not implemented for this driver!
     * @throws \Exception
     */
    public function enableAutoCommit()
    {
        throw new \Exception("Transactions not implemented in package " . config::getPackageName(__CLASS__));
    }

    /**
     * Disable auto commit changes
     *
     * @not implemented for this driver!
     * @throws \Exception
     */
    public function disableAutoCommit()
    {
        throw new \Exception("Transactions not implemented in package " . config::getPackageName(__CLASS__));
    }

    /**
     * Initialize driver and connect to host
     *
     * @param string $configSection
     */
    public function __construct($configSection = 'default')
    {
        $config = config::getPackageConfig(__CLASS__)[$configSection];
        $this->instance = new \Redis();
        $this->instance->pconnect($config['server']['host'], $config['server']['port'], $config['server']['timeout']);
        $this->instance->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        $this->instance->setOption(\Redis::OPT_PREFIX, $config['server']['prefix']);
    }

    /**
     * Set value by key
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire
     *
     * @return mixed
     */
    public function set($key, $value, $expire = 60)
    {
        return $this->instance->setex($key, $expire, $value);
    }

    /**
     * Add value by key
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire
     *
     * @return bool|mixed
     */
    public function add($key, $value, $expire = 60)
    {
        if($this->exists($key)) {
            return false;
        }
        return $this->instance->setex($key, $expire, $value);
    }

    /**
     * Get value by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->instance->get($key);
    }

    /**
     * Check is key exists
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return $this->instance->exists($key);
    }

    /**
     * Remove record from cache by key
     *
     * @param string $key
     * @return bool
     */
    public function remove($key)
    {
        return $this->instance->delete($key);
    }

    /**
     * WARNING! Clear all cache!
     *
     * @return bool
     */
    public function clear()
    {
        return $this->instance->flushDB();
    }

    /**
     * Return last error
     *
     * @return mixed
     */
    public function getResultCode()
    {
        return $this->instance->getLastError();
    }

    /**
     * Cache driver backend
     *
     * @return mixed
     */
    public function getBackend()
    {
        return $this->instance;
    }
}