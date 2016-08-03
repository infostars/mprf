<?php
namespace mpr\cache;

use \mpr\config;
use \mpr\interfaces\cache as cache_interface;

/**
 * Memcached driver wrapper for mpr_cache package
 *
 * @author GreeveX <greevex@gmail.com>
 */
class memcached
    extends \mpr\cache
    implements cache_interface
{

    /**
     * Memcached native driver instance
     *
     * @var \Memcached
     */
    protected $memcached;

    protected $configSection;

    /**
     * Commit changes
     *
     * @not implemented for this driver!
     * @throws \Exception
     */
    public function commit()
    {
        throw new \Exception("Transactions not implemented in package " . config::getPackageName(__CLASS__));
    }

    /**
     * Enable auto commit changes
     *
     * @not implemented for this driver!
     * @throws \Exception
     */
    public function enableAutoCommit()
    {
        throw new \Exception("Transactions not implemented in package " . config::getPackageName(__CLASS__));
    }

    /**
     * Disable auto commit changes
     *
     * @not implemented for this driver!
     * @throws \Exception
     */
    public function disableAutoCommit()
    {
        throw new \Exception("Transactions not implemented in package " . config::getPackageName(__CLASS__));
    }

    /**
     * Initialize driver and connect to host
     *
     * @var string $configSection
     */
    public function __construct($configSection = 'default')
    {
        $config = config::getPackageConfig(__CLASS__)[$configSection];
        $this->configSection = $configSection;
        $this->memcached = new \Memcached();
        foreach($config['servers'] as $server) {
            $this->memcached->addServer($server['host'], $server['port']);
        }
    }

    protected function reconnect()
    {
        $config = config::getPackageConfig(__CLASS__)[$this->configSection];
        $this->memcached = new \Memcached();
        foreach ($config['servers'] as $server) {
            $this->memcached->addServer($server['host'], $server['port']);
        }
    }

    protected function checkConnection()
    {
        static $pid, $srvPid;
        $currentPid = getmypid();
        if($currentPid !== $pid) {
            $pid = $currentPid;
            $srvCurPid = reset($this->memcached->getStats())['pid'];
            if($srvPid !== $srvCurPid) {
                $srvPid = $srvCurPid;
                $this->reconnect();
            }
        }
    }

    /**
     * Set value by key
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire
     *
     * @return bool|mixed
     */
    public function set($key, $value, $expire = 60)
    {
        $this->checkConnection();

        return $this->memcached->set($key, $value, $expire);
    }

    /**
     * Add value by key
     *
     * @param string $key
     * @param mixed $value
     * @param int $expire
     *
     * @return bool|mixed
     */
    public function add($key, $value, $expire = 60)
    {
        $this->checkConnection();

        return $this->memcached->add($key, $value, $expire);
    }

    /**
     * Get value by key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $this->checkConnection();

        return $this->memcached->get($key);
    }

    /**
     * Check is key exists
     *
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        $this->checkConnection();
        $data = $this->memcached->get($key);

        return !($data === false || $data === null);
    }

    /**
     * Remove record from cache by key
     *
     * @param string $key
     * @return bool
     */
    public function remove($key)
    {
        $this->checkConnection();

        return $this->memcached->delete($key);
    }

    /**
     * WARNING! Clear all cache!
     *
     * @return bool
     */
    public function clear()
    {
        $this->checkConnection();

        return $this->memcached->flush();
    }

    /**
     * Return last error
     *
     * @return mixed
     */
    public function getResultCode()
    {
        $this->checkConnection();

        return $this->memcached->getResultCode();
    }

    /**
     * Cache driver backend
     *
     * @return mixed
     */
    public function getBackend()
    {
        $this->checkConnection();

        return $this->memcached;
    }
}