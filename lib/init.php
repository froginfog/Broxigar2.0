<?php
require_once APP_ROOT.'/vendor/autoload.php';//加载composer

lib\core\Ip::banIp(lib\core\Ip::getIp());//阻止指定的IP访问

lib\core\Router::match();
