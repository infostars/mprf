<?php
namespace mpr\service;

/**
 * Description of timer
 *
 * @author GreeveX <greevex@gmail.com>
 */
class timer {

    private $time_start;

    private $time_end;

    public function __construct()
    {

    }

    public function start()
    {
        $this->time_start = microtime(true);
        return $this;
    }

    public function stop()
    {
        $this->time_end = microtime(true);
        return $this;
    }

    public function getPeriod($precision = 4)
    {
        $this->time_end = microtime(true);
        return round($this->time_end - $this->time_start, $precision);
    }

    public function reset()
    {
        $this->time_start = 0;
        $this->time_end = 0;
        return $this;
    }
}