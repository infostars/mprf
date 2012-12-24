<?php

namespace mpr\system;

use \mpr\debug\log;
use \mpr\config;

/**
 * Description of application
 *
 * @author GreeveX <greevex@gmail.com>
 */
abstract class application
{
    /**
     * How much instances we can store
     *
     * @var bool
     */
    protected $only_one_instance = false;

    /**
     * Handle function
     *
     * @return mixed
     */
    abstract protected function handle();

    /**
     * Get toolkit object
     *
     * @return \mpr\toolkit
     */
    protected function getToolkit()
    {
        static $toolkit;
        if($toolkit == null) {
            $toolkit = new \mpr\toolkit();
        }
        return $toolkit;
    }

    /**
     * Get package config data
     *
     * @return mixed
     */
    protected function getPackageConfig()
    {
        return config::getPackageConfig(get_called_class());
    }

    /**
     * Run application
     */
    public function run()
    {
        log::put("Starting application...", config::getPackageName(__CLASS__));
        $this->handle();
        log::put("Ending application...", config::getPackageName(__CLASS__));
    }

    /**
     * Return how much instances is allowed
     *
     * @return bool
     */
    public function isOnlyOneInstanceAllowed()
    {
        return $this->only_one_instance;
    }
}
