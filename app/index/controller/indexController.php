<?php
namespace app\index\controller;

use lib\core\Controller;
use lib\core\Tree;
use lib\core\Db;
class indexController extends Controller {
    function index(){
        $db = Db::instance();
        $a = new Tree(
            $db,
            'pic_cate',
            'pic_cate_id',
            'pic_cate_name',
            'pic_cate_father',
            'pic_cate_left',
            'pic_cate_right',
            'pic_cate_cover',
            'pic_cate_intro',
            'pic_cate_order');
        //$r = $a->init();
        //$a->add(4,'d');
        //$r = $a->getDescendants(3);
        //var_dump($r);
        //$a->look();
        //print_r($a->getDescendants(3, false));
        //$a->add(17,'e');
        //$a->move(17,13,'dd');

    }

    function news(){
        $this->assign('fuck', 'nihao');
        $this->render('index.html');
    }

    function newsshow(){
        $this->render('show.html');
    }
}