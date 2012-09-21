<?php
namespace mpr\debug;

use \mpr\config;
use \mpr\io\output;
use \mpr\io\fileWriter;

class log
{
    /**
     * @var bool
     */
    private static $initialized = false;

    /**
     * @var bool
     */
    private static $enabled;

    /**
     * @var \mpr\io\output
     */
    private static $output;

    /**
     * @var \mpr\io\fileWriter
     */
    private static $logfile;

    protected static function initResources($config)
    {
        static $initialized;
        if($initialized == null) {
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

    public static function init()
    {
        $config = config::getPackageConfig(__CLASS__);
        self::$enabled = $config['enabled'];
        if(self::$enabled) {
            self::initResources($config);
        }
        self::$initialized = true;
    }

    public static function put($comment, $prefix)
    {
        if(!self::$initialized) {
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
        }
    }
}