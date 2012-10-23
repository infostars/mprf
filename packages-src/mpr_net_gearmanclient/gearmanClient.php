<?php

namespace mpr\net;

use \mpr\config;
use \mpr\debug\log;
use \mpr\pattern\abstractFactory;

/**
 * Client for Gearman queue server
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class gearmanClient
extends abstractFactory
{

    /**
     * Gearman client instance
     *
     * @var \GearmanClient
     */
    private $gearmanInstance;

    /**
     * Create new gearman client by config name.
     *
     * @param string $configName key in config array in mpr_config
     */
    public function __construct($configName = 'default')
    {
        $this->gearmanInstance = new \GearmanClient();
        log::put("Loading config {$configName}...", config::getPackageName(__CLASS__));
        $config = config::getPackageConfig(__CLASS__)[$configName];
        if(!isset($config['port'])) {
            log::put("Connecting using {$configName}...", config::getPackageName(__CLASS__));
            $this->gearmanInstance->addServer($config['host']);
        } else {
            $this->gearmanInstance->addServer($config['host'], $config['port']);
        }
    }

    /**
     * Set method that would be called when task is completed
     * Attention! Method wouldn't be called if you use sendToBackground method!
     *
     * @param callable $callable Runnable function, method or closure
     * @return bool result
     */
    public function setOnComplete($callable)
    {
        return $this->gearmanInstance->setCompleteCallback($callable);
    }

    /**
     * Add task to foreground queue by function name and params
     *
     * @param string $function Gearman function name
     * @param mixed $workload Params that would be transfered to called function (would be json encoded)
     * @param bool $start Start it now
     * @return \GearmanTask
     */
    public function addToTasks($function, $workload, $start = false)
    {
        $result = $this->gearmanInstance->addTask($function, json_encode($workload));
        if($start) {
            $result = $this->gearmanInstance->runTasks();
        }
        return $result;
    }

    /**
     * Run tasks, that was sent to background with param start = false
     *
     * @return bool Result
     */
    public function runTasks()
    {
        return $this->gearmanInstance->runTasks();
    }

    /**
     * Add task to background queue
     *
     * @param string $function Function name
     * @param mixed $workload Params that would be transfered to called function (would be json encoded)
     * @param bool $start Start it now
     * @return bool|\GearmanTask
     */
    public function sendToBackground($function, $workload, $start = true)
    {
        $result = $this->gearmanInstance->addTaskBackground($function, json_encode($workload));
        if($start) {
            $result = $this->gearmanInstance->runTasks();
        }
        return $result;
    }
}