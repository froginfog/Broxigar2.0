<?php
namespace lib\core;

use voku\helper\AntiXSS;

/**
 * 封装get,post等客户端输入
 * Class Request
 * @package lib\core
 */
class Request {
    protected static $instance = null;
    protected $domain;
    protected $phpInput;
    protected $get=[];
    protected $post=[];
    protected $pathInfo='';

    protected function __construct(){
        $this->phpInput = file_get_contents('php://input');
    }

    /**
     * 单例模式
     * @return Request 实例
     */
    public static function instance(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取当前域名
     * @return string
     */
    public function domain(){
        if(!$this->domain){
            $this->domain = isset($_SERVER['HTTP_X_REAL_HOST']) ? $_SERVER['HTTP_X_REAL_HOST'] : $_SERVER['HTTP_HOST'];
        }
        return $this->domain;
    }

    public function basePath(){
        $root = URL_ROOT == '' ? '' : '/'.URL_ROOT;
        $basePath = '//'.$this->domain().$root;
        return $basePath;
    }

    /**
     * 获取请求数据流
     * @return bool|string
     */
    public function phpInput(){
        return $this->phpInput;
    }

    /**
     * 获取请求的url 不包含域名和get参数
     */
    public function pathInfo(){
        if(isset($_SERVER['PATH_INFO'])){
            $this->pathInfo = $_SERVER['PATH_INFO'];
            return $this->pathInfo;
        }elseif(isset($_SERVER['ORIG_PATH_INFO'])){
            $this->pathInfo = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['ORIG_PATH_INFO']);
            return $this->pathInfo;
        }else{
            $this->pathInfo = '';
            return $this->pathInfo;
        }
    }

//    /**
//     * 将一个键值对数组放入GET
//     * @param $param array
//     */
//    public function setGet($param=[]){
//        $param = (array)$param;
//        if(empty($this->get)){
//            $this->get = $_GET;
//        }
//        $this->get = array_merge($param, $this->get);
//    }

    /**
     * @param null|string $name 要获取的一个get键
     * @param AntiXSS $antiXSS
     * @return array|bool|string
     */
    public function get($name=null){
        $antiXSS = new AntiXSS();
        $this->get = $_GET;
        if(is_null($name)){
            //如果没有传递name则获取所有get
            $res = [];
            foreach($this->get as $key=>$value){
                $res[$key] = $antiXSS->xss_clean($value);
            }
            return $res;
        }else{
            //如果name为字符串则获取对应的值
            if(isset($this->get[$name])) {
                $res = $antiXSS->xss_clean($this->get[$name]);
            }else{
                $res = '';
            }
            return $res;
        }
    }

    public function post($name=null){
        $antiXSS = new AntiXSS();
        $this->post = $_POST;
        if(is_null($name)){
            //name为null则获取所有post
            $res = [];
            foreach($this->post as $key=>$value){
                $res[$key] = $antiXSS->xss_clean($value);
            }
            return $res;
        }else{
            //name不为null获取对应post
            if(isset($this->post[$name])) {
                $res = $antiXSS->xss_clean($this->post[$name]);
            }else{
                $res = '';
            }
            return $res;
        }

    }
	
	public function json(){
        $antiXSS = new AntiXSS();
        $antiXSS->removeEvilAttributes(array('style'));
        $_res = $antiXSS->xss_clean($this->phpInput());
        $res = json_decode($_res, true);
        return $res;
    }
}