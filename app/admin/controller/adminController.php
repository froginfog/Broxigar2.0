<?php
namespace app\admin\controller;

use lib\core\Controller;
use lib\core\Request;
use voku\helper\AntiXSS;

class adminController extends Controller{
    function index(){
        $this->assign('fuck', 'admin');
        $this->render('index.html');
    }

    function nihao (Request $request, AntiXSS $antiXSS){
        echo $request->get('id',$antiXSS);
    }
}