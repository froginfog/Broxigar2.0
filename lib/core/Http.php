<?php
namespace lib\core;

class Http{

    /**
     * @param string $url
     * @param array $parameters
     * @return mixed
     */
    public static function get($url, $parameters=array(), $header=array()){
        return self::request('get', $url, $parameters,  $header);
    }

    /**
     * @param  string $url
     * @param array $parameters
     * @return mixed
     */
    public static function post($url, $parameters=array(), $header=array()){
        return self::request('post', $url, $parameters, $header);
    }

    private static function request($method, $url, $params=array(), $header=array()){
        $param = http_build_query($params);
        $ch = curl_init();
        if($method == 'get'){
            $url .= $param == '' ? '' : '?'.$param;
        }elseif ($method == 'post'){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }else{
            return false;
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER  , $header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        if($res === false){
            echo curl_error($ch);
            curl_close($ch);
            exit();
        }else{
            curl_close($ch);
            return $res;
        }
    }
}