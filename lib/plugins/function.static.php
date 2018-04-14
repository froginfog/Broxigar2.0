<?php
use lib\core\Request;
use lib\core\Config;

function smarty_function_static($params){
    $request = Request::instance();
    $root = URL_ROOT == '' ? '' : URL_ROOT.'/';
    $res = '//'.$request->domain().'/'.$root.'static/'.Config::$mod.'/'.implode('/', $params);
    return $res;
}