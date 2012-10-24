<?php
namespace mpr\threads;

use \mpr\debug\log;
use \mpr\config;

/**
 * Implements threading in PHP
 *
 */
class thread
{

    /**
     * Status code
     * Function is not callable
     */
    const FUNCTION_NOT_CALLABLE     = 10;

    /**
     * Status code
     * Couldn't fork
     */
    const COULD_NOT_FORK            = 15;

    /**
     * Status code
     * Fork ready
     */
    const FORK_READY                = -50;

    /**
     * Possible errors
     *
     * @var array
     */
    private $errors = [
        self::FUNCTION_NOT_CALLABLE   => 'You must specify a valid function name that can be called from the current scope.',
        self::COULD_NOT_FORK          => 'pcntl_fork() returned a status of -1. No new process was created',
    ];

    /**
     * callback for the function that should
     * run as a separate thread
     *
     * @var callable
     */
    protected $runnable;

    /**
     * holds the current process id
     *
     * @var integer
     */
    private $pid;

    /**
     * Get thread data file path
     *
     * @return string
     */
    public function getPath()
    {
        $pid = $this->getPid();
        if(empty($pid)) {
            $pid = getmypid();
        }
        return sys_get_temp_dir() . "/phpthread.{$pid}.data";
    }

    /**
     * checks if threading is supported by the current
     * PHP configuration
     *
     * @return boolean
     */
    public static function available()
    {
        $required_functions = array(
            'pcntl_fork',
        );

        foreach( $required_functions as $function ) {
            if ( !function_exists( $function ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * class constructor - you can pass
     * the callback function as an argument
     *
     * @param callable $_threadStart
     */
    public function __construct($_threadStart = null)
    {
        if($_threadStart !== null) {
            $this->setRunnable($_threadStart);
        }
    }

    /**
     * Set result for this thread
     *
     * @param $data
     */
    public function setResult($data)
    {
        file_put_contents($this->getPath(), serialize($data));
    }

    /**
     * Get result of this thread
     *
     * @return mixed|null
     */
    public function getResult()
    {
        if(!file_exists($this->getPath()))
        {
            return null;
        }
        $data = unserialize(file_get_contents($this->getPath()));
        unlink($this->getPath());
        return $data;
    }

    /**
     * Set callback
     *
     * @param callable $runnable
     */
    public function setRunnable($runnable)
    {
        $this->runnable = $runnable;
    }

    /**
     * Get callback
     *
     * @return callable
     */
    public function getRunnable()
    {
        return $this->runnable;
    }

    /**
     * returns the process id (pid) of the simulated thread
     *
     * @return int pid
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * checks if the child thread is alive
     *
     * @return boolean
     */
    public function isAlive()
    {
        $pid = pcntl_waitpid( $this->pid, $status, WNOHANG );
        return ( $pid === 0 );
    }

    /**
     * starts the thread, all the parameters are
     * passed to the callback function
     *
     * @return \mpr\threads\thread
     * @throws \Exception
     */
    public function start()
    {
        $pid = @ pcntl_fork();
        if( $pid == -1 ) {
            throw new \Exception( $this->getError( self::COULD_NOT_FORK ), self::COULD_NOT_FORK );
        }
        if( $pid ) {
            $this->pid = $pid;
            $status = null;
        }
        else {
            $arguments = func_get_args();
            pcntl_signal(SIGTERM, array($this, 'signalHandler'));
            register_shutdown_function(array($this, 'signalHandler'));
            pcntl_signal_dispatch();
            call_user_func_array($this->runnable, $arguments);
            exit(0);
        }
        return $this;
    }

    /**
     * attempts to stop the thread
     * returns true on success and false otherwise
     *
     * @param integer $_signal - SIGKILL/SIGTERM
     * @param boolean $_wait
     */
    public function stop( $_signal = SIGKILL, $_wait = false )
    {
        $isAlive = (int)$this->isAlive();
        log::put("Stopping process {$this->pid}, alive:{$isAlive}", config::getPackageName(__CLASS__));
        if($isAlive) {
            posix_kill( $this->pid, $_signal );
            if( $_wait ) {
                pcntl_waitpid( $this->pid, $status = 0 );
            }
        }
    }

    /**
     * alias of stop();
     *
     * @return boolean
     */
    public function kill( $_signal = SIGKILL, $_wait = false )
    {
        log::put("Killing process with pid {$this->pid}...", config::getPackageName(__CLASS__));
        for($i = 0; $i < 10; $i++) {
            posix_kill( $this->pid, $_signal );
            usleep(10000);
        }
        if( $_wait ) {
            log::put("Waiting process [pid {$this->pid}]...", config::getPackageName(__CLASS__));
            pcntl_waitpid( $this->pid, $status = 0 );
        }
        log::put("Killed! [pid {$this->pid}]...", config::getPackageName(__CLASS__));
    }

    /**
     * gets the error's message based on
     * its id
     *
     * @param integer $_code
     * @return string
     */
    public function getError( $_code )
    {
        if ( isset( $this->errors[$_code] ) ) {
            return $this->errors[$_code];
        }
        else {
            return 'No such error code ' . $_code . '! Quit inventing errors!!!';
        }
    }

    /**
     * signal handler
     *
     * @param integer $_signal
     */
    protected function signalHandler($_signal = SIGTERM)
    {
        switch($_signal) {
            case SIGTERM:
                log::put(__METHOD__ . ":exit()", config::getPackageName(__CLASS__));
                exit();
                break;
        }
    }
}