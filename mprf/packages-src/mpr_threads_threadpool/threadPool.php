<?php
namespace mpr\threads;

/**
 * Thread master
 */
class threadPool
{
    private $max_threads    = 5;
    private $max_queue      = 5;
    private $queue          = array();
    private $pool           = array();
    private $result         = array();

    public function __construct()
    {

    }

    public function hasAlive()
    {
        $this->refresh();
        return (count($this->pool) > 0 || count($this->queue) > 0);
    }

    public function waitAll()
    {
        while($this->hasAlive()) {
            $this->refresh();
            usleep(100000);
        }
    }

    public function refresh()
    {
        // Проверяем наличие законченных заданий
        foreach($this->pool as $thread_key => $thread) {
            if(!$thread->isAlive()) {
                $thread->kill();
                //$this->result[$thread_key] = $thread->getResult();
                unset($this->pool[$thread_key]);
            }
        }
        // Добавляем задачи из очереди
        while(count($this->queue) > 0 && count($this->pool) <= $this->max_threads) {
            list($thread, $params) = array_shift($this->queue);
            $this->pool[] = $thread->start($params);
        }
    }

    public function getPoolCount()
    {
        return is_array($this->pool) ? count($this->pool) : 0;
    }

    public function add($thread, $arguments = null)
    {
        if(count($this->pool) >= $this->max_threads && count($this->queue) >= $this->max_queue) {
            return false;
        }
        $this->queue[] = array($thread, $arguments);
        $this->refresh();
        return true;
    }

    public function setMaxQueue($max_queue)
    {
        $this->max_queue = $max_queue;
    }

    public function getMaxQueue()
    {
        return $this->max_queue;
    }

    public function setMaxThreads($max_threads)
    {
        $this->max_threads = $max_threads;
    }

    public function getMaxThreads()
    {
        return $this->max_threads;
    }

    public function getPool()
    {
        return $this->pool;
    }

    public function getResults()
    {
        $result = $this->result;
        $this->result = array();
        return $result;
    }
}