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
class file
implements interfaces\locker
{

    /**
     * Generate lock key
     *
     * @static
     * @param string $key
     * @return string
     */
    public function getLockKey($key)
    {
        static $resources = [];
        if(!isset($resources[$key])) {
            $resources[$key] = fopen(config::getPackageConfig(__CLASS__)['path'] . "{$key}.sem", 'r+');
        }
        return $resources[$key];
    }

    /**
     * Lock method
     *
     * @static
     * @param string $method
     * @param int    $expire
     * @return mixed
     */
    public function lock($method, $expire = 10)
    {
        flock($this->getLockKey($method), LOCK_EX);
    }

    /**
     * Unlock method
     *
     * @static
     * @param string $method
     * @return mixed
     */
    public function unlock($method)
    {
        flock($this->getLockKey($method), LOCK_UN);
    }

    /**
     * Check is method locked
     *
     * @static
     * @param string $method
     * @return bool
     */
    public function locked($method)
    {
        // TODO: Implement locked() method.
    }

    /**
     * Store data by lock key
     *
     * @static
     * @param string $lock_key
     * @param mixed  $data
     * @param int    $lock_expire
     */
    public function storeLockedData($lock_key, $data, $lock_expire = 10)
    {
        // TODO: Implement storeLockedData() method.
    }

    /**
     * Get data by lock key
     *
     * @static
     * @param string $lock_key
     * @return mixed
     */
    public function getLockedData($lock_key)
    {
        // TODO: Implement getLockedData() method.
    }
}