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
     *
     * @param bool $parseAll
     */
    public function __construct($parseAll = false)
    {
        $this->parseInput($parseAll);
    }

    /**
     * Get current HTTP method in lower case
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return mb_strtolower($this->httpMethod);
    }

    /**
     * Get input params
     *
     * @return array
     */
    public function getInputParams()
    {
        return $this->httpParams;
    }

    /**
     * Parse request method and params
     *
     * @return bool
     */
    protected function parseInput($all = false)
    {
        if(isset($_SERVER['REQUEST_METHOD'])) {
            $this->httpMethod = mb_strtolower($_SERVER['REQUEST_METHOD']);
        } elseif(PHP_SAPI == 'cli') {
            $this->httpMethod = 'cli';
        } else {
            $this->httpMethod = 'get';
        }
        if($all) {
            $this->httpParams = [];
            foreach(explode('&', file_get_contents('php://input')) as $pair) {
                $item = explode('=', $pair);
                if(count($item) == 2) {
                    $this->httpParams[urldecode($item[0])] = urldecode($item[1]);
                }
            }
            $this->httpParams = array_merge(
                $_REQUEST, $_GET, $_POST, $this->httpParams, toolkit::getInstance()->getInput()->export()
            );
        } else {
            switch ($this->httpMethod) {
                case 'post':
                    $this->httpParams = $_POST;
                    break;
                case 'get':
                    $this->httpParams = $_GET;
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
                default:
                case 'cli':
                    $this->httpParams = toolkit::getInstance()->getInput()->export();
                    if(isset($this->httpParams['httpMethod'])) {
                        $this->httpMethod = $this->httpParams['httpMethod'];
                    }
                    break;

            }
        }
        return true;
    }
}