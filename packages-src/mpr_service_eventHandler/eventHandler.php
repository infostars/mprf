<?php

namespace mpr\service;

use \mpr\debug\log;

/**
 * Register and call functions on events
 */
class eventHandler
{
    /**
     * Callback function that called after callable function
     *
     * @var callable
     */
    protected $callback;

    /**
     * Run this callable function in onTick method
     *
     * @var callable
     */
    protected $callable;

    /**
     * Parameters
     *
     * @var array
     */
    protected $params = array();

    /**
     * @param $callback
     * @return bool
     */
    public function onEvent($callback)
    {
        if(!is_callable($callback)) {
            return false;
        }
        $this->callback = $callback;
        return true;
    }

    /**
     * Store event function in protected callable variable with parameters
     *
     * @param $callable
     * @param array $params
     * @return bool
     */
    public function registerEventFunc($callable, $params = array())
    {
        if(!is_callable($callable)) {
            return false;
        }
        $this->callable = $callable;
        $this->params = $params;
        declare(ticks=100);
        register_tick_function(array($this, 'onTick'));

        return true;
    }

    /**
     * Call $callable function with parameters if success call $callback function
     */
    public function onTick()
    {
        log::put("Checking...", __METHOD__);
        $result = call_user_func_array($this->callable, $this->params);
        if($result === true) {
            log::put("Calling...", __METHOD__);
            call_user_func($this->callback);
        }
    }
}