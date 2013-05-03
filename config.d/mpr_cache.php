<?php
/**
 * @author greevex
 * @date: 11/16/12 5:11 PM
 */

\mpr\config::$package['mpr_cache'] = [
    // driver package name
    'redis' => [
        'driver' => 'mpr_cache_redis',
        'config_section' => 'default'
    ],
    'redis01' => [
        'driver' => 'mpr_cache_redis',
        'config_section' => 'r01'
    ],
    'redis02' => [
        'driver' => 'mpr_cache_redis',
        'config_section' => 'r02'
    ],
    'memcached' => [
        'driver' => 'mpr_cache_memcached',
        'config_section' => 'default'
    ],
    'file' => [
        'driver' => 'mpr_cache_file',
        'config_section' => 'default'
    ]
];

\mpr\config::$package['mpr_cache']['default'] =& \mpr\config::$package['mpr_cache']['redis'];