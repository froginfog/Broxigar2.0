<?php
//映射规则形如：'/news/(\d+)/(w+).html' => '模块名/控制器名/方法名?(第1个参数名):(捕获到的第1个参数)&(第2个参数名):(捕获到的第2个参数)'
$index = array(
    '' => 'index/index/index',
    '/news/(\d+).html'=>'index/index/news?news_id:1',
    '/news/show/fuck/(\d+).html' => 'index/index/newsshow',
);

$admin = array(
    '/admin' => 'admin/admin/index',
    '/admin/nihao/(\d+).html' => 'admin/admin/nihao?id:1',
);









$_url = array_merge($index, $admin);
return $_url;