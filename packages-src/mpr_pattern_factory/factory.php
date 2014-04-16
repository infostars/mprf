<?php

namespace mpr\pattern;

use mpr\config;

/**
 * Trait factory for initialize instances with custom config sections
 *
 * @note If you have __construct() method in your class - don't forget to call trait construct method! You can use alias to do it.
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 * @author Borovikov Maxim <maxim.mahi@gmail.com>
 */
trait factory
{

    private $configSection = 'default';

    /**
     * Initialize instances with custom config sections
     *
     * @static
     * @param string $configSection
     *
     * @return static
     */
    public static function factory($configSection = 'default')
    {
        static $instances = [];
        if (!isset($instances[$configSection])) {
            $instances[$configSection] = new self($configSection);
        }

        return $instances[$configSection];
    }

    protected function getPackageConfig()
    {
        static $config;
        if (!isset($config)) {
            $config = config::getPackageConfig(__CLASS__);
        }
        return $config[$this->configSection];
    }

    public function __construct($configSection)
    {
        $this->configSection = $configSection;
    }
}
