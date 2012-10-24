<?php

namespace mpr\threads;

/**
 * Thread Pool class
 *
 * Organize thread pool with queue
 */
class threadPool
{
    /**
     * Max treads in pool
     *
     * @var int
     */
    private $max_threads = 5;

    /**
     * Max queue length
     *
     * @var int
     */
    private $max_queue = 5;

    /**
     * Pool queue array
     *
     * @var array
     */
    private $queue = array();

    /**
     * Pool array
     *
     * @var array
     */
    private $pool = array();

    /**
     * Result array
     *
     * @var array
     */
    private $result = array();

    /**
     * Check if has alive threads
     *
     * @return bool
     */
    public function hasAlive()
    {
        $this->refresh();
        return (count($this->pool) > 0 || count($this->queue) > 0);
    }

    /**
     * Wait for alive threads
     *
     * @return bool
     */
    public function waitAll()
    {
        while($this->hasAlive()) {
            $this->refresh();
            usleep(100000);
        }

        return true;
    }

    /**
     * Refresh threads in pool
     *
     * @return bool
     */
    public function refresh()
    {
        /**
         * @note kill thread if thread is not alive
         */
        foreach($this->pool as $thread_key => $thread) {
            if(!$thread->isAlive()) {
                $thread->kill();
                //$this->result[$thread_key] = $thread->getResult();
                unset($this->pool[$thread_key]);
            }
        }
        /**
         * @note add threads in pool from queue
         */
        while(count($this->queue) > 0 && count($this->pool) <= $this->max_threads) {
            list($thread, $params) = array_shift($this->queue);
            $this->pool[] = $thread->start($params);
        }

        return true;
    }

    /**
     * Get pool count
     *
     * @return int
     */
    public function getPoolCount()
    {
        return is_array($this->pool) ? count($this->pool) : 0;
    }

    /**
     * Add thread with arguments in pool
     *
     * @param callable $thread
     * @param array|null $arguments
     * @return bool
     */
    public function add($thread, $arguments = null)
    {
        if(count($this->pool) >= $this->max_threads && count($this->queue) >= $this->max_queue) {
            return false;
        }
        $this->queue[] = array($thread, $arguments);
        $this->refresh();

        return true;
    }

    /**
     * Set max queue length
     *
     * @param $max_queue
     * @return mixed
     */
    public function setMaxQueue($max_queue)
    {
       return $this->max_queue = $max_queue;
    }

    /**
     * Get max queue length
     *
     * @return int
     */
    public function getMaxQueue()
    {
        return $this->max_queue;
    }

    /**
     * Set max threads length
     *
     * @param int $max_threads
     * @return mixed
     */
    public function setMaxThreads($max_threads)
    {
        return $this->max_threads = $max_threads;
    }

    /**
     * Get max threads value
     *
     * @return int max threads
     */
    public function getMaxThreads()
    {
        return $this->max_threads;
    }

    /**
     * Get thread pool
     *
     * @return array pool
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * Get threads work results
     *
     * @return array threads work results
     */
    public function getResults()
    {
        $result = $this->result;
        $this->result = array();

        return $result;
    }
}