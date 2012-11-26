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
     * PHP shared memory resource id
     *
     * @var int
     */
    private $shm_int_key = 1;

    /**
     * Set shared memory integer identifier
     *
     * @param int $shm_key
     */
    public function __construct($shm_key = 1)
    {
        $this->shm_int_key = shm_attach($shm_key);
    }

    /**
     * Generate shared memory resource
     *
     * @param string $key
     * @return resource
     */
    public function getLockKey($key)
    {
        return crc32($key);
    }

    /**
     * Lock method
     *
     * @param string $key
     * @param int $expire
     * @return bool
     */
    public function lock($key, $expire = 10)
    {
        log::put("lock expire {$expire} in phpSemaphore not used", config::getPackageName(__CLASS__));
        $shm_id = self::getLockKey($key);
        return shm_put_var($this->shm_int_key, $shm_id, true);
    }

    /**
     * Unlock method
     *
     * @param string $key
     * @return bool
     */
    public function unlock($key)
    {
        $shm_id = self::getLockKey($key);
        return shm_remove_var($this->shm_int_key, $shm_id);
    }

    /**
     * Check is method locked
     *
     * @param string $key
     * @return bool
     */
    public function locked($key)
    {
        $shm_id = self::getLockKey($key);
        return shm_has_var($this->shm_int_key, $shm_id);
    }

    /**
     * Store data by lock key
     *
     * @param resource $lock_key
     * @param mixed $data
     * @param int $lock_expire
     * @return bool
     */
    public function storeLockedData($lock_key, $data, $lock_expire = 10)
    {
        log::put("storeLockedData expire {$lock_expire} in phpSemaphore not used", config::getPackageName(__CLASS__));
        return shm_put_var($this->shm_int_key, $lock_key, $data);
    }

    /**
     * Get data by lock key
     *
     * @param resource $lock_key
     * @return mixed
     */
    public function getLockedData($lock_key)
    {
        return shm_get_var($this->shm_int_key, $lock_key);
    }

    public function __destruct()
    {
        shm_detach($this->shm_int_key);
    }
}