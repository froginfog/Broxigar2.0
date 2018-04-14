<?php
namespace lib\core;
use lib\core\Encrypt;
/**
 * header {"typ": "JWT", "alg": "custom"}
 * payload {"exp":150000000, "id": "1", "role": "5"}
 * Class Jwt
 * @package lib\core
 *
 */
class Jwt {

    //jwt的header部分暂时写死
    protected $header = array('typ'=>'JWT', 'alg'=>'custom');

    /**
     * 生成header的字符串
     * @return string
     */
    protected function makeHeader(){
        $_header = json_encode($this->header);
        return base64_encode($_header);
    }

    /**
     * 生成payload的字符串
     * @param array $payload
     * @return string
     */
    protected function makePayload(array $payload){
        $_payload = json_encode($payload);
        return base64_encode($_payload);
    }

    /**
     * 组合header和payload字符串 加密后生成signature串 三部分组合成最终的jwt字符串
     * @param array $payload
     * @param \lib\core\Encrypt $encrypt
     * @return string
     */
    public function makeJwt(array $payload, Encrypt $encrypt){
        $header = $this->makeHeader();
        $payload = $this->makePayload($payload);
        $str = $header.'.'.$payload;
        $signature = $encrypt->encode($str);
        $res = $header.'.'.$payload.'.'.$signature;
        return $res;
    }

    /**
     * 验证收到的jwt字符串
     * @param $jwtstr
     * @param \lib\core\Encrypt $encrypt
     * @return bool|mixed
     */
    public function resolveJwt($jwtstr, Encrypt $encrypt){
        //把收到的jwt串拆成数组
        $jwtarr = explode('.', $jwtstr);
        //数组长度不是3 出错
        if(count($jwtarr) != 3) return false;

        list($headerStr, $payloadStr, $signatureStr) = $jwtarr;
        //payload部分解析成数组
        $payloadJson = base64_decode($payloadStr);
        $payloadArr = json_decode($payloadJson, true);
        //如果过期 出错
        if($payloadArr['exp'] < $_SERVER['REQUEST_TIME']) return false;
        //解密signature部分
        $signature = $encrypt->decode($signatureStr);
        //判断解密后的signature是否和 header+payload 一样
        if($signature == $headerStr.'.'.$payloadStr){
            //一样返回payload部分的信息
            return $payloadArr;
        }else{
            //不一样 出错
            return false;
        }
    }
}