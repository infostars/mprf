<?php
namespace mpr\io;

use \mpr\toolkit;

/**
 * HTTP input package
 *
 * Parses input params and methods
 * Usefull for REST.
 *
 * @author GreeveX <greevex@gmail.com>
 */
class httpInput
{
    /**
     * Request HTTP Method
     *
     * @var string
     */
    protected $httpMethod = 'GET';

    /**
     * Request HTTP arguments
     *
     * @var array
     */
    protected $httpParams = [];

    /**
     * Construct new HTTP request parser and parse input
     */
    public function __construct()
    {
        $this->parseInput();
    }

    /**
     * Get current HTTP method
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Get input params
     *
     * @return array
     */
    public function getInputParams()
    {
        return toolkit::getInstance()->getInput()->export();
    }

    /**
     * Parse request method and params
     *
     * @return bool
     */
    protected function parseInput()
    {
        if(isset($_SERVER['REQUEST_METHOD'])) {
            $this->httpMethod = mb_strtolower($_SERVER['REQUEST_METHOD']);
        } elseif(PHP_SAPI == 'cli') {
            $this->httpMethod = 'cli';
        } else {
            $this->httpMethod = 'get';
        }
        switch ($this->httpMethod) {
            case 'post':
                $this->httpParams = $_POST;
                break;
            case 'get':
                $this->httpParams = $_GET;
                break;
            case 'cli':
                $this->httpParams = toolkit::getInstance()->getInput()->export();
                $this->httpMethod = isset($this->httpParams['m']) ? $this->httpParams : 'get';
                break;
            case 'put':
            case 'delete':
                foreach(explode('&', file_get_contents('php://input')) as $pair) {
                    $item = explode('=', $pair);
                    if(count($item) == 2) {
                        $this->httpParams[urldecode($item[0])] = urldecode($item[1]);
                    }
                }
                break;

        }
        return true;
    }
}