<?php

namespace mpr\net;

/**
 * Curl wrapper
 *
 * @author GreeveX <greevex@gmail.com>
 */
class curl
{
    /**
     * initialized curl resource
     *
     * @var resource
     */
    private $curl;

    /**
     * Default options array
     *
     * @var array
     */
    private $_defaultoptions = array(
        CURLOPT_VERBOSE             => 0,
        CURLOPT_RETURNTRANSFER      => true,
        CURLOPT_FOLLOWLOCATION      => 10,
        CURLOPT_SSL_VERIFYPEER      => false,
        CURLOPT_USERAGENT           => 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; en-US) AppleWebKit/534.16 (KHTML, like Gecko) Chrome/10.0.648.205 Safari/534.16',
        CURLOPT_CONNECTTIMEOUT      => 30,
        CURLOPT_TIMEOUT             => 90,
        CURLOPT_CUSTOMREQUEST       => 'GET',
        CURLOPT_HTTP_VERSION        => CURL_HTTP_VERSION_1_0,
        CURLOPT_COOKIEJAR           => '/tmp/global_cookie.sds',
        CURLOPT_COOKIEFILE          => '/tmp/global_cookie.sds',
    );

    /**
     * Current options array
     *
     * @var array
     */
    private $options = array();

    /**
     * Verbosity flag
     *
     * @param int $verbose
     * @return int verbosity
     */
    public function setVerbose($verbose = 1)
    {
        return $this->options[CURLOPT_VERBOSE] = $verbose;
    }

    /**
     * Construct new object
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Initialize new curl object and apply default options
     *
     * @return bool
     */
    public function reset()
    {
        $this->curl = curl_init();
        $this->options = $this->_defaultoptions;
        return true;
    }

    /**
     * Set file path for cookie file
     *
     * @param string $path Cookie file path
     */
    public function setCookieFile($path)
    {
        $this->options[CURLOPT_COOKIEJAR] = $path;
        $this->options[CURLOPT_COOKIEFILE] = $path;
    }

    /**
     * Select output interface
     * For example ip-address or interface name (127.0.0.1 eth0 ...)
     *
     * @param string $interface
     * @return bool
     */
    public function selectInterface($interface)
    {
        $this->options[CURLOPT_INTERFACE] = $interface;
        return bool;
    }

    /**
     * Prepare new curl request
     *
     * @param string $url
     * @param array|null $params
     * @param string|null $method null = default (GET)
     * @return resource Curl object
     */
    public function prepare($url, $params = null, $method = null)
    {
        if($method === null) {
            $method = 'GET';
        }
        $url = trim($url);
        if (is_array($params)) {
            $params = http_build_query($params);
        }
        switch(strtoupper($method)) {
            case 'GET':
                $this->options[CURLOPT_CUSTOMREQUEST] = 'GET';
                if($params !== null) {
                    $url .= strpos($url, '?') === false ? "?$params" : "&$params";
                }
                break;
            case 'PUT':
                $this->options[CURLOPT_CUSTOMREQUEST] = 'PUT';
                if($params !== null) {
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
                }
                break;
            case 'DELETE':
                $this->options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                if($params !== null) {
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
                }
                break;
            case 'POST':
                $this->options[CURLOPT_CUSTOMREQUEST] = 'POST';
                if($params !== null) {
                    curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
                }
                curl_setopt($this->curl, CURLOPT_POST, 1);
                break;
        }

        $this->options[CURLOPT_URL] = $url;

        curl_setopt_array($this->curl, $this->options);

        return $this->curl;
    }

    /**
     * Add some curl options to current request
     *
     * @param array $options
     * @return bool
     */
    public function addOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
        return true;
    }

    /**
     * Execute request
     *
     * @return string|bool string result (CURLOPT_RETURNTRANSFER = 1), bool (CURLOPT_RETURNTRANSFER = 0)
     * @throws CurlException
     */
    public function execute()
    {
        $result = curl_exec($this->curl);

        $curl_errno = curl_errno($this->curl);

        if ($curl_errno != CURLE_OK) {
            if($curl_errno == CURLE_COULDNT_CONNECT) {
                $this->setConnectTimeout(2);
            }
            $curl_error = curl_error($this->curl);
            if(strpos($curl_error, 'bind') !== false) {
                static $default_ip;
                if($default_ip == null) {
                    $default_ip = trim(shell_exec("ip a s | grep 'inet' | grep -v '127.0.0' | grep -v '::' | awk '{print $2}' | sed 's#/[0-9][0-9]##' | head -n 1"));
                }
                $this->selectInterface($default_ip);
                $result = $this->execute();
            } else {
                curl_close($this->curl);
                $this->curl = null;
                throw new CurlException($curl_error, $curl_errno);
            }
        }

        return $result;
    }

    /**
     * Set connect timeout seconds
     *
     * @param $seconds
     */
    public function setConnectTimeout($seconds)
    {
        $this->options[CURLOPT_CONNECTTIMEOUT] = intval($seconds);
    }

    /**
     * Set timeout seconds
     *
     * @param $seconds
     */
    public function setTimeout($seconds)
    {
        $this->options[CURLOPT_TIMEOUT] = intval($seconds);
    }
}

/**
 * Curl exception object
 */
class CurlException extends \Exception {}
