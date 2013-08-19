<?php

namespace mpr\net;

use \mpr\debug\log;

/**
 * Socket client class
 *
 */
class socketClient
{
    /**
     * Host param
     * default '127.0.0.1'
     *
     * @var string
     */
    private $host = '127.0.0.1';

    /**
     * Port param
     * default 65455
     *
     * @var int
     */
    private $port = 65455;

    /**
     * Pause param
     * default 1000 microseconds
     *
     * @var int
     */
    private $pause = 1000;

    /**
     * Call function if connect successful
     *
     * @var callable
     */
    private $onConnect;

    /**
     * Call function if connect successful
     *
     * @var callable
     */
    private $onEveryCycle;

    /**
     * Socket client name
     *
     * @var string
     */
    private $name = "SockClient";

    /**
     * ???
     *
     * @var
     */
    private $sock;

    /**
     * Timeout on reading
     *
     * @var int
     */
    private $timeout_read = 1;

    /**
     * Timeout on writing
     *
     * @var int
     */
    private $timeout_write = 1;

    /**
     * Set socket client name
     *
     * @param string $name
     * @return string
     */
    public function setName($name)
    {
        return $this->name = $name;
    }

    /**
     * Initialize socket client object with host and port params
     *
     * @param string|null $host
     * @param int|null $port
     */
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

    /**
     * Set reading timeout
     *
     * @param int $timeout_read
     * @return int
     */
    public function setReadTimeout($timeout_read)
    {
        return $this->timeout_read = $timeout_read;
    }

    /**
     * Set writing timeout
     *
     * @param int $timeout_write
     * @return int
     */
    public function setWriteTimeout($timeout_write)
    {
        return $this->timeout_write = $timeout_write;
    }

    /**
     * Close connection
     *
     * @return bool
     */
    public function disconnect()
    {
        try {
            return $this->shutdown();
        } catch(\Exception $e) {
            // Already down
            log::put("Already down", __METHOD__);
        }
        return true;
    }

    /**
     * Try to reconnect
     *
     * @return bool
     */
    public function reconnect()
    {
        $this->disconnect();
        $this->connect();

        return true;
    }

    /**
     * Disconnect when object destructed
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Check if socket client object connected
     *
     * @return bool
     */
    public function isConnected()
    {
        if(strtolower(get_resource_type($this->sock)) != 'socket') {
            return false;
        }
        $read = array($this->sock);
        $write = array();
        $except = array();
        if(socket_select($read, $write, $except, 0, 100)) {
            if(count($read)) {
                try {
                    if(!$this->recvData(1, false, true)) {
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
     * @return bool
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

        return $connected;
    }

    /**
     * Get peer name
     *
     * @return bool|string
     */
    public function getPeerName()
    {
        return socket_getpeername($this->sock, $ip, $port) ? "{$ip}:{$port}" : false;
    }

    /**
     * Send data in socket and return how much bites we are sent
     *
     * @param string $data
     * @param null $length
     * @param bool $force
     * @return int|bool how much bites we are sent
     */
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

        return false;
    }

    /**
     * Write data in socket and return how much bites we are write
     *
     * @param string $data
     * @param null $length
     * @param bool $force
     * @return int|bool how much bites we are write
     */
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

        return false;
    }

    /**
     * Shutdown connection on socket
     *
     * @return bool
     */
    public function shutdown()
    {
        if(strtolower(get_resource_type($this->sock)) != 'socket') {
            socket_close($this->sock);
        }
        return true;
    }

    /**
     * Get data from a connected socket
     *
     * @param int  $length
     * @param bool $wait
     * @param bool $peek
     *
     * @return mixed
     */
    public function recvData($length, $wait = false, $peek = false)
    {
        $flags = $wait ? MSG_WAITALL : MSG_DONTWAIT;
        $flags |= ($peek) ? MSG_PEEK : 0;
        socket_recv($this->sock, $data, $length, $flags);
        return $data;
    }

    /**
     * Reads a string of bytes maximum length length of the socket
     *
     * @param int $length
     * @param bool $binary
     * @return string
     */
    public function readData($length, $binary = true)
    {
        return socket_read($this->sock, $length, $binary ? PHP_BINARY_READ : PHP_NORMAL_READ);
    }

    /**
     * Sets blocking or unblocking mode on a socket resource
     *
     * @param bool $isBlocked
     * @return bool
     */
    public function setBlocking($isBlocked = true)
    {
        return $isBlocked ? socket_set_block($this->sock) : socket_set_nonblock($this->sock);
    }

    /**
     * Set host
     *
     * @param string $host
     * @return string $host
     */
    public function setHost($host)
    {
        return $this->host = $host;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set port
     *
     * @param int $port
     * @return int $port
     */
    public function setPort($port)
    {
        return $this->port = $port;
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set pause in microseconds
     *
     * @param int $pause
     * @return int $pause
     */
    public function setPause($pause)
    {
        return $this->pause = $pause;
    }

    /**
     * Get pause in microseconds
     *
     * @return int
     */
    public function getPause()
    {
        return $this->pause;
    }

    /**
     * Set on connect callable function
     *
     * @param callable $onConnect
     * @return bool
     */
    public function setOnConnect($onConnect)
    {
        $this->onConnect = $onConnect;
        return true;
    }

    /**
     * Set callable function to call on every cycle
     *
     * @param callable $onEveryCycle
     * @return bool
     */
    public function setOnEveryCycle($onEveryCycle)
    {
        $this->onEveryCycle = $onEveryCycle;
        return true;
    }
}