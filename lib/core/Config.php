<?php
namespace lib\core;

/**
 * 根据当前访问的模块 并加载其配置
 * Class Config
 * @package lib\core
 */
class Config {

    public static $conf=[];

    public static $mod;

    public static function set($modules){

        self::$conf = require(APP_ROOT.'/app/'.$modules.'/config.php');

        self::$mod = $modules;
    }
}