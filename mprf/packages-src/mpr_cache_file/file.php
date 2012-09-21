<?php
namespace mpr\cache;

use \mpr\config;

/**
 * file cache driver for mpr_cache_factory
 *
 * @author GreeveX <greevex@gmail.com>
 */
class file
extends factory
implements \mpr\cache\cache_interface
{

    private $data = array();

    private $autoCommit = true;

    private function getCacheFilename()
    {
        static $cache_filename;
        if($cache_filename == null) {
            $cache_path = config::getPackageConfig(__CLASS__)['cache_dir'];
            $cache_filename = $cache_path . "mpr_file_cache.json";
        }
        return $cache_filename;
    }

    public function commit()
    {
        file_put_contents($this->getCacheFilename(), json_encode($this->data));
    }

    public function __construct()
    {
        $this->loadData();
    }

    protected function loadData()
    {
        if(file_exists($this->getCacheFilename())) {
            $this->data = json_decode(file_get_contents($this->getCacheFilename()), true);
        }
    }

    public function enableAutoCommit()
    {
        $this->autoCommit = true;
    }

    public function disableAutoCommit()
    {
        $this->autoCommit = false;
    }

    public function set($key, $value, $expire = 0)
    {
        $this->data[$key] = $value;
        if($this->autoCommit) {
            $this->commit();
        }
    }

    public function get($key)
    {
        return $this->data[$key];
    }

    public function exists($key)
    {
        return isset($this->data[$key]);
    }

    public function remove($key)
    {
        if(isset($this->data[$key])) {
            unset($this->data[$key]);
        }
        if($this->autoCommit) {
            $this->commit();
        }
    }

    public function clear()
    {
        $this->data = array();
        $this->commit();
    }
}