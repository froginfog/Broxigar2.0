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
    $prefix = Config::$conf['url_prefix'] == '' ? '' : Config::$conf['url_prefix'].'/';
    $root = URL_ROOT == '' ? '' : URL_ROOT.'/';
    $res = '//'.$domain.'/'.$root.$prefix.implode('/', $params).Config::$conf['url_suffix'].$anchor;
    return $res;
}