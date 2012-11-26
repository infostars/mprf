<?php
namespace mpr\locker;

use \mpr\config;
use \mpr\debug\log;
use \mpr\interfaces;

/**
 * Locker package
 *
 * Semaphore implementation using cache
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 * @author Diomin Piotr <demin@infostars.ru>
 */
class cache
extends \mpr\cache
implements interfaces\locker
{

    /**
     * Generate lock key
     *
     * @param string $key
     * @return string
     */
    public function getLockKey($key)
    {
        return "lck:{$key}";
    }

    /**
     * Lock method
     *
     * @param string $method
     * @return mixed
     */
    public function lock($method, $expire = 10)
    {
        $method = self::getLockKey($method);
        return $this->set("{$method}:l", true, $expire);
    }

    /**
     * Unlock method
     *
     * @param string $method
     * @return mixed
     */
    public function unlock($method)
    {
        $method = self::getLockKey($method);
        return $this->set("{$method}:l", false);
    }

    /**
     * Check is method locked
     *
     * @param string $method
     * @return bool
     */
    public function locked($method)
    {
        $lock_key = self::getLockKey($method);
        return $this->get("{$lock_key}:l") == true;
    }

    /**
     * Get data by lock key
     *
     * @param string $lock_key
     * @return mixed
     */
    public function getLockedData($lock_key)
    {
        return $this->get($lock_key);
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
        $this->set($lock_key, $data, $lock_expire);
    }
}