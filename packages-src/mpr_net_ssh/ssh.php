<?php
namespace mpr\net;

class ssh
{
    const SSH_RSA = 1;
    const SSH_DSS = 2;

    /**
    * Хост, к которому производится подключение
    *
    * @var mixed
    */
    private $host;
    private $currentConnection; // Текущее соедиение
    private $connections=array(); // Массив открытых соединений
    private $port;
    private $username;
    private $key_password;
    public $key_public;
    public $key_private;
    private $host_key = null;

    public function __construct($host, $port, $username, $key_password, $key_public, $key_private, $host_key = self::SSH_RSA)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->key_password = $key_password;
        $this->key_public = $key_public;
        $this->key_private = $key_private;
        if ($host_key == self::SSH_RSA) {
            $this->host_key = 'ssh-rsa';
        }
        if ($host_key == self::SSH_DSS) {
            $this->host_key = 'ssh-dss';
        }
    }

    public function connect()
    {
        $md5Host = md5($this->host);
        if (!isset($this->connections[$md5Host])) {
            $connection = ssh2_connect($this->host, $this->port, array('hostkey' => $this->host_key));
            ssh2_auth_pubkey_file($connection, $this->username,
                          $this->key_public,
                          $this->key_private, $this->key_password);
            $this->connections[$md5Host] = $connection;
        }
        $this->currentConnection = $this->connections[$md5Host];
        return $this;
    }

    public function execute($command, $interactive = false)
    {
        if($interactive) {
            $stream = ssh2_shell($this->currentConnection, 'xterm', null, 120, 24, SSH2_TERM_UNIT_CHARS);
            fwrite($stream, $command . PHP_EOL);
            sleep(1);
        } else {
            $stream = ssh2_exec($this->currentConnection, $command);
        }
        $content = $this->getStreamContent($stream);
        fclose($stream);
        return $content;
    }

    public function upload($local_path, $remote_path)
    {
        if (!file_exists($local_path)) { return $local_path." - File not exists!"; }
        if (ssh2_scp_send($this->currentConnection, $local_path, $remote_path) === false) {
            return "Can't send file ".$local_path." to ".$remote_path.". Cancelled.";
        }
        return "OK";
    }

    private function getStreamContent($stream)
    {
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        $content = stream_get_contents($stream);
        $content .= stream_get_contents($errorStream);

        return trim($content);
    }
}