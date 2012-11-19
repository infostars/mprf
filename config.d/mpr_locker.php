<?php
/**
 * @author demin
 * @date: 11/19/12 5:11 PM
 */

\mpr\config::$package['mpr_locker'] = [
    'default' => [
        // driver package name
        'driver' => 'mpr_locker_cache',
        'backend_section' => 'mpr_cache_memcached'
    ],
    'cache_memcache' => [
        // driver package name
        'driver' => 'mpr_locker_cache',
        'backend_section' => 'mpr_cache_memcached'
    ],
    'cache_redis' => [
        // driver package name
        'driver' => 'mpr_locker_cache',
        'backend_section' => 'mpr_cache_redis'
    ]
];