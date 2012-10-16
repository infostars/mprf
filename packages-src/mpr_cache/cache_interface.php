<?php
namespace mpr\cache;

/**
 * Cache interface for cache factory and drivers
 *
 * @author GreeveX <greevex@gmail.com>
 */
interface cache_interface
{

    /**
     * Set value by key
     *
     * @abstract
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return mixed result
     */
    public function set($key, $value, $expire = 600);

    /**
     * Get value by key
     *
     * @abstract
     * @param string $key
     * @return mixed result
     */
    public function get($key);

    /**
     * Remove record by key
     *
     * @abstract
     * @param string $key
     * @return mixed result
     */
    public function remove($key);

    /**
     * Checks is key exists in cache
     *
     * @abstract
     * @param $key
     * @return mixed
     */
    public function exists($key);


    /**
     * Flush all
     *
     * @abstract
     * @return mixed result
     */
    public function clear();

    /**
     * Enable auto-commit
     * Non-transaction mode
     *
     * @abstract
     * @return mixed result
     */
    public function enableAutoCommit();

    /**
     * Disable auto-commit
     * Transaction mode
     *
     * @abstract
     * @return mixed result
     */
    public function disableAutoCommit();

    /**
     * Commit data to cache
     * End of transaction
     *
     * @abstract
     * @return mixed result
     */
    public function commit();
}