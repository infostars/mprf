<?php
/**
 * @author greevex
 * @date: 11/16/12 5:11 PM
 */

\mpr\config::$package['mpr_cache_memcached'] = [
    'default' => [
        'servers' => [
            [
                'host' => 'localhost',
                'port' => 11211
            ]
        ]
    ]
];