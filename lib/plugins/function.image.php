<?php
use lib\core\Request;

function smarty_function_image($params){
    $request = Request::instance();
    $root = URL_ROOT == '' ? '' : URL_ROOT.'/';
    $res = '//'.$request->domain().'/'.$root.implode('/', $params);
    return $res;
}