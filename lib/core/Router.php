<?php
namespace lib\core;

class Router {

    public static function match(){
        $request = Request::instance();
        $url = $request->pathInfo();//获取url
        $rule = include(APP_ROOT.'/app/url.php'); //获取url映射规则

        //遍历匹配映射规则
        foreach($rule as $left=>$right){
            //把映射规则左侧转成正则表达式
            $left = str_replace('/', '\/', $left);
            $left = '/^'.$left.'$/';

            //正则表达式匹配url
            $res = preg_match($left, $url, $match);

            //匹配到结果一次后结束循环
            if($res == 1){
                //解析匹配到的映射规则，拆分规则右侧，以？为分界拆成数组，一部分是模块控制器方法 ，一部分是get参数
                $right = explode('?', $right);

                //以/为分界拆分模块控制器方法部分，获得要执行哪个模块下哪个控制器的哪个方法
                list($moudles, $class, $method) = explode('/', $right[0]);

                //如果有get部分，把get部分以&分界拆成数组
                if(isset($right[1])){
                    $args = explode('&', $right[1]);
                }
                break;
            }
        }
        if($res == 1){
            //如果有get部分，拆分后放入全局变量
            if(!empty($args)) {
                foreach ($args as $arg) {
                    $_arg = explode(':', $arg);
                    $_GET[$_arg[0]] = $match[$_arg[1]];
                }
            }

            //加载模块的配置，并用自动依赖注入执行控制器的方法
            Config::set($moudles);
            try{
                $run = new Di('app\\'.$moudles.'\controller\\'.$class.'Controller', $method);
                $run->runWithArgs();
            }catch(\Exception $e){
                die($e->getMessage());
            }
        }else{
            http_response_code(404);
        }
    }
}