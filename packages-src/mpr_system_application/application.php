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

    protected function getToolkit()
    {
        static $toolkit;
        if($toolkit == null) {
            $toolkit = new \mpr\toolkit();
        }
        return $toolkit;
    }

    protected function getPackageConfig()
    {
        return config::getPackageConfig(get_called_class());
    }

    public function run()
    {
        log::put("Starting application...", config::getPackageName(__CLASS__));
        $this->handle();
        log::put("Ending application...", config::getPackageName(__CLASS__));
    }
}
