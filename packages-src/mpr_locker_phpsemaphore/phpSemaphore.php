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
    private $shm_variable_key = 1;

    /**
     * Expire time
     *
     * @var int
     */
    private $expire_time = 0;

    /**
     * Generate shared memory resource
     *
     * @param int $key
     * @return resource
     */
    public function getLockKey($key)
    {
        $this->shm_variable_key = intval($key);
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
        $this->expire_time = time() + $expire;

        $shm_id = self::getLockKey($key);
        return shm_put_var($shm_id, $this->shm_variable_key, $key);
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
        if(shm_has_var($shm_id, $this->shm_variable_key)) {
            log::put("Has var - remove var {$this->shm_variable_key} from {$shm_id}", config::getPackageName(__CLASS__));
            shm_remove_var($shm_id, $this->shm_variable_key);
        }
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
        $has_var = shm_has_var($shm_id, $this->shm_variable_key);

        if($has_var && $this->expire_time < time()) {
            return $this->unlock($key);
        }

        return $has_var;
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
        return shm_put_var($lock_key, $this->shm_variable_key, $data);
    }

    /**
     * Get data by lock key
     *
     * @param resource $lock_key
     * @return mixed
     */
    public function getLockedData($lock_key)
    {
        return shm_get_var($lock_key, $this->shm_variable_key);
    }
}