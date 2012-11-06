<?php

namespace mpr\net;

/**
 * Net client class
 */
class client
{
    /**
     * Base url for requests
     *
     * @var string
     */
    private $base_url = 'http://api.sdstream.ru/';

    /**
     * mpr\net\curl client object
     *
     * @var curl
     */
    private $client;

    /**
     * Object name.
     * `author` or `document`
     *
     * @var string
     */
    private $object;

    /**
     * Request method
     *
     * @var string
     */
    private $method = 'get';

    /**
     * Parameters array
     *
     * @var array
     */
    private $params = array();

    /**
     * Constructor
     * As an input parameter, you can pass an array of standard values
     *
     * @example array( 'base_url' => 'http://api.sdstream.ru', 'method' => 'get' )
     * @param array $config
     */
    public function __construct($config = [])
    {
        $vars = get_class_vars(__CLASS__);
        foreach($config as $var => $value) {
            if(!in_array($var, $vars)) {
                continue;
            }
            $this->{$var} = $value;
        }
        $this->client = new \mpr\net\curl();
    }

    /**
     * Set object with which to work.
     * For example: `search` or `document`
     *
     * @param string $object
     * @return \mpr\net\client
     */
    protected function setObject($object)
    {
        $this->object = strval($object);
        return $this;
    }

    /**
     * Sets the HTTP-request method.
     * For modifying queries using methods: `POST`, `PUT`, `DELETE`
     * For reading use: `GET`
     *
     * @param string $method
     * @return boolean|\mpr\net\client
     */
    protected function setMethod($method)
    {
        $method = strtolower($method);
        $this->method = $method;
        return $this;
    }

    /**
     * Set parameters for query
     *
     * @param array $params
     * @param boolean $clean
     * @return \mpr\net\client
     */
    protected function setParams($params, $clean = true)
    {
        if($clean) {
            $this->params = $params;
        } else {
            $this->params = array_merge($this->params, $params);
        }
        return $this;
    }

    /**
     * Execute query to API
     *
     * @return array JSON-decoded response from API
     */
    protected function execute()
    {
        $url = "{$this->base_url}?object={$this->object}";

        $this->client->reset();
        $this->client->prepare($url, $this->params, $this->method);
        $response = $this->client->execute();
        return json_decode($response, true);
    }

    /**
     * Call method by name and send params
     *
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function __call($method, $params = [])
    {
        $params = array_shift($params);
        $explode = explode('_', $method);
        $httpMethod = array_pop($explode);
        $object = implode('_', $explode);
        if(is_array($params)) {
            if($object == 'document') {
                $params['key'] = md5(date("Ymd"));
            }
        } else {
            $params = [];
        }

        $this->setMethod($httpMethod);
        $this->setParams($params);
        $this->setObject($object);

        return $this->execute();
    }
}