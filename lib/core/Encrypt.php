<?php
namespace lib\core;

class Encrypt{
    private static function keying($str){
        $private_key = md5('vurtne'); //todo
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($str); $i++) {
            $ctr = $ctr == strlen($private_key) ? 0 : $ctr;
            $tmp .= $str[$i] ^ $private_key[$ctr++];
        }
        return $tmp;
    }

    public static function encode($str){
        $str = (string)$str;
        $encrypt_key = md5(mt_rand(0, 32000));
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($str); $i++) {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr].($str[$i] ^ $encrypt_key[$ctr++]);
        }
        return base64_encode(self::keying($tmp));
    }

    public static function decode($str){
        $txt = self::keying(base64_decode($str));
        $tmp = '';
        for ($i = 0; $i < strlen($txt); $i++) {
            $tmp .= $txt[$i] ^ $txt[++$i];
        }
        return $tmp;
    }
}