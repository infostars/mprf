<?php

namespace mpr\net;

/**
 * HTTP Request class
 */
class request
{
    /**
     * @var object $instance
     */
    protected $instance;

    /**
     * Loading driver
     *
     * @param string $driver - curl | httpRequest
     */
    public function __construct($driver = 'curl')
    {
        $classname = "\\mpr\\net\\{$driver}";
        $this->instance = new $classname();
    }

    /**
     * Make request
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @return mixed
     */
    public function makeRequest($url, $params = [], $method = 'GET')
    {
        $this->instance->reset();
        $this->instance->prepare($url, $params, $method);

        return $this->instance->execute();
    }

    /**
     * Set connection timeout
     *
     * @param int $seconds
     */
    public function setConnectTimeout($seconds)
    {
        $this->instance->setConnectTimeout($seconds);
    }

    /**
     * Set get request timeout
     *
     * @param int $seconds
     */
    public function setTimeout($seconds)
    {
        $this->instance->setTimeout($seconds);
    }
}