<?php
namespace mpr\debug;

use \mpr\config;
use \mpr\io\output;
use \mpr\io\fileWriter;

/**
 * Log package
 *
 * Debug all that you need
 */
class log
{
    /**
     * Is log already initialized
     *
     * @var bool
     */
    private static $initialized = false;

    /**
     * Is log enabled
     *
     * @var bool
     */
    private static $enabled;

    /**
     * Output object
     *
     * @var \mpr\io\output
     */
    private static $output;

    /**
     * fileWriter object to write log to file
     *
     * @var \mpr\io\fileWriter
     */
    private static $logfile;

    /**
     * Initialize resources by config
     *
     * @param array $config
     * @return bool is initialized
     */
    protected static function initResources($config, $force = false)
    {
        static $initialized;
        if($initialized == null || $force) {
            $initialized = true;
            if($config['output'] == 'stdout') {
                self::$output = new output(false, output::OUT_STDOUT);
            } elseif($config['output'] == 'output') {
                self::$output = new output(false, output::OUT_OUTPUT);
            }
            self::$logfile = new fileWriter($config['logfile']);
        }
        return $initialized;
    }

    /**
     * Initialize debug
     *
     * @return bool Result
     */
    public static function init()
    {
        static $last_config;
        $config = config::getPackageConfig(__CLASS__);
        $config_hash = md5(json_encode($config));
        self::$initialized = true;
        if($config_hash != $last_config) {
            $last_config = $config_hash;
            self::$enabled = $config['enabled'];
            if(self::$enabled) {
                self::initResources($config, true);
            }
        }
        return self::$initialized == true;
    }

    /**
     * Put message to log
     *
     * @param string $comment Your text to log
     * @param string $prefix Your log message prefix
     * @warning if log disabled will be (bool)false
     * @return bool true if logged, false if not logged
     */
    public static function put($comment, $prefix)
    {
        if(!isset(self::$initialized)) {
            self::$initialized = true;
            self::init();
        }
        if(self::$enabled) {
            $mtime = explode('.', microtime(true));
            $date = date("Y-m-d H:i:s.", $mtime[0]) . sprintf('%-4d', isset($mtime[1]) ? round($mtime[1], 4) : 0);
            $string = sprintf("<%4s> [%-30s] %s", $date, $prefix, $comment);
            if(self::$logfile) {
                self::$logfile->writeLn($string);
            }
            if(self::$output) {
                self::$output->writeLn($string);
            }
            return true;
        }
        return false;
    }
}