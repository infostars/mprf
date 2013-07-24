<?php
namespace mpr\system;

use mpr\config;

/**
 * Description of daemonization
 *
 * @author GreeveX <greevex@gmail.com>
 */
class daemonization
{
    /**
     * Is current process is daemonized
     *
     * @var bool
     */
    protected static $daemonized = false;

    /**
     * Daemonize current process
     *
     * @throws \Exception
     */
    public static function daemonize()
    {
        self::$daemonized = false;
        static $STDIN, $STDOUT, $STDERR;

        $options = config::getPackageConfig(__CLASS__);

        if(strtolower(php_sapi_name()) != 'cli') {
            throw new \Exception("Can't daemonize in non-cli sapi");
        }
        $pid = pcntl_fork();

        if ($pid < 0) { // Fail
            exit("Daemonization failed!");
        } elseif ($pid > 0) { // Parent
            exit(0);
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

    public static function check()
    {
        if(!self::$daemonized && isset($GLOBALS['argv'])) {
            if(in_array('--daemonize', $GLOBALS['argv'])) {
                self::daemonize();
            }
        }
    }

    /**
     * Is current process daemonized
     *
     * @return bool
     */
    public static function isDaemonized()
    {
        return self::$daemonized;
    }
}