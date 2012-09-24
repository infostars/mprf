<?php
namespace mpr\io;

/**
 * Input
 *
 * @author GreeveX <greevex@gmail.com>
 */
class input
{

    const SC_GET = '_GET';
    const SC_POST = '_POST';
    const SC_REQUEST = '_REQUEST';
    const SC_FILES = '_FILES';
    const SC_ARGV = 'argv';
    const SC_INTERNAL = 'self::$INTERNAL';
    const NOT_EXISTS = 'param_not_exists';

    private static $INTERNAL = array();

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
                $data = $_REQUEST;
                break;
        }
        if($clean) {
            self::$INTERNAL = $data;
        } else {
            self::$INTERNAL = array_merge_recursive(self::$INTERNAL, $data);
        }
    }

    public function export()
    {
        return self::$INTERNAL;
    }

    public function setArguments($array, $clean = true)
    {
        if($clean) {
            self::$INTERNAL = $array;
        } else {
            self::$INTERNAL = array_merge_recursive(self::$INTERNAL, $array);
        }
    }

    private function getParam($name, $request_type)
    {
        $req = $request_type == self::SC_INTERNAL ? self::$INTERNAL : ${$request_type};
        return isset($req[$name]) ?
                        $req[$name] : self::NOT_EXISTS;
    }

    public function getString($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return strval($param);
    }

    public function getBoolean($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return (bool)($param == true || $param == 'true');
    }

    public function getInteger($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return intval($param);
    }

    public function getFloat($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return floatval($param);
    }

    public function getArray($name, $request_type = self::SC_INTERNAL)
    {
        $param = $this->getParam($name, $request_type);
        if($param === self::NOT_EXISTS) {
            return null;
        }
        return (array)$param;
    }
}