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
        $key = self::getLockKey($method);
        do {
            usleep(rand(100000,500000));
        } while($this->get($key));
        return $this->set("{$key}:l", true, $expire);
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

}