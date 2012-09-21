<?php
namespace mpr\view;

use mpr\config;

require_once __DIR__ . '/Smarty/libs/Smarty.class.php';

class smarty
extends \Smarty
{
    public function __construct()
    {
        parent::__construct();
        define(SMARTY_MBSTRING, true);
        $options = config::getPackageConfig(__CLASS__);
        $this->smarty->force_compile = $options['force_compile'];
        $this->smarty->debugging = $options['debugging'];
        $this->smarty->caching = $options['caching'];;
        $this->smarty->cache_lifetime   = $options['cache_lifetime'];
        $this->smarty->setCompileDir($options['compile_dir']);
        $this->smarty->setConfigDir($options['config_dir']);
        $this->smarty->setCacheDir($options['cache_dir']);
    }

    public function render($template, $asString = true, $cache_id = null)
    {
        if($asString) {
            return $this->smarty->fetch($template, $cache_id);
        } else {
            $this->smarty->display($template, $cache_id);
            return true;
        }
    }
}