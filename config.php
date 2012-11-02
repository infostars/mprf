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

\mpr\config::$package['mpr_cache'] = [
    'default' => 'mpr_cache_memcached' // driver package name
];

\mpr\config::$package['mpr_cache_memcached'] = [
    'servers' => [
        [ 'host' => '127.0.0.1', 'port' => 11211 ]
    ]
];

\mpr\config::$package['mpr_cache_redis'] = [
    'server' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'timeout' => 0,
        'prefix' => 'test:'
    ]
];

\mpr\config::$package['mpr_cache_file'] = [
    'cache_dir' => '/tmp/'
];

\mpr\config::$package['mpr_net_gearmanClient'] = [
    'default' => [
        'host' => 'mongo.hostname',
        //'port' => 1234 //optional
    ]
];