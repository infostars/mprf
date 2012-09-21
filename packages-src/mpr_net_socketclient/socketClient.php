<?php
namespace mpr\net;

use \mpr\debug\log;

/**
 *
 */
class socketClient
{
    private $host = '127.0.0.1';
    private $port = 65455;
    private $pause = 1000;
    private $onConnect;
    private $onEveryCycle;
    private $name = "SockClient";
    private $sock;
    private $timeout_read = 1;
    private $timeout_write = 1;

    public function setName($name)
    {
        $this->name = $name;
    }

    public function __construct($host = null, $port = null)
    {
        if(!is_null($host)) {
            $this->setHost($host);
        }
        if(!is_null($port)) {
            $this->setPort($port);
        }
        ob_implicit_flush();
    }

    public function setReadTimeout($timeout_read)
    {
        $this->timeout_read = $timeout_read;
    }

    public function setWriteTimeout($timeout_write)
    {
        $this->timeout_write = $timeout_write;
    }

    public function disconnect()
    {
        try {
            $this->shutdown();
        } catch(\Exception $e) {
            // Already down
        }
    }

    public function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    public function __destruct()
    {
        if($this->sock != null) {
            $this->disconnect();
        }
    }

    public function isConnected()
    {
        $read = array($this->sock);
        $write = array();
        $except = array();
        if(socket_select($read, $write, $except, 0, 100)) {
            if(count($read)) {
                try {
                    if(!$this->recvData(1, false)) {
                        $this->shutdown();
                        return false;
                    }
                } catch(\Exception $e) {
                    return false;
                }
            } else {
                return true;
            }
        }
        return true;
    }

    /**
     * Creates a server socket and listens for incoming client connections
     *
     */
    public function connect()
    {
        $connected = false;
        for($attempt = 5; $attempt > 0 && !$connected; $attempt--) {
            try {
                $this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
                if(!$this->sock) {
                    log::put("Error Creating Socket: " . socket_strerror(socket_last_error()), __METHOD__);
                }
                socket_set_option($this->sock, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec" => 0));
                socket_connect($this->sock, $this->host, $this->port);
                if(!$this->sock) {
                    log::put("Unable to connect to Socket: " . socket_strerror(socket_last_error()), __METHOD__);
                }
                $connected = true;
            } catch(\Exception $e) {
                log::put("Error! Code: {$e->getCode()} / Message: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", __METHOD__);
            }
        }
        log::put("Client [{$this->name}] created![{$attempt}]", __METHOD__);
        self::setBlocking($this->sock, false);
    }

    public function getPeerName()
    {
        return socket_getpeername($this->sock, $ip, $port) ? "{$ip}:{$port}" : false;
    }

    public function sendData($data, $length = null, $force = false)
    {
        if($force) {
            for($attempt = 10; $attempt > 0; $attempt--) {
                try {
                    return socket_send($this->sock, $data, is_null($length) ? strlen($data) : $length, 0);
                } catch(\Exception $e) {
                    usleep(100);
                }
            }
        } else {
            return socket_send($this->sock, $data, strlen($data), 0);
        }
    }

    public function writeData($data, $length = null, $force = false)
    {
        if($force) {
            for($attempt = 10; $attempt > 0; $attempt--) {
                try {
                    return socket_write($this->sock, $data, is_null($length) ? strlen($data) : $length);
                } catch(\Exception $e) {
                    usleep(100);
                }
            }
        } else {
            return socket_write($this->sock, $data, strlen($data));
        }
    }

    public function shutdown()
    {
        socket_close($this->sock);
        return true;
    }

    public function recvData($length, $wait = false)
    {
        socket_recv($this->sock, $data, $length, $wait ? MSG_WAITALL : MSG_DONTWAIT);
        return $data;
    }

    public function readData($length, $binary = true)
    {
        return socket_read($this->sock, $length, $binary ? PHP_BINARY_READ : PHP_NORMAL_READ);
    }

    public function setBlocking($isBlocked = true)
    {
        return $isBlocked ? socket_set_block($this->sock) : socket_set_nonblock($this->sock);
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