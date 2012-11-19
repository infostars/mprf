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
        return "locked:{$key}";
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
        return $this->set("{$method}:lock", true, $expire);
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
        return $this->set("{$method}:lock", false);
    }

    /**
     * Check is method locked
     *
     * @param string $method
     * @return bool
     */
    public function locked($method)
    {
        $method = self::getLockKey($method);
        return $this->get("{$method}:lock") == true;
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
    public function storeLockedData($lock_key, $data, $lock_expire)
    {
        $this->set($lock_key, $data, $lock_expire);
    }
}