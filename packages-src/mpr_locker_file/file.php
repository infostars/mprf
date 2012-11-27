<?php
namespace mpr;

use \mpr\config;
use \mpr\debug\log;
use \mpr\interfaces;

/**
 * Locker driver
 *
 * Semaphore implementation using file locks
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
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
            $file = config::getPackageConfig(__CLASS__)['path'] . "{$key}.sem";
            $resources[$key] = fopen($file, 'r+');
            chmod($file, 0777);
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
        return flock($this->getLockKey($method), LOCK_EX);
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
        return flock($this->getLockKey($method), LOCK_UN);
    }

}