<?php
use Sinergi\BrowserDetector\Language;

use lib\core\Config;

function smarty_modifier_l10n($str, $toLang=null){
    if(Config::$conf['l10n']){
        if(is_null($toLang)){
            $detect = new Language();
            $lang = $detect->getLanguage();
            if(file_exists(APP_ROOT.'/lib/l10n/'.$lang.'.php')){
                $arr = require (APP_ROOT.'/lib/l10n/'.$lang.'.php');
                if(array_key_exists($str, $arr)){
                    return $arr[$str];
                }else{
                    return $str;
                }
            }else{
                return $str;
            }
        }else{
            if(file_exists(APP_ROOT.'/lib/l10n/'.$toLang.'.php')){
                $arr = require (APP_ROOT.'/lib/l10n/'.$toLang.'.php');
                if(array_key_exists($str, $arr)){
                    return $arr[$str];
                }else{
                    return $str;
                }
            }else{
                return $str;
            }
        }
    }else{
        return $str;
    }

}