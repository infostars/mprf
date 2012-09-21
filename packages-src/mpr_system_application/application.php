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

    public $app_config;

    public $only_one_instance = false;

    abstract public function handle();

    public function run()
    {
        log::put("Starting application...", config::getPackageName(__CLASS__));
        $this->handle();
        log::put("Ending application...", config::getPackageName(__CLASS__));
    }
}