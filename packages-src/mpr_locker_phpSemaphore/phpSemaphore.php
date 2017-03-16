<?php
namespace mpr\locker;

use \mpr\config;
use \mpr\debug\log;
use \mpr\interfaces;

/**
 * Locker package
 *
 * PHP Semaphore implementation
 *
 * @author Diomin Piotr <demin@infostars.ru>
 */
class phpSemaphore
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
        static $keys = [];
        if(!isset($keys[$key])) {
            $keys[$key] = sem_get(crc32($key), 1, 0666, 10);
        }
        return $keys[$key];
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
        sem_acquire($this->getLockKey($method));
    }

    /**
     * Lock more method
     *
     * @static
     * @param string $method
     * @param int $expire
     * @return bool
     */
    public function lockMore($method, $expire = 10)
    {
        //until php5.6.1
        return false;
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
        sem_release($this->getLockKey($method));
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
        return false;
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
        throw new \Exception("Not implemented for this driver!");
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
        throw new \Exception("Not implemented for this driver!");
    }
}