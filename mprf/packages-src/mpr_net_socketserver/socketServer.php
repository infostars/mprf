<?php
namespace mpr\net;

/**
 *
 */
class socketServer
{
    private $__server_listening = true;

    private $host = '127.0.0.1';
    private $port = 65455;
    private $pause = 1000;
    private $onConnect;
    private $onEveryCycle;
    private $name = "SockServ";
    private $blocking = false;
    private $timeout_read = 1;
    private $timeout_write = 1;

    public function setReadTimeout($timeout_read)
    {
        $this->timeout_read = $timeout_read;
    }

    public function setWriteTimeout($timeout_write)
    {
        $this->timeout_write = $timeout_write;
    }

    public function serverBlocking($isBlocking)
    {
        $this->blocking = (bool)$isBlocking;
    }

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
                $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
                socket_set_option($sock, SOL_SOCKET, SO_SNDTIMEO, array("sec" => $this->timeout_write, "usec" => 0));
                socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec" => $this->timeout_read, "usec" => 0));
                socket_bind($sock, $this->host, $this->port);
                socket_listen($sock);
            } catch(\Exception $e) {
                echo "Error! Code: {$e->getCode()} / Message: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n";
            }
        } while(!$sock && $try_counter < 5);
        if(!$sock) {
            return;
        }
        echo "\rServer [{$this->name}] created! [$try_counter]\n";

        if($this->blocking) {
            socket_set_block($sock);
        } else {
            socket_set_nonblock($sock);
        }
        while ($this->__server_listening) {
            $connection = false;
            try {
                $select = array($sock);
                $p2 = null;
                $p3 = null;
                if(socket_select($select, $p2, $p3, 0, 50)) {
                    $connection = socket_accept($sock);
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

    public static function getPeerName($sock)
    {
        return socket_getpeername($sock, $ip, $port) ? "{$ip}:{$port}" : false;
    }

    private static function handle_client($sock, $connection)
    {

    }

    public static function sendDataTo($sock, $data, $length = null, $force = false)
    {
        if($force) {
            for($attempt = 10; $attempt > 0; $attempt--) {
                try {
                    return socket_send($sock, $data, is_null($length) ? strlen($data) : $length, 0);
                } catch(\Exception $e) {
                    usleep(100);
                }
            }
        } else {
            return socket_send($sock, $data, strlen($data), 0);
        }
    }

    public static function writeDataTo($sock, $data, $length = null, $force = false)
    {
        if($force) {
            for($attempt = 10; $attempt > 0; $attempt--) {
                try {
                    return socket_write($sock, $data, is_null($length) ? strlen($data) : $length);
                } catch(\Exception $e) {
                    usleep(100);
                }
            }
        } else {
            return socket_write($sock, $data, strlen($data));
        }
    }

    public static function shutdown($sock)
    {
        socket_close($sock);
        return true;
    }

    public static function recvDataFrom($sock, $length, $wait = false)
    {
        socket_recv($sock, $data, $length, $wait ? MSG_WAITALL : MSG_DONTWAIT);
        return $data;
    }

    public static function readDataFrom($sock, $length, $binary = true)
    {
        return socket_read($sock, $length, $binary ? PHP_BINARY_READ : PHP_NORMAL_READ);
    }

    public static function setBlocking($sock, $isBlocked = true)
    {
        return $isBlocked ? socket_set_block($sock) : socket_set_nonblock($sock);
    }

    public static function getDeadClients($clients)
    {
        $read = array();
        foreach($clients as $key => $client) {
            $read[$key] = $client;
        }
        $write = null;
        $except = null;
        if(!empty($read)) {
            socket_select($read, $write, $except, 0, 50);
        }
        return $read;
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