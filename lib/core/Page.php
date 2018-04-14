<?php
namespace lib\core;

class Page {
    private $records;  //总记录数
    private $pageSize; //每页记录数
    private $listOneSide; //页码列表中 当前页一边保持带链接页码的数量
    private $totalPage; //总页数
    private $thisPage;  //当前页
    private $query;
    private $left; //页码列表 起始页
    private $right; //页码列表结束页
    private $res = array();

    function __construct($record, $pageSize, $listOneSide = 3){
        $this->records = intval($record) > 0 ? intval($record) : 1;
        $this->pageSize = intval($pageSize) >= 1 ? intval($pageSize) : 1;
        $this->listOneSide = intval($listOneSide) >= 1 ? intval($listOneSide) : 1;
        $this->totalPage = ceil($this->records / $this->pageSize);
        $this->thisPage = isset($_GET['page'])? $_GET['page'] : 1;
        if($this->thisPage < 1){
            $this->thisPage = 1;
        }
        if($this->thisPage > $this->totalPage){
            $this->thisPage = $this->totalPage;
        }
        $this->query = $this->getQuery();
        $this->left = $this->thisPage - $this->listOneSide;
        $this->right = $this->thisPage + $this->listOneSide;
        if($this->left < 1){
            $this->left = 1;
        }
        if($this->right > $this->totalPage){
            $this->right = $this->totalPage;
        }
    }

    private function getQuery(){
        parse_str($_SERVER['QUERY_STRING'], $arr);
        unset($arr['page']);
        if(empty($arr)){
            $_query = '?page=';
        }else{
            $_query = '?'.http_build_query($arr).'&page=';
        }
        return $_query;
    }

    private function home(){
        if($this->thisPage != 1){
            $this->res['home'] = $this->query.'1';
        }else{
            $this->res['home'] = '';
        }
    }

    private function end(){
        if($this->thisPage != $this->totalPage){
            $this->res['end'] = $this->query.$this->totalPage;
        }else{
            $this->res['end'] = '';
        }
    }

    private function pageCount(){
        $this->res['pagecount'] = $this->totalPage;
    }

    private function recordCount(){
        $this->res['recordcount'] = $this->records;
    }

    private function prev(){
        if($this->thisPage != 1){
            $this->res['prev'] = $this->query.($this->thisPage - 1);
        }else{
            $this->res['prev'] = '';
        }
    }

    private function next(){
        if($this->thisPage != $this->totalPage){
            $this->res['next'] = $this->query.($this->thisPage + 1);
        }else{
            $this->res['next'] = '';
        }
    }

    private function pageList(){
        for($i = $this->left; $i <= $this->right; $i++){
            if($i == $this->thisPage){
                $this->res['pagelist'][$i] = '';
            }else{
                $this->res['pagelist'][$i] = $this->query.$i;
            }
        }
    }

    private function thisPage(){
        $this->res['thispage'] = $this->thisPage;
    }

    public function outPut(){
        $this->thisPage();
        $this->recordCount();
        $this->pageCount();
        $this->home();
        $this->end();
        $this->prev();
        $this->next();
        $this->pageList();
        return $this->res;
    }
}