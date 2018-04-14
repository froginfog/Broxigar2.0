<?php
namespace lib\core;

class Ip {
    public static function getIp(){
        static $ip = null;
        if (!is_null($ip)) {
            return $ip;
        }
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
            } else if (getenv('HTTP_CLIENT_IP')) {
                $ip = getenv('HTTP_CLIENT_IP');
            } else {
                $ip = getenv('REMOTE_ADDR');
            }
        }
        $ip = filter_var($ip, FILTER_VALIDATE_IP);
        if($ip === false){
            $ip = '0.0.0.0';
        }
        return $ip;
    }

    public static function banIp($ip){
        $ip = filter_var($ip, FILTER_VALIDATE_IP);
        if($ip !== false){
            $file = APP_ROOT.'/banIP';
            $arr = [];
            $handle = fopen($file, 'r');
            while(!feof($handle)){
                $arr[] = fgets($handle);
            }
            if(in_array($ip, $arr)){
                die('access deny!');
            }
        }
    }
}