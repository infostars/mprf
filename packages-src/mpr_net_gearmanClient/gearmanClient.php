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
{
    use abstractFactory;

    const DEFAULT_PORT = 4730;

    /**
     * @var socketClient
     */
    private $socketClient;

    /**
     * Gearman client instance
     *
     * @var \GearmanClient
     */
    private $gearmanInstance;

    private $config = [];
    private $configSection = 'default';
    private $initialized = false;

    /**
     * Create new gearman client by config name.
     *
     * @param string $configName key in config array in mpr_config
     */
    public function __construct($configName = 'default')
    {
        $this->configSection =& $configName;
        log::put("Loading config {$this->configSection}...", config::getPackageName(__CLASS__));
        $this->config = config::getPackageConfig(__CLASS__)[$this->configSection];
    }

    public function getConnectionInfo()
    {
        return [
            'host' => $this->config['host'],
            'port' => isset($this->config['port']) ? $this->config['port'] : self::DEFAULT_PORT,
        ];
    }

    protected function init()
    {
        if($this->initialized) {
            return;
        }
        $this->gearmanInstance = new \GearmanClient();
        log::put("Connecting using {$this->configSection}...", config::getPackageName(__CLASS__));
        if(!isset($this->config['port'])) {
            $this->gearmanInstance->addServer($this->config['host']);
        } else {
            $this->gearmanInstance->addServer($this->config['host'], $this->config['port']);
        }
        $this->initialized = true;
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
        $this->init();
        $result = $this->gearmanInstance->addTask($function, json_encode($workload, JSON_UNESCAPED_UNICODE));
        if($start) {
            $this->gearmanInstance->runTasks();
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
        $this->init();
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
        $this->init();
        $result = $this->gearmanInstance->addTaskBackground($function, json_encode($workload, JSON_UNESCAPED_UNICODE));
        if($start) {
            $result = $this->gearmanInstance->runTasks();
        }
        return $result;
    }

    /**
     * Get gearmanClient backend
     *
     * @return \GearmanClient
     */
    public function getBackend()
    {
        $this->init();
        return $this->gearmanInstance;
    }

    public function gearmandInfo()
    {
        static $expression = '/([^\t]+)\t(\d+)\t(\d+)\t(\d+)/';
        $host =& $this->config['host'];
        $port = isset($this->config['port']) ? $this->config['port'] : self::DEFAULT_PORT;
        if(!isset($this->socketClient)) {
            log::put("Requesting data by {$host}:{$port}", __METHOD__);
            $this->socketClient = new socketClient($host, $port);
            $this->socketClient->setName('Gearman connector');
            $this->socketClient->setReadTimeout(10);
            $this->socketClient->setWriteTimeout(10);
            $this->socketClient->connect();
            $this->socketClient->setBlocking();
        }
        $this->socketClient->writeData("status\n");
        $raw_status = '';
        $timeoutAt = time() + 10;
        do {
            $read = $this->socketClient->readData(4096);
            $raw_status .= $read;
        } while(empty($read) && $timeoutAt > time());

        log::put("Parsing data from {$host}:{$port}", __METHOD__);
        preg_match_all($expression, $raw_status, $matches);

        $status = $this->buildStatus($matches);

        log::put("Disconnecting {$host}:{$port}", __METHOD__);

        return $status;
    }

    /**
     * Build status
     *
     * @param $matches
     *
     * @return array
     */
    protected function buildStatus(&$matches)
    {
        $status = [];
        foreach ($matches[0] as $key => $match) {
            $funcName = trim($matches[1][$key]);
            $status[$funcName] = [
                'function' => $funcName,
                'total' => (int)$matches[2][$key],
                'running' => (int)$matches[3][$key],
                'workers' => (int)$matches[4][$key]
            ];
        }

        return $status;
    }
}