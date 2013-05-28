<?php
/**
 * @author demin
 * @date: 11/19/12 5:11 PM
 */

\mpr\config::$package['mpr_locker'] = [
    'default' => [
        // driver package name
        'driver' => 'mpr_locker_cache',
        'backend_section' => 'memcached'
    ],
    'cache_memcached' => [
        // driver package name
        'driver' => 'mpr_locker_cache',
        'backend_section' => 'memcached'
    ],
    'cache_redis' => [
        // driver package name
        'driver' => 'mpr_locker_cache',
        'backend_section' => 'redis'
    ],
    'cache_semaphore' => [
        // driver package name
        'driver' => 'mpr_locker_phpsemaphore',
        'backend_section' => ''
    ]
];