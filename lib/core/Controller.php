<?php
namespace lib\core;

use Smarty;
use Sinergi\BrowserDetector\Os;
/**
 * 控制器类继承此类
 * Class Controller
 * @package lib\core
 */
class Controller {

    private static $smarty = null;

    private function smartyInit(){
        if(is_null(self::$smarty)){
            self::$smarty = new Smarty();
        }
        self::$smarty->left_delimiter = Config::$conf['smarty']['left'];
        self::$smarty->right_delimiter = Config::$conf['smarty']['right'];
        self::$smarty->setTemplateDir(Config::$conf['smarty']['template_dir'].'/'.Config::$mod);
        self::$smarty->setCompileDir(Config::$conf['smarty']['compile_dir']);
        self::$smarty->setCacheDir(Config::$conf['smarty']['cache_dir']);
        self::$smarty->addPluginsDir(Config::$conf['smarty']['plugins_dir']);
        self::$smarty->caching = Config::$conf['smarty']['cacheing'];
        self::$smarty->cache_lifetime = Config::$conf['smarty']['cache_lifetime'];
        self::$smarty->debugging = Config::$conf['smarty']['debugging'];
    }


    protected function assign($tpl_var, $value = null, $nocache = false){
        $this->smartyInit();
        return self::$smarty->assign($tpl_var, $value, $nocache);
    }

    protected function render($templateFile, $cache_id = null, $compile_id = null, $parent = null){
        $this->smartyInit();
        $os = new Os();
        //如果设置为开启移动端 且客户端为移动端 且指定的移动端模板存在 则优先渲染移动端模板
        if(Config::$conf['has_mobile'] && $os->isMobile() && self::$smarty->templateExists('mobile/' . $templateFile)) {
            $pathToFile = 'mobile/' . $templateFile;
            return self::$smarty->display($pathToFile, $cache_id, $compile_id, $parent);
        }else{
            return self::$smarty->display($templateFile, $cache_id, $compile_id, $parent);
        }

    }

    protected function jsonResponse($arr){
        header("Content-type: application/json");
        echo json_encode($arr, JSON_HEX_TAG);
        exit;
    }

    /**
     * 重定向
     * 1.参数不填时返回上一页
     * 2.定向到站外时参数必须为合法的带协议名的url http://www.example.com
     * 3.定向到站内时参数为站内url
     * @param null|array|string $where
     */
    protected function redirect($where=null){
        if(is_null($where)) {
            $url = $_SERVER['HTTP_REFERER'];
            header("location:$url");
            exit;
        }
        if(false !== filter_var($where, FILTER_VALIDATE_URL)){
            header("location:$where");
            exit;
        }
        $url = '/' . URL_ROOT.$where;
        header("location:$url");
        exit;
    }
}