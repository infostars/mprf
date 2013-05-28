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
     * @param int    $expire
     * @return mixed
     */
    public function lock($method, $expire = 10)
    {
        $key = self::getLockKey($method);
        while($this->exists($key)) {
            usleep(50000);
        }
        return $this->set($key, true, $expire);
    }

    public function locked($method)
    {
        $key = self::getLockKey($method);
        return $this->get($key) === true;
    }

    /**
     * Unlock method
     *
     * @param string $method
     * @return mixed
     */
    public function unlock($method)
    {
        $key = self::getLockKey($method);
        return $this->set($key, false);
    }

}