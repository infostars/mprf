<?php
/**
 * @author greevex
 * @date: 9/20/12 4:27 PM
 */

\mpr\config::$package['symfony_component_ClassLoader'] = [
    'register' => [

    ]
];

\mpr\config::$package['mpr_debug_log'] = [
    'enabled' => true,
    'output' => 'stdout', // "stdout"|"output"|null
    'logfile' => "/tmp/mylog.log",
];

\mpr\config::$package['mpr_db_mongoDb'] = [
    'host' => '127.0.0.1',
    'dbname' => 'test'
];

\mpr\config::$package['mpr_cache_factory'] = [
    'default' => 'mpr_cache_memcached' // driver package name
];

\mpr\config::$package['mpr_cache_memcached'] = [
    'servers' => [
        [ 'host' => '127.0.0.1', 'port' => 11211 ]
    ]
];
