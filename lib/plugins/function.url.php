<?php
use lib\core\Config;
use lib\core\Request;

function smarty_function_url($params){
    if(array_key_exists('anchor', $params) && $params['anchor'] != ''){
        $anchor = '#'.$params['anchor'];
        unset($params['anchor']);
    }else{
        $anchor = '';
    }
    $request = Request::instance();
    $domain = $request->domain();
    $root = URL_ROOT == '' ? '' : '/'.URL_ROOT;
    $prefix = Config::$conf['url_prefix'] == '' ? '' : '/'.Config::$conf['url_prefix'];
    $path = $params['path'] == '' ? '' : '/'.implode('/', $params);
    $suffix = $params['path'] == '' ? '' : Config::$conf['url_suffix'];
    $res = '//'.$domain.$root.$prefix.$path.$suffix.$anchor;
    return $res;

}