<?php
namespace mpr\locker;

use mpr\config;
use mpr\debug\log;
use mpr\interfaces;

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
     * @return bool
     */
    public function lock($method, $expire = 10)
    {
        $key = self::getLockKey($method);
        while(!($result = $this->add($key, true, $expire))) {
            usleep(50000);
        }
        return $result;
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
        $key = self::getLockKey($method);
        if ($this->get($key) === false) {

            return false;
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
        return $this->remove($key);
    }
}