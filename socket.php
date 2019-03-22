<?php
require_once '../../vendor/autoload.php';
require_once __DIR__.'/lib/core/Socket.php';

$a = new \lib\core\Socket();
$a->start();
