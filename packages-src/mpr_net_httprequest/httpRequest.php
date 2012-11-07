<?php

namespace mpr\net;

/**
 * NOT USED, BECAUSE HttpRequest not in the standard package, and must be installed separately
 *
 * HttpRequest driver for HTTP Request class
 */
class httpRequest
{
    /**
     *
     * @var \HttpRequest
     */
    private $httprequest;

    /**
     * Options
     *
     * @var array $options
     */
    private $options = array(
        'redirect' => 1,
        'verifypeer' => false,
        'useragent' => 'net\request v0.2',
        'connecttimeout' => 10,
        'timeout' => 60,
    );

    /**
     * Prepare new http request
     *
     * @param string $url
     * @param array $params
     * @param string $method
     * @return \HttpRequest
     */
    public function prepare($url, $params = array(), $method = 'GET')
    {
        if($this->httprequest == null) {
            $this->httprequest = new \HttpRequest();
        }
        if ($params) {
            $params = http_build_query($params);
        }

        switch($method) {
            case 'GET':
                $this->httprequest->setMethod(\HttpRequest::HTTP_METH_GET);
                $url .= strpos($url, '?') === false ? "?$params" : "&$params";
                break;
            case 'PUT':
                $this->httprequest->setMethod(\HttpRequest::HTTP_METH_PUT);
                $this->httprequest->setPutData($params);
                break;
            case 'DELETE':
                $this->httprequest->setMethod(\HttpRequest::HTTP_METH_DELETE);
                $this->httprequest->setPostFields($params);
                break;
            case 'POST':
                $this->httprequest->setMethod(\HttpRequest::HTTP_METH_POST);
                $this->httprequest->setPostFields($params);
                break;
        }

        $this->httprequest->setUrl($url);

        $this->httprequest->setOptions($this->options);

        return $this->httprequest;
    }

    /**
     * Execute request
     *
     * @return \HttpMessage
     */
    public function execute()
    {
        return $this->httprequest->send();
    }

    /**
     * Set connect timeout in seconds
     *
     * @param $seconds
     */
    public function setConnectTimeout($seconds)
    {
        $this->httprequest['connecttimeout'] = $seconds;
    }

    /**
     * Set timeout in seconds
     *
     * @param $seconds
     */
    public function setTimeout($seconds)
    {
        $this->httprequest['timeout'] = $seconds;
    }
}