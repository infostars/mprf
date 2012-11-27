<?php
namespace mpr;

use \mpr\config;
use \mpr\debug\log;
use \mpr\interfaces;

/**
 * Locker package
 *
 * Semaphore implementation using driver package name
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 * @author Diomin Piotr <demin@infostars.ru>
 */
class locker
implements interfaces\locker
{
    /**
     * Locker driver instances
     *
     * @static
     * @var array
     */
    protected static $instances = array();

    /**
     * Locker driver backend
     *
     * @see interfaces\locker
     * @var locker
     */
    private $backend;

    /**
     * Factory locker by driver package name
     *
     * @static
     * @param string|null $configSection
     * @see \mpr\interfaces\locker
     * @return self
     */
    public static function factory($configSection = 'default')
    {
        if(!isset(self::$instances[$configSection])) {
            self::$instances[$configSection] = new self($configSection);
        }
        return self::$instances[$configSection];
    }

    /**
     * Build locker by driver's package name
     *
     * @param $configSection
     */
    public function __construct($configSection)
    {
        $currentConfig = config::getPackageConfig(__CLASS__)[$configSection];
        $driver = config::getClassName($currentConfig['driver']);
        $this->backend = new $driver($currentConfig['backend_section']);
    }

    /**
     * Generate lock key
     *
     * @param string $key
     * @return string
     */
    public function getLockKey($key)
    {
        return $this->backend->getLockKey($key);
    }

    /**
     * Lock method
     *
     * @param string $method
     * @param int $expire
     * @return mixed
     */
    public function lock($method, $expire = 10)
    {
        log::put("Lock method {$method}", config::getPackageName(__CLASS__));
        return $this->backend->lock($method, $expire);
    }

    /**
     * Unlock method
     *
     * @param string $method
     * @return mixed
     */
    public function unlock($method)
    {
        log::put("Unlock method {$method}", config::getPackageName(__CLASS__));
        return $this->backend->unlock($method);
    }

    /**
     * Call some function or closure with params as locked function (ala semaphore)
     *
     * @param callable $callable closure, function or method
     * @param mixed|null $input params
     * @param string $method_name name of locked function
     * @return mixed
     */
    public function lockedFunction($callable, &$input, $method_name)
    {
        self::lock($method_name);

        $data = call_user_func($callable, $input);

        self::unlock($method_name);

        return $data;
    }
}