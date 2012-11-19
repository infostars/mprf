<?php
/**
 * @author greevex
 * @date: 11/19/12 5:11 PM
 */

\mpr\config::$package['mpr_locker_cache'] = [
    'servers' => [
        [
            //'host' => 'memcached01.sdstream.ru',
            'host' => 'localhost',
            'port' => 11211
        ]
    ]
];