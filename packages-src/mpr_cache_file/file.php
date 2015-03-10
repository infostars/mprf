<?php
namespace mpr\cache;

use \mpr\config;
use \mpr\cache;
use \mpr\interfaces\cache as cache_interface;

/**
 * File cache driver wrapper for mpr_cache package
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

    private $config_section = 'default';

    /**
     * Build cache filename
     *
     * @return string
     */
    private function getCacheFilename()
    {
        static $cache_filename;
        if($cache_filename == null) {
            $cache_path = config::getPackageConfig(__CLASS__)[$this->config_section]['cache_dir'];
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
    public function __construct($configSection = 'default')
    {
        $this->config_section = $configSection;
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
        $this->data[$key] = [
            'e' => time() + $expire,
            'v' => $value
        ];
        if($this->autoCommit) {
            $this->commit();
        }
        return true;
    }

    /**
     * Add value by key
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire
     * @return bool
     */
    public function add($key, $value, $expire = 0)
    {
        if($this->exists($key)) {
            return false;
        }
        $this->set($key, $value, $expire);
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
        return $this->exists($key) ? $this->data[$key]['v'] : null;
    }


    /**
     * Check is key exists
     *
     * @param string $key
     * @return bool Result
     */
    public function exists($key)
    {
        if(isset($this->data[$key])) {
            if(time() >= $this->data[$key]['e']) {
                $this->remove($key);
            } else {
                return true;
            }
        }
        return false;
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

    /**
     * Return last error
     *
     * @return mixed
     */
    public function getResultCode()
    {
        // TODO: Implement getResultCode() method.
    }

    /**
     * Cache driver backend
     *
     * @return mixed
     */
    public function getBackend()
    {
        // TODO: Implement getBackend() method.
    }
}