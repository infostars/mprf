<?php
namespace mpr\cache;

use \mpr\config;
use \mpr\interfaces\cache as cache_interface;

/**
 * Memcached driver wrapper for mpr_cache package
 *
 * @author GreeveX <greevex@gmail.com>
 */
class memcached
extends \mpr\cache
implements cache_interface
{

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
     */
    public function __construct()
    {
        $config = config::getPackageConfig(__CLASS__);
        $this->memcached = new \Memcached();
        foreach($config['servers'] as $server) {
            $this->memcached->addServer($server['host'], $server['port']);
        }
    }

    /**
     * Set value by key
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return bool|mixed
     */
    public function set($key, $value, $expire = 60)
    {
        return $this->memcached->set($key, $value, $expire);
    }

    /**
     * Get value by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->memcached->get($key);
    }

    /**
     * Check is key exists
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        $data = $this->memcached->get($key);
        return !($data === false || $data === null);
    }

    /**
     * Remove record from cache by key
     *
     * @param string $key
     * @return bool
     */
    public function remove($key)
    {
        return $this->memcached->delete($key);
    }

    /**
     * WARNING! Clear all cache!
     *
     * @return bool
     */
    public function clear()
    {
        return $this->memcached->flush();
    }
}