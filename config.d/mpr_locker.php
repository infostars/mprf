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
    'memcached' => [
        // driver package name
        'driver' => 'mpr_locker_cache',
        'backend_section' => 'memcached'
    ],
    'redis' => [
        // driver package name
        'driver' => 'mpr_locker_cache',
        'backend_section' => 'redis'
    ],
    'semaphore' => [
        // driver package name
        'driver' => 'mpr_locker_phpSemaphore',
        'backend_section' => ''
    ]
];