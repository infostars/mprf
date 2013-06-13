<?php

namespace mpr\app;

use \mpr\system\application;
use \mpr\view\smarty;
use \mpr\config;
use \mpr\io\httpInput;

/**
 * Web application for template checking
 */
class web
    extends application
{

    /**
     * @see \mpr\interfaces\web
     * @var \mpr\interfaces\web
     */
    protected $appClass;

    /**
     * Handle function
     *
     * @throws \Exception
     * @return bool
     */
    protected function handle()
    {
        $input = self::parseInput();

        $classImplemention = trim(config::getClassName('mpr_interfaces_web'), '\\');
        $class = config::getClassName($input['class']);
        if(!class_exists($class) || !in_array($classImplemention, class_implements($class))) {
            $debug = [
                'class' => $class,
                'exists' => class_exists($class),
                'implements' => class_implements($class)
            ];
            throw new \Exception("Invalid web application class `{$class}`!\n" . json_encode($debug, JSON_UNESCAPED_UNICODE));
        }

        $this->appClass = new $class();

        $routings = $this->appClass->getRoutings();



        if(!isset($routings[$input['path']][$input['method']])) {
            throw new \Exception("Routing not found: {$input['method']} {$input['path']}!");
        }

        $routing = $routings[$input['path']][$input['method']];

        if(!$this->checkRequiredParams($routing['required'], $input['params'])) {
            throw new \Exception("Required params doesn't passed!");
        }

        return $this->render($routing['call'], $routing['tpl'], $input['params']);
    }

    protected function render($call, $tpl, $params)
    {
        $view = new smarty();
        $view->clearAllCache();
        $view->setTemplateDir($this->appClass->getTemplateDirectory());

        if(is_array($call)) {
            $call[0] = config::getClassName($call[0]);
            $vars = call_user_func($call, $params);
        } else {
            $vars = $this->appClass->$call($params);
        }

        foreach($vars as $var => $value) {
            $view->assign($var, $value);
        }
        return $view->render($tpl, false, microtime(1));
    }

    /**
     * Parse HTTP input
     *
     * @return array
     */
    protected static function parseInput()
    {
        $input = new httpInput();
        $params = $input->getInputParams();
        $path = trim($_GET['path'], '/');
        $path_array = explode('/', $path);
        $class = array_shift($path_array);
        return [
            'class' => $class,
            'path' => '/' . implode('/', $path_array),
            'method' => strtoupper($input->getHttpMethod()),
            'params' => $params
        ];
    }

    protected function checkRequiredParams($required, $params)
    {
        foreach($required as $field => $pattern) {
            if(!isset($params[$field]) || !preg_match($pattern,$params[$field])) {
                return false;
            }
        }
        return true;
    }

}