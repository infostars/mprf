<?php
namespace mpr\interfaces;

/**
 * Locker interface for package mpr_locker and locker drivers packages
 *
 * @author Demin Petr <demin@infostars.ru>
 */
interface locker
{

    /**
     * Generate lock key
     *
     * @static
     * @param string $key
     * @return string
     */
    public function getLockKey($key);

    /**
     * Lock method
     *
     * @static
     * @param string $method
     * @param int $expire
     * @return mixed
     */
    public function lock($method, $expire = 10);

    /**
     * Unlock method
     *
     * @static
     * @param string $method
     * @return mixed
     */
    public function unlock($method);
}