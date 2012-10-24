<?php
namespace symfony\component;

require_once __DIR__ . '/ClassLoader/UniversalClassLoader.php';
require_once __DIR__ . '/ClassLoader/ApcUniversalClassLoader.php';
require_once __DIR__ . '/ClassLoader.php';

$loader = new ClassLoader();
$loader->init();

__halt_compiler();