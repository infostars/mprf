<?php
/**
 * @author greevex
 * @date: 11/16/12 5:11 PM
 */

\mpr\config::$package['mpr_cache_redis'] = [
    'server' => [
        //'host' => 'memcached01.sdstream.ru',
        'host' => 'localhost',
        'port' => 6379,
        'timeout' => 0,
        // 'prefix' => 'p:' //d = dev, p = prod
        'prefix' => 'd:' //d = dev, p = prod
    ]
];