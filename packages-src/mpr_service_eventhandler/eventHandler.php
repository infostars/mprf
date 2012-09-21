<?php
namespace mpr\service;

use \mpr\debug\log;

class eventHandler
{

    protected $callback;
    protected $callable;
    protected $params = array();
    protected $event;

    public function onEvent($callback)
    {
        if(!is_callable($callback)) {
            return false;
        }
        $this->callback = $callback;
        return true;
    }

    public function registerEventFunc($callable, $params = array())
    {
        if(!is_callable($callable)) {
            return false;
        }
        $this->callable = $callable;
        $this->params = $params;
        declare(ticks=100);
        register_tick_function(array($this, 'onTick'));
    }

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