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
        $this->shm_int_key = $shm_key;
    }

    /**
     * Generate shared memory resource
     *
     * @param int $key
     * @return resource
     */
    public function getLockKey($key)
    {
        return shm_attach($key);
    }

    /**
     * Lock method
     *
     * @param int $key
     * @param int $expire
     * @return bool
     */
    public function lock($key, $expire = 10)
    {
        $shm_id = self::getLockKey($key);
        return shm_put_var($shm_id, $this->shm_int_key, $key);
    }

    /**
     * Unlock method
     *
     * @param int $key
     * @return bool
     */
    public function unlock($key)
    {
        $shm_id = self::getLockKey($key);
        shm_remove_var($shm_id, $this->shm_int_key);
        return shm_remove($shm_id);
    }

    /**
     * Check is method locked
     *
     * @param int $key
     * @return bool
     */
    public function locked($key)
    {
        $shm_id = self::getLockKey($key);
        return shm_has_var($shm_id, $this->shm_int_key);
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
        return shm_put_var($lock_key, $this->shm_int_key, $data);
    }

    /**
     * Get data by lock key
     *
     * @param resource $lock_key
     * @return mixed
     */
    public function getLockedData($lock_key)
    {
        return shm_get_var($lock_key, $this->shm_int_key);
    }
}