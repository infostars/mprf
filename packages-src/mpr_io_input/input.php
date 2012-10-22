<?php
namespace mpr\io;

/**
 * Input package
 *
 * Parses all availible input data from command-line interface or from web-server
 *
 * @author GreeveX <greevex@gmail.com>
 */
class input
{

    /**
     * Look-up in $_GET super-global var
     */
    const SC_GET = '_GET';

    /**
     * Look-up in $_POST super-global var
     */
    const SC_POST = '_POST';

    /**
     * Look-up in $_REQUEST super-global var
     */
    const SC_REQUEST = '_REQUEST';

    /**
     * Look-up in $_FILES super-global var
     */
    const SC_FILES = '_FILES';

    /**
     * Look-up in command-line arguments
     */
    const SC_ARGV = 'argv';

    /**
     * Look-up in internal parsed data
     */
    const SC_INTERNAL = 'self::$INTERNAL';

    /**
     * Param isn't exists
     */
    const NOT_EXISTS = 'param_not_exists';

    /**
     * Internal parsed data
     *
     * @var array
     */
    private static $INTERNAL = array();

    /**
     * Construct new input object
     *
     * If global argv var exists we'll parse it
     */
    public function __construct()
    {
        if(isset($GLOBALS['argv'])) {
            $this->setArguments($this->importArgs($GLOBALS['argv']));
        }
    }

    /**
     * Function to parse php arguments
     *
     * @param array $argv
     * @return array
     */
    public function importArgs($argv){
        array_shift($argv);
        $out = array();
        foreach ($argv as $arg){
            if (substr($arg,0,2) == '--'){
                $eqPos = strpos($arg,'=');
                if ($eqPos === false){
                    $key = substr($arg,2);
                    $out[$key] = isset($out[$key]) ? $out[$key] : true;
                } else {
                    $key = substr($arg,2,$eqPos-2);
                    $out[$key] = substr($arg,$eqPos+1);
                }
            } elseif (substr($arg,0,1) == '-'){
                if (substr($arg,2,1) == '='){
                    $key = substr($arg,1,1);
                    $out[$key] = substr($arg,3);
                } else {
                    $chars = str_split(substr($arg,1));
                    foreach ($chars as $char){
                        $key = $char;
                        $out[$key] = isset($out[$key]) ? $out[$key] : true;
                    }
                }
            } elseif (substr($arg,0,1) == ']'){
                list($key, $value) = explode('=', $arg);
                $out[$key] = $value;
            } else {
                $out[] = $arg;
            }
        }
        return $out;
    }

    /**
     * Read line from user by command-line request
     *
     * @param string $requestMessage Request message
     * @param string $defaultValue Default value will be shown to user
     * @return string User-typed data or default value
     */
    public function readLine($requestMessage = "", $defaultValue = "")
    {
        if(!empty($defaultValue)) {
            $requestMessage .= " [{$defaultValue}]: ";
        }
        $input = trim(readline($requestMessage));
        if(empty($input)) {
            $input = $defaultValue;
        }
        return $input;
    }

    /**
     * Import arguments from super-global var
     *
     * @param string $request_type Select it by constants self::SC_*
     * @param bool $clean Clear already parsed data
     */
    public function importArguments($request_type = self::SC_REQUEST, $clean = true)
    {
        switch($request_type)
        {
            case self::SC_FILES:
                $data = $_FILES;
                break;
            case self::SC_GET:
                $data = $_GET;
                break;
            case self::SC_POST:
                $data = $_POST;
                break;
            case self::SC_REQUEST:
            default:
                $data = $_REQUEST;
                break;
        }
        if($clean) {
            self::$INTERNAL = $data;
        } else {
            self::$INTERNAL = array_merge_recursive(self::$INTERNAL, $data);
        }
    }

    /**
     * Export parsed data as array
     *
     * @return array
     */
    public function export()
    {
        return self::$INTERNAL;
    }

    /**
     * Set arguments to internal parsed data
     *
     * @param array $array
     * @param bool $clean Clear already exists data
     */
    public function setArguments($array, $clean = true)
    {
        if($clean) {
            self::$INTERNAL = $array;
        } else {
            self::$INTERNAL = array_merge_recursive(self::$INTERNAL, $array);
        }
    }

    /**
     * Get param by name and request type
     *
     * @param $name
     * @param $request_type
     * @return mixed|string
     */
    private function getParam($name, $request_type)
    {
        $req = $request_type == self::SC_INTERNAL ? self::$INTERNAL : ${$request_type};
        return isset($req[$name]) ?
                        $req[$name] : self::NOT_EXISTS;
    }

    /**
     * Get string from request
     *
     * @param $name
     * @param string $request_type
     * @return null|string Null if param not exists
     */
    public function getString($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return strval($param);
    }

    /**
     * Get boolean from request
     *
     * @param $name
     * @param string $request_type
     * @return bool|null
     */
    public function getBoolean($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return (bool)($param == true || $param == 'true');
    }

    /**
     * Get integer from request
     *
     * @param $name
     * @param string $request_type
     * @return int|null Null if param not exists
     */
    public function getInteger($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return intval($param);
    }

    /**
     * Get float from request
     *
     * @param $name
     * @param string $request_type
     * @return float|null Null if param not exists
     */
    public function getFloat($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return floatval($param);
    }

    /**
     * Get array from request
     *
     * @param $name
     * @param string $request_type
     * @return array|null Null if param not exists
     */
    public function getArray($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return (array)$param;
    }
}