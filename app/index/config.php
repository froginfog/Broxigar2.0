<?php
return array(
    'l10n'        => false, //开启本地化

    'has_mobile'   => false, //true时 如果访问者为移动端则优先渲染移动端页面

    'url_prefix'  => '',

    'url_suffix'  => '.html',

    'smarty'      => [ //smarty设置

        'left'    => '{',

        'right'   => '}',

        'template_dir' => APP_ROOT.'/templates',

        'compile_dir'  => APP_ROOT.'/templates/other/tpl_c',

        'cache_dir' => APP_ROOT.'/templates/other/cache',

        'plugins_dir' => APP_ROOT.'/lib/plugins',

        'cacheing' => false,

        'cache_lifetime' => 120,

        'debugging' => false

    ],

    'database' => [
        'db_file' => APP_ROOT.'/database/index/index.db',
        //'db_charset' => 'utf8'
    ],
);