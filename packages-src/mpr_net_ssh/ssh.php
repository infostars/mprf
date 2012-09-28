<?php
namespace mpr\net;

class ssh
{
    const SSH_RSA = 1;
    const SSH_DSS = 2;

    private $host;
    private $port;
    private $username;
    private $key_password;
    public $key_public;
    public $key_private;
    private $connection; // Текущее соедиение
    private $host_key = 'ssh-rsa';

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
        $this->connection = ssh2_connect($this->host, $this->port, array('hostkey' => $this->host_key));
        if(!empty($this->key_password)) {
            ssh2_auth_pubkey_file($this->connection, $this->username, $this->key_public, $this->key_private, $this->key_password);
        } else {
            ssh2_auth_pubkey_file($this->connection, $this->username, $this->key_public, $this->key_private);
        }
        return $this;
    }

    public function executeAsync($command)
    {
        static $stream;
        if($stream == null) {
            $stream = ssh2_shell($this->connection, 'xterm', null, 120, 24, SSH2_TERM_UNIT_CHARS);
            stream_set_blocking($stream, true);
        }
        fwrite($stream, $command . PHP_EOL);
        return $stream;
    }

    public function execute($command)
    {
        $stream = ssh2_exec($this->connection, $command);
        stream_set_blocking($stream, true);
        $content = $this->getStreamContent($stream);
        fclose($stream);
        return $content;
    }

    public function upload($local_path, $remote_path)
    {
        if (!file_exists($local_path)) { return $local_path." - File not exists!"; }
        if (ssh2_scp_send($this->connection, $local_path, $remote_path) === false) {
            return "Can't send file ".$local_path." to ".$remote_path.". Cancelled.";
        }
        return "OK";
    }

    public function getStreamContent(&$stream)
    {
        $content = "";
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        while(is_resource($errorStream) && !feof($errorStream)) {
            $content .= stream_get_line($stream, 4096);
        }
        while(is_resource($stream) && !feof($stream)) {
            $content .= stream_get_line($stream, 4096);
        }

        return $content;
    }
}