<?php
namespace mpr\interfaces;

/**
 * Cache interface for cache factory and drivers
 *
 * @author GreeveX <greevex@gmail.com>
 */
interface cache
{

    /**
     * Set value by key into cache
     *
     * @abstract
     * @param $key
     * @param $value
     * @param string $expire
     * @return mixed
     */
    public function set($key, $value, $expire = '600');

    /**
     * Get value from cache by key
     *
     * @abstract
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * Remove value from cache by key
     *
     * @abstract
     * @param $key
     * @return mixed
     */
    public function remove($key);

    /**
     * Check key exists in cache
     *
     * @abstract
     * @param $key
     * @return mixed
     */
    public function exists($key);


    /**
     * DANGER! Clear all cache !!!
     *
     * @abstract
     * @return mixed
     */
    public function clear();

    /**
     * Enable auto-commit
     * It's means that every command sends operation to cache
     * If auto commit disabled it works like a transaction
     *
     * @abstract
     * @return mixed
     */
    public function enableAutoCommit();

    /**
     * Disable auto-commit
     * It's means that every command sends operation to cache
     * If auto commit disabled it works like a transaction
     *
     * @abstract
     * @return mixed
     */
    public function disableAutoCommit();

    /**
     * Commit data to cache
     * Like end of transaction started by enableAutoCommit()
     *
     * @abstract
     * @return mixed
     */
    public function commit();
}