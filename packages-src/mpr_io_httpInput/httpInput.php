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
        $input_str = file_get_contents('php://input');
        $contentType = strtolower($_SERVER['CONTENT_TYPE']);
        $jsonParams = [];
        if ($contentType === 'application/json') {
            $jsonParams = (array)json_decode($input_str, true);
        }
        if($all) {
            $items = [];
            if (empty($jsonParams)) {
                parse_str($input_str, $items);
            }
            $this->httpParams = array_merge(
                $_REQUEST, $_GET, $_POST, toolkit::getInstance()->getInput()->export(), $items, $jsonParams
            );
        } else {
            switch ($this->httpMethod) {
                case 'get':
                    $this->httpParams = $_GET;
                    break;
                case 'post':
                    $this->httpParams = !empty($jsonParams) ? $jsonParams : $_POST;
                    break;
                case 'cli':
                    $this->httpParams = toolkit::getInstance()->getInput()->export();
                    if(isset($this->httpParams['httpMethod'])) {
                        $this->httpMethod = $this->httpParams['httpMethod'];
                    }
                    break;
                case 'put':
                case 'delete':
                default:
                    parse_str($input_str, $items);
                    $this->httpParams = $items;
                    break;

            }
        }
        return true;
    }
}