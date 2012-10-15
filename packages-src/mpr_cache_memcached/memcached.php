<?php
namespace mpr\cache;

use \mpr\config;

/**
 * Memcached driver wrapper for mpr_cache package
 *
 * @author GreeveX <greevex@gmail.com>
 */
class memcached
extends \mpr\cache
implements \mpr\cache\cache_interface
{
    
    public function commit()
    {
        throw new \Exception("Transactions not implemented in package " . config::getPackageName(__CLASS__));
    }

    public function enableAutoCommit()
    {
        throw new \Exception("Transactions not implemented in package " . config::getPackageName(__CLASS__));
    }

    public function disableAutoCommit()
    {
        throw new \Exception("Transactions not implemented in package " . config::getPackageName(__CLASS__));
    }
    
    public function __construct()
    {
        $config = config::getPackageConfig(__CLASS__);
        $this->memcached = new \Memcached();
        foreach($config['servers'] as $server) {
            $this->memcached->addServer($server['host'], $server['port']);
        }
    }
    
    public function set($key, $value, $expire = 60)
    {
        $this->memcached->set($key, $value, $expire);
    }
    
    public function get($key)
    {
        return $this->memcached->get($key);
    }
    
    public function exists($key)
    {
        $data = $this->memcached->get($key);
        return !($data === false || $data === null);
    }
    
    public function remove($key)
    {
        return $this->memcached->delete($key);
    }
    
    public function clear()
    {
        return $this->memcached->flush();
    }
}