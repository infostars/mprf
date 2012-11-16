<?php
/**
 * @author greevex
 * @date: 11/16/12 5:11 PM
 */

\mpr\config::$package['mpr_debug_log'] = [
    'enabled' => true,
    'output' => 'stdout', // "stdout"|"output"|null
    'logfile' => "/tmp/mylog2.log",
];