<?php
/**
 * @author greevex
 * @date: 11/16/12 5:11 PM
 */

\mpr\config::$package['mpr_cache_redis'] = [
    'default' => [
        'server' => [
            'host' => 'memcached01.sdstream.ru',
            'port' => 6379,
            'timeout' => 0,
            'prefix' => 'p:' //d = dev, p = prod
        ]
    ],
    'r01' => [
        'server' => [
            'host' => 'localhost',
            'port' => 6379,
            'timeout' => 0,
            'prefix' => 'p:' //d = dev, p = prod
        ]
    ],
    'r02' => [
        'server' => [
            'host' => '192.168.10.37',
            'port' => 6379,
            'timeout' => 0,
            'prefix' => 'p:' //d = dev, p = prod
        ]
    ]
];