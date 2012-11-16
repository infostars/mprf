<?php
/**
 * Config file
 *
 * @author greevex
 * @date: 9/20/12 4:27 PM
 */

// Path to app root
define('APP_ROOT', __DIR__);

// dev | prod
$GLOBALS['APP_ENV'] = 'dev';

// src | phar
$GLOBALS['PACKAGES_TYPE'] = 'src';

$GLOBALS['PACKAGES_CACHE_EXPIRE'] = 3600;

// only if PACKAGES_TYPE = src
$GLOBALS['PACKAGES_PATH'] = [
    APP_ROOT . '/packages-src'
];