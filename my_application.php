<?php
require_once __DIR__ . '/init.phar';

$ssh = new \mpr\net\ssh("bots02.sdstream.ru", 22, 'ostrovskiy', '123456Fe', '/home/ostrovskiy/.ssh/id_rsa.pub', '/home/ostrovskiy/.ssh/id_rsa');
$ssh->connect();
var_dump($ssh->execute("ls -la /", true));