<?php
namespace mpr\net;

class ssh
{
    const SSH_RSA = 1;
    const SSH_DSS = 2;

    /**
     * IP or domain to connect to
     *
     * @var string
     */
    private $host;

    /**
     * Connect to port
     *
     * @var int
     */
    private $port;

    /**
     * Username to connect to host as
     *
     * @var string
     */
    private $username;

    /**
     * Passpharse for ssh key
     *
     * @var string
     */
    private $key_password;

    /**
     * Path to public ssh key file
     *
     * @var string
     */
    public $key_public;

    /**
     * Path to private ssh key file
     *
     * @var string
     */
    public $key_private;

    /**
     * SSH connection resource
     *
     * @var resource
     */
    private $connection;

    /**
     * Host key type
     * Allowed ssh-rsa ot ssh-dsa
     *
     * @var string
     */
    private $host_key = 'ssh-rsa';

    /**
     * Build ssh connection with params
     * Note: connection wouldn't be established
     * To connect to server call connect() method
     *
     * @param string $host SSH Hostname or IP address
     * @param int $port SSH Port
     * @param string $username
     * @param string $key_password Passphrase for ssh key
     * @param string $key_public Path to public key
     * @param string $key_private Path to private key
     * @param int $host_key SSH key type (SSH_RSA or SSH_DSA)
     */
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

    /**
     * Connect to server
     *
     * @return ssh
     */
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

    /**
     * Execute command in asynchronous (non-block) mode
     *
     * @param string $command
     * @return resource Connection streaming resource
     */
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

    /**
     * Execute command and wait result (block-mode)
     *
     * @param string $command
     * @return string result
     */
    public function execute($command)
    {
        $stream = ssh2_exec($this->connection, $command);
        stream_set_blocking($stream, true);
        $content = $this->getStreamContent($stream);
        fclose($stream);
        return $content;
    }

    /**
     * Upload file to ssh server using scp
     *
     * @param string $local_path Local file source path
     * @param string $remote_path Remote file destination path
     * @return string Result. (string)"OK" - upload successful
     */
    public function upload($local_path, $remote_path)
    {
        if (!file_exists($local_path)) { return $local_path." - File not exists!"; }
        if (ssh2_scp_send($this->connection, $local_path, $remote_path) === false) {
            return "Can't send file ".$local_path." to ".$remote_path.". Cancelled.";
        }
        return "OK";
    }

    /**
     * Get content from stream resource
     *
     * @param resource $stream Ssh connection
     * @return string
     */
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