<?php
namespace mpr\net;

    /**
     *
     */
/**
 *
 */
class socketServer
{
    /**
     * Is server listening flag
     *
     * @var bool
     */
    private $__server_listening = true;

    /**
     * Server ip address or host to bind to
     *
     * @var string
     */
    private $host = '127.0.0.1';

    /**
     * Port number
     *
     * @var int
     */
    private $port = 65455;

    /**
     * Main cycle pause in microseconds
     *
     * @var int
     */
    private $pause = 1000;

    /**
     * On connect callback
     *
     * @var callable
     */
    private $onConnect;

    /**
     * On every cycle of main cycle callback
     *
     * @var callable
     */
    private $onEveryCycle;

    /**
     * Name of server for log
     *
     * @var string
     */
    private $name = "SockServ";

    /**
     * Is server blocking flag
     *
     * @var bool
     */
    private $blocking = false;

    /**
     * Read timeout
     *
     * @var int
     */
    private $timeout_read = 1;

    /**
     * Write timeout
     *
     * @var int
     */
    private $timeout_write = 1;

    /**
     * Set read timeout in seconds
     *
     * @param $timeout_read
     * @return int timeout seconds
     */
    public function setReadTimeout($timeout_read)
    {
        return $this->timeout_read = intval($timeout_read);
    }

    /**
     * Set write timeout in seconds
     *
     * @param $timeout_write
     * @return int timeout seconds
     */
    public function setWriteTimeout($timeout_write)
    {
        return $this->timeout_write = intval($timeout_write);
    }

    /**
     * Set server blocking or non-blocking
     *
     * @param bool $isBlocking
     */
    public function serverBlocking($isBlocking)
    {
        $this->blocking = (bool)$isBlocking;
    }

    /**
     * Set server name for log
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = strval($name);
    }

    /**
     * Construct a new socketServer
     * Default host is 127.0.0.1
     * Default port is 65455
     *
     * @param string $host
     * @param int $port
     */
    public function __construct($host = null, $port = null)
    {
        if($host !== null) {
            $this->setHost($host);
        }
        if($port !== null) {
            $this->setPort($port);
        }
        $this->setOnConnect([$this, 'handle_client']);
        $this->setOnEveryCycle([$this, 'handle_every_cycle']);
        ob_implicit_flush();
    }

    /**
     * Start server loop
     */
    public function start()
    {
        echo "Starting socket server [{$this->name}] on {$this->getHost()}:{$this->getPort()}\n";
        $this->loop();
    }

    /**
     * Creates a server socket and listens for incoming client connections
     * Infinite cycle
     */
    private function loop()
    {
        $try_counter = 0;
        do {
            $sock = false;
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

    /**
     * Get peer name by peer socket resource
     *
     * @param resource $sock
     * @return bool|string false on error or (string)ip:port on success
     */
    public static function getPeerName($sock)
    {
        return socket_getpeername($sock, $ip, $port) ? "{$ip}:{$port}" : false;
    }

    /**
     * Handle client example callback
     *
     * @param resource $sock client socket
     * @param resource $connection base connection
     */
    private static function handle_client($sock, $connection)
    {
        echo "Received data: " . json_encode([$sock, $connection]) . PHP_EOL;
    }

    /**
     * Send data to client
     *
     * @param resource $sock
     * @param string $data
     * @param null|int $length
     * @param bool $force
     * @return int bytes written
     */
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

    /**
     * Write data to socket
     *
     * @param resource $sock
     * @param string $data
     * @param null|int $length
     * @param bool $force
     * @return int bytes written
     */
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
        return 0;
    }

    /**
     * Shutdown socket
     *
     * @param resource $sock
     * @return bool
     */
    public static function shutdown($sock)
    {
        socket_close($sock);
        return true;
    }

    /**
     * Receive data from socket
     *
     * @param resource $sock
     * @param int $length
     * @param bool $wait
     * @return mixed Received data
     */
    public static function recvDataFrom($sock, $length, $wait = false)
    {
        socket_recv($sock, $data, $length, $wait ? MSG_WAITALL : MSG_DONTWAIT);
        return $data;
    }

    /**
     * Read data from socket
     *
     * @param resource $sock
     * @param int $length
     * @param bool $binary use binary-safe read
     * @return string
     */
    public static function readDataFrom($sock, $length, $binary = true)
    {
        return socket_read($sock, $length, $binary ? PHP_BINARY_READ : PHP_NORMAL_READ);
    }

    /**
     * Set connection blocked or non-blocked
     *
     * @param resource $sock
     * @param bool $isBlocked
     * @return bool is blocked
     */
    public static function setBlocking($sock, $isBlocked = true)
    {
        return $isBlocked ? socket_set_block($sock) : socket_set_nonblock($sock);
    }

    /**
     * Get clients that are dead
     *
     * @param array $clients
     * @return array dead client resources
     */
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

    /**
     * Example of every cycle callback
     */
    private function handle_every_cycle()
    {
        echo '.';
    }

    /**
     * Set host to bind to
     *
     * @param string $host ip-address or host domain
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Get host to bind to
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set port to bind to
     *
     * @param int $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Get port to bind to
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set pause for every cycle
     *
     * @param int $pause seconds
     */
    public function setPause($pause)
    {
        $this->pause = $pause;
    }

    /**
     * Get pause for every cycle
     *
     * @return int seconds
     */
    public function getPause()
    {
        return $this->pause;
    }

    /**
     * Set on new client callback
     *
     * @param callable $onConnect
     */
    public function setOnConnect($onConnect)
    {
        $this->onConnect = $onConnect;
    }

    /**
     * Set on every cycle callback
     *
     * @param callable $onEveryCycle
     */
    public function setOnEveryCycle($onEveryCycle)
    {
        $this->onEveryCycle = $onEveryCycle;
    }
}