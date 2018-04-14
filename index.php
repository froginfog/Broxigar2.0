<?php
if(version_compare(PHP_VERSION,'5.5.0','<'))  die('要求php版本至少为5.5.0 !');

define("APP_ROOT", __DIR__);//网站根目录

define('URL_ROOT', 'binbin'); //网站所在目录如果不是根目录填写目录名，如果是根目录为空

date_default_timezone_set("Asia/Shanghai");//设置默认时区

require APP_ROOT."/lib/init.php";//开始

