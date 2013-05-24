<?php
namespace mpr\net;

/**
 * Stream socket server class
 *
 * @author Ostrovskiy Grigoriy <greevex@gmail.com>
 */
class streamSocketServer
{
    /**
     * is server listening flag
     *
     * @var bool
     */
    private $__server_listening = true;

    /**
     * Host to bind to
     *
     * @var string
     */
    private $host = '127.0.0.1';

    /**
     * Port to bind to
     *
     * @var int
     */
    private $port = 65455;

    /**
     * Pause on every cycle
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
     * On every cycle
     *
     * @var callable
     */
    private $onEveryCycle;

    /**
     * Server name for log
     *
     * @var string
     */
    private $name = "SockServ";

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
     * Create new stream socket server
     * Default host is 127.0.0.1
     * Default port is 65455
     *
     * @param null|string $host
     * @param null|int $port
     */
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

    /**
     * Start server cycle
     */
    public function start()
    {
        echo "Starting socket server [{$this->name}] on {$this->getHost()}:{$this->getPort()}\n";
        $this->loop();
    }

    /**
     * Creates a server socket and listens for incoming client connections
     *
     */
    private function loop()
    {
        $sock = false;
        $try_counter = 0;
        do {
            try {
                $try_counter++;
                $sock = stream_socket_server("tcp://{$this->getHost()}:{$this->getPort()}");
            } catch(\Exception $e) {
                echo "Error! {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n";
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