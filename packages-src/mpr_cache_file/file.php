<?php
namespace mpr\cache;

use \mpr\config;
use \mpr\cache;
use \mpr\interfaces\cache as cache_interface;

/**
 * file cache driver for mpr_cache
 *
 * @author GreeveX <greevex@gmail.com>
 */
class file
extends cache
implements cache_interface
{

    /**
     * Cache in-memory data
     *
     * @var array
     */
    private $data = array();

    /**
     * Auto commit flag
     *
     * @var bool
     */
    private $autoCommit = true;

    /**
     * Build cache filename
     *
     * @return string
     */
    private function getCacheFilename()
    {
        static $cache_filename;
        if($cache_filename == null) {
            $cache_path = config::getPackageConfig(__CLASS__)['cache_dir'];
            $cache_filename = $cache_path . "mpr_file_cache.json";
        }
        return $cache_filename;
    }

    /**
     * Commit changes
     *
     * @return bool
     */
    public function commit()
    {
        file_put_contents($this->getCacheFilename(), json_encode($this->data));
        return true;
    }

    /**
     * Initialize driver and load data
     */
    public function __construct()
    {
        $this->loadData();
    }

    /**
     * Load data from file to memory
     *
     * @return bool
     */
    protected function loadData()
    {
        if(file_exists($this->getCacheFilename())) {
            $this->data = json_decode(file_get_contents($this->getCacheFilename()), true);
        }
        return true;
    }

    /**
     * Enable auto commit changes
     *
     * @return bool
     */
    public function enableAutoCommit()
    {
        $this->autoCommit = true;
        return true;
    }

    /**
     * Disable auto commit changes
     *
     * @return bool
     */
    public function disableAutoCommit()
    {
        $this->autoCommit = false;
        return true;
    }

    /**
     * Set value by key
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return bool
     */
    public function set($key, $value, $expire = 0)
    {
        $this->data[$key] = $value;
        if($this->autoCommit) {
            $this->commit();
        }
        return true;
    }

    /**
     * Get value by key
     *
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Check is key exists
     *
     * @param string $key
     * @return bool Result
     */
    public function exists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Remove record from cache by key
     *
     * @param string $key
     * @return bool Result
     */
    public function remove($key)
    {
        if(isset($this->data[$key])) {
            unset($this->data[$key]);
        }
        if($this->autoCommit) {
            $this->commit();
        }
        return true;
    }

    /**
     * WARNING! Clear all cache
     *
     * @return bool
     */
    public function clear()
    {
        $this->data = array();
        $this->commit();
        return true;
    }
}