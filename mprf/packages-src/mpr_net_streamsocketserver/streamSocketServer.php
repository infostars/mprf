<?php
namespace mpr\net;

/**
 *
 */
class streamSocketServer
{
    private $__server_listening = true;

    private $host = '127.0.0.1';
    private $port = 65455;
    private $pause = 1000;
    private $onConnect;
    private $onEveryCycle;
    private $name = "SockServ";

    public function setName($name)
    {
        $this->name = $name;
    }

    public function __construct($host = null, $port = null)
    {
        if($host !== null) {
            $this->setHost($host);
        }
        if($port !== null) {
            $this->setPort($port);
        }
        ob_implicit_flush();
    }

    public function start()
    {
        echo "Starting socket server [{$this->name}] on {$this->getHost()}:{$this->getPort()}\n";
        $this->loop();
    }

    /**
     * Change the identity to a non-priv user
     *
     * @param $uid
     * @param $gid
     */
    private function chowner($uid, $gid)
    {
        posix_setgid($gid);
        posix_setuid($uid);
    }

    /**
     * Creates a server socket and listens for incoming client connections
     *
     */
    private function loop()
    {
        $try_counter = 0;
        do {
            try {
                $try_counter++;
                $sock = stream_socket_server("tcp://{$this->getHost()}:{$this->getPort()}", $errno, $errstr);
            } catch(\Exception $e) {
                echo "Error! Code: {$errno} / Message: {$errstr}\n{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n";
            }
        } while(!$sock);
        echo "\rServer [{$this->name}] created! [$try_counter]\n";

        stream_set_blocking($sock, 0);
        while ($this->__server_listening) {
            $connection = false;
            try {
                $select = array($sock);
                $p2 = null;
                $p3 = null;
                if(stream_select($select, $p2, $p3, 0, 100)) {
                    $connection = stream_socket_accept($sock, 0);
                }
            } catch(\Exception $e) {
                $connection = false;
            }
            if($connection !== false && $this->onConnect) {
                call_user_func_array($this->onConnect, array($sock, $connection));
            }
            if($this->onEveryCycle) {
                call_user_func($this->onEveryCycle);
            }
            usleep(intval($this->pause));
        }
    }

    private function handle_client($sock, $connection)
    {

    }

    private function handle_every_cycle()
    {

    }

    /**
     * Signal handler
     *
     * @param int $sig Signal
     */
    public function sig_handler($sig)
    {
        switch($sig)
        {
            case SIGTERM:
            case SIGINT:
                exit();
                break;

            case SIGCHLD:
                pcntl_waitpid(-1, $status);
                break;
        }
    }

    /**
     * @param $socket
     */
    private function interact($socket)
    {
        /* TALK TO YOUR CLIENT */
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setPort($port)
    {
        $this->port = $port;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function setPause($pause)
    {
        $this->pause = $pause;
    }

    public function getPause()
    {
        return $this->pause;
    }

    public function setOnConnect($onConnect)
    {
        $this->onConnect = $onConnect;
    }

    public function setOnEveryCycle($onEveryCycle)
    {
        $this->onEveryCycle = $onEveryCycle;
    }
}