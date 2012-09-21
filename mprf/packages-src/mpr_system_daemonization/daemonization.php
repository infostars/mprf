<?php
namespace mpr\system;

use \mpr\debug\log;
use \mpr\config;

/**
 * Description of daemonization
 *
 * @author GreeveX <greevex@gmail.com>
 */
class daemonization
{
    protected static $daemonized = false;

    public static function daemonize()
    {
        self::$daemonized = false;
        static $STDIN, $STDOUT, $STDERR;

        $options = config::getPackageConfig(__CLASS__);

        log::put("Daemonizing...", config::getPackageName(__CLASS__));

        if(strtolower(php_sapi_name()) != 'cli') {
            throw new \Exception("Can't daemonize in non-cli sapi");
        }
        $pid = pcntl_fork();

        if ($pid < 0) { // Fail
            log::put("Daemonization failed!", config::getPackageName(__CLASS__));
            exit();
        } elseif ($pid > 0) { // Parent
            log::put("Daemonized! PID: {$pid}", config::getPackageName(__CLASS__));
            exit();
        } // Child

        posix_setsid();

        fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
        $STDIN = fopen($options['stdin'], 'r');
        $STDOUT = fopen($options['stdout'], 'ab');
        $STDERR = fopen($options['stderr'], 'ab');

        self::$daemonized = true;
    }

    public function isDaemonized()
    {
        return self::$daemonized;
    }
}