<?php
namespace lib\core;

class Tree {

    protected $map=[];

    protected $db;

    protected $table_name;

    protected $column_id_name;

    protected $column_title_name;

    protected $column_father_name;

    protected $column_left_name;

    protected $column_right_name;

    protected $column_cover_name;

    protected $column_intro_name;

    protected $column_order_name;

    protected $column_isrec_name;

    /**
     * 参数为表名和表中字段名
     * Tree constructor.
     * @param Db $db
     * @param $tableName
     * @param $idName
     * @param $titleName
     * @param $fatherName
     * @param $leftName
     * @param $rightName
     * @param null $coverName
     * @param $introName
     * @param $orderName
     * @param $isrecName
     */
    public function __construct(Db $db, $tableName, $idName, $titleName, $fatherName, $leftName, $rightName, $coverName, $introName, $orderName, $isrecName){
        $this->db = $db;
        $this->table_name = $tableName;
        $this->column_id_name = $idName;
        $this->column_title_name = $titleName;
        $this->column_father_name = $fatherName;
        $this->column_left_name = $leftName;
        $this->column_right_name = $rightName;
        $this->column_cover_name = $coverName;
        $this->column_intro_name = $introName;
        $this->column_order_name = $orderName;
        $this->column_isrec_name = $isrecName;
    }

    /**
     * 初始化，从数据库中读取所有分类放入map数组
     */
    function init(){
        if(count($this->map) == 0){
            $sql = 'select '.
                $this->column_id_name.','.
                $this->column_title_name.','.
                $this->column_father_name.','.
                $this->column_left_name.','.
                $this->column_right_name.','.
                $this->column_cover_name.','.
                $this->column_intro_name.','.
                $this->column_order_name.','.
                $this->column_isrec_name.
                ' from '.$this->table_name.
                ' order by '.$this->column_left_name.' asc';
            try{
                $tmp = $this->db->query($sql)->getAll();
            }catch(\PDOException $e){
                die($e->getMessage());
            }
            foreach($tmp as $item){
                $this->map[$item[$this->column_id_name]] = $item;
            }
            unset($tmp);
        }
    }

    /**
     * 对修改后的map重新排序
     */
    protected function mapReorder(){
        //取出map中所有节点的left
        foreach($this->map as $item){
            ${$this->column_left_name}[] = $item[$this->column_left_name];
        }
        //按升序排序这个新生成的数组，以此策略来对map排序
        array_multisort(${$this->column_left_name}, SORT_ASC, SORT_NUMERIC, $this->map);
        $tmp = [];
        //排序后map的key也被打乱 遍历map 以各项的id值作为map各项的key
        foreach($this->map as $item){
            $tmp[$item[$this->column_id_name]] = $item;
        }
        $this->map = $tmp;
        unset($tmp);
    }

    public function get_tree(){
        $this->init();
        return $this->map;

    }

    /**
     * 获取指定节点的后代
     * @param int $node_id 要获取哪个节点的后代 0表示从对顶层的节点开始
     * @param bool $son_only 是否只获取直接后代
     * @return array|bool
     */
    public function getDescendants($node_id=0, $son_only=true){
        $this->init();
        if(isset($this->map[$node_id]) or $node_id === 0){
            $descendants = [];
            $keys = array_keys($this->map);
            //遍历map数组
            foreach($keys as $k){
                if(
                    //判断当前遍历项的left是否大于指定节点的left，后代节点的left一定大于父节点的left
                    $this->map[$k][$this->column_left_name] > ($node_id === 0 ? 0 : $this->map[$node_id][$this->column_left_name]) and
                    //判断当前遍历项的right是否小于指定节点的right，后代节点的right一定小于父节点的right
                    $this->map[$k][$this->column_right_name] < ($node_id === 0 ? PHP_INT_MAX : $this->map[$node_id][$this->column_right_name]) and
                    //如果只要直接后代节点，判断当前遍历项的直接父节点是不是指定的节点
                    (!$son_only or $this->map[$k][$this->column_father_name] == $node_id)
                ){
                    $descendants[$this->map[$k][$this->column_id_name]] = $this->map[$k];
                }
            }
            return $descendants;
        }
        return false;
    }

    /**
     * 获取指定节点到根的路径
     * @param $node
     * @return array
     */
    public function get_path($node){
        $this->init();
        $path = array();
        //节点必须存在
        if(isset($this->map[$node])){
            foreach($this->map as $id=>$value){
                //遍历map 如果当前遍历项的left小于指定节点的left并且right大于指定节点的right 那么这一项就是指定节点的祖先节点
                if($value[$this->column_left_name] < $this->map[$node][$this->column_left_name] and $value[$this->column_right_name] > $this->map[$node][$this->column_right_name]){
                    $path[$value[$this->column_id_name]] = $value;
                }
            }
        }
        return $path;
    }

    public function add($father, $title, $cover=null, $intro=null){
        $this->init();
        $father = (int)$father;
        //指定的节点必须是已有的节点或0
        if($father === 0 or isset($this->map[$father])){
            //获取指定节点的后代
            $descendants = $this->getDescendants($father);
            //没有后代时
            if(count($descendants) == 0){
                //没有后代并且指定节点为0时 即为向空表中插入第一个节点 边界设置为0
                //没有后代 指定节点不为0时 即为向指定节点插入第一个后代 边界为指定节点的left
                $boundary = $father == 0 ? 0 : $this->map[$father][$this->column_left_name];
            //有后代时
            }else{
                //获取指定节点的最后一个直接后代节点 边界为这个节点的right
                $lastOne = array_pop($descendants);
                $boundary = $lastOne[$this->column_right_name];

            }
            //遍历所有的节点 为即将插入的节点空出位置
            foreach($this->map as $id=>$value){
                //所有left大于边界的节点的left+2
                if($value[$this->column_left_name] > $boundary){
                    $this->map[$id][$this->column_left_name] += 2;
                }
                //所有right大于边界的节点的right+2
                if($value[$this->column_right_name] > $boundary){
                    $this->map[$id][$this->column_right_name] += 2;
                }
            }
            //同步数据库中的数据
            $leftSql = 'update '.$this->table_name
                .' set '.$this->column_left_name.'='.$this->column_left_name.'+2'
                .' where '.$this->column_left_name. '>'.$boundary;
            $rightSql = 'update '.$this->table_name
                .' set '.$this->column_right_name.'='.$this->column_right_name.'+2'
                .' where '.$this->column_right_name. '>' . $boundary;
            $insertSql = 'insert into '.$this->table_name
                .'('
                .$this->column_title_name.','
                .$this->column_intro_name.','
                .$this->column_cover_name.','
                .$this->column_father_name.','
                .$this->column_left_name.','
                .$this->column_right_name
                .') '.' values (?,?,?,?,?,?)';
            $insertArr = array(
                array('value'=>$title, 'type'=>\PDO::PARAM_STR),
                array('value'=>$intro, 'type'=>\PDO::PARAM_STR),
                array('value'=>$cover, 'type'=>\PDO::PARAM_STR),
                array('value'=>$father, 'type'=>\PDO::PARAM_INT),
                array('value'=>$boundary + 1, 'type'=>\PDO::PARAM_INT),
                array('value'=>$boundary + 2, 'type'=>\PDO::PARAM_INT)
            );
            try{
                $this->db->beginTransaction();
                $this->db->exec($leftSql);
                $this->db->exec($rightSql);
                $this->db->prepare($insertSql)->bindValue($insertArr)->execute();
                $id = $this->db->lastInsertId();
                $this->db->commit();
            }catch(\PDOException $e){
                $this->db->rollBack();
                die($e->getMessage());
            }
            //拿到数据库中插入新节点的id后 向map中插入新节点
            $this->map[$id] = array(
                $this->column_id_name = $id,
                $this->column_title_name = $title,
                $this->column_cover_name = $cover,
                $this->column_intro_name = $intro,
                $this->column_father_name = $father,
                $this->column_left_name = $boundary + 1,
                $this->column_right_name = $boundary +2,
            );
            return $id;
        }
        $this->mapReorder();
        return false;
    }

    /**
     * 删除节点,包括节点的后代
     * @param $node
     * @return bool
     */
    public function delete($node){
        $this->init();
        //如果指定的节点存在
        if(isset($this->map[$node])){
            //找到指定节点的所有后代并删除
            $descendants = $this->getDescendants($node, false);
            foreach($descendants as $descendant){
                unset($this->map[$descendant[$this->column_id_name]]);
            }
            //记下要删除节点的left和right
            $nodeLeft = $this->map[$node][$this->column_left_name];
            $noderight = $this->map[$node][$this->column_right_name];
            //删除指定节点后空出的位置由后边的其他节点补上 这些节点移动时left和right需要减小的值是被删除节点的：right-left+1
            $diff = $noderight - $nodeLeft + 1;
            //被删除节点的left作为边界
            $boundary = $this->map[$node][$this->column_left_name];
            //删除指定节点
            unset($this->map[$node]);
            //后边的节点前移填补被删除节点的空位
            foreach($this->map as $id=>$item){
                //如果当前节点的left大于边界 left减去diff
                if($this->map[$id][$this->column_left_name] > $boundary){
                    $this->map[$id][$this->column_left_name] -= $diff;
                }
                //如果当前节点的right大于边界 right减去diff
                if($this->map[$id][$this->column_right_name] > $boundary){
                    $this->map[$id][$this->column_right_name] -= $diff;
                }
            }
            //同步数据库
            $deleteSql =
                'delete from '.$this->table_name.
                ' where '.
                $this->column_left_name.'>='.$nodeLeft.
                ' and '.$this->column_right_name.'<='.$noderight;
            $leftSql =
                'update '.$this->table_name.
                ' set '.$this->column_left_name.'='.$this->column_left_name.'-'.$diff.
                ' where '.$this->column_left_name.'>'.$boundary;
            $rightSql =
                'update '.$this->table_name.
                ' set '.$this->column_right_name.'='.$this->column_right_name.'-'.$diff.
                ' where '.$this->column_right_name.'>'.$boundary;
            try{
                $this->db->beginTransaction();
                $this->db->exec($deleteSql);
                $this->db->exec($leftSql);
                $this->db->exec($rightSql);
                $this->db->commit();
            }catch(\PDOException $e){
                $this->db->rollBack();
                $e->getMessage();
            }
            return true;
        }
        return false;
    }

    /**
     * 把一个节点连同其后代 移动为另一个节点的后代
     * @param $from
     * @param $target
     * @param $title
     * @param null $cover
     * @param null $intro
     * @return bool
     */
    public function move($from, $target, $title, $cover=null, $intro=null){
        $this->init();
        $fromDescendants = $this->getDescendants((int)$from, false);
        //目标节点不能是源节点的后代 也不能是自己
        if(in_array($target, array_keys($fromDescendants)) or $from == $target) return false;
        //源节点和目标节点必须存在 目标节点也可以是0
        if( isset($this->map[$from]) and (isset($this->map[$target]) or $target == 0) ){
            //把源节点的父节点字段改为目标节点
            $this->map[$from][$this->column_father_name] = $target;
            //把源节点及其所有后代放入一个数组,然后把它们都从map中删掉
            $fromTree = array($this->map[$from]);
            foreach($fromDescendants as $descendant){
                $fromTree[] = $descendant;
                //首先删掉其后代
                unset($this->map[$descendant[$this->column_id_name]]);
            }
            //源节点及其后代被删掉后空出的位置由后边的节点填补，后边节点的left和right需要减小的值是源节点的right-left+1
            $diff = $this->map[$from][$this->column_right_name] - $this->map[$from][$this->column_left_name] + 1;
            //源节点的left作为边界 边界之外的节点要向前移动
            $fromBoundary = $this->map[$from][$this->column_left_name];
            //暂存源节点的最初的left right
            $_left = $this->map[$from][$this->column_left_name];
            $_right = $this->map[$from][$this->column_right_name];
            //删掉源节点
            unset($this->map[$from]);
            //后边节点开始向前移
            foreach($this->map as $id=>$item){
                if($this->map[$id][$this->column_left_name] > $fromBoundary){
                    $this->map[$id][$this->column_left_name] -= $diff;
                }
                if($this->map[$id][$this->column_right_name] > $fromBoundary){
                    $this->map[$id][$this->column_right_name] -= $diff;
                }
            }
            //获取目标节点的直接后代
            $targetDescendants = $this->getDescendants((int)$target);
            if(count($targetDescendants) == 0){
                //如果目标节点没有后代 源节点会成为目标节点的第一个后代 这时如果目标节点是0 则边界为0
                //目标节点没有后代且目标节点不是0 则边界为目标节点的left
                $targetBoundary = $target == 0 ? 0 : $this->map[$target][$this->column_left_name];
            }else{
                //如果目标节点有后代，则边界为目标节点最后一个直接后代的right
                $lastOne = array_pop($targetDescendants);
                $targetBoundary = $lastOne[$this->column_right_name];
            }
            //遍历map为移动过来的源节点让出位置
            foreach($this->map as $id=>$item){
                if($item[$this->column_left_name] > $targetBoundary){
                    $this->map[$id][$this->column_left_name] += $diff;
                }
                if($item[$this->column_right_name] > $targetBoundary){
                    $this->map[$id][$this->column_right_name] += $diff;
                }
            }
            //开始把源节点插入目标位置 源节点的left和right需要更新
            $shift = $targetBoundary - $fromBoundary + 1;
            //遍历刚开始存的源节点及其后代 更新left和right 并放入目标位置
            foreach($fromTree as $item){
                $item[$this->column_left_name] += $shift;
                $item[$this->column_right_name] += $shift;
                $this->map[$item[$this->column_id_name]] = $item;
            }
            var_dump($fromDescendants);
            //同步数据库
            //把源节点的left和right乘以-1 使其不会参与后边的数据库操作
            $sql1 = 'update '.$this->table_name.
                ' set '.$this->column_left_name.'='.$this->column_left_name.'*-1,'.
                        $this->column_right_name.'='.$this->column_right_name.'*-1'.
                ' where '.$this->column_left_name.'>='.$_left.' and '.$this->column_right_name.'<='.$_right;
            //让源节点后边的节点填补空出的位置
            $sql2_1 = 'update '.$this->table_name.
                ' set '.$this->column_left_name.'='.$this->column_left_name.'-'.$diff.
                ' where '.$this->column_left_name.'>'.$fromBoundary;

            $sql2_2 = 'update '.$this->table_name.
                ' set '.$this->column_right_name.'='.$this->column_right_name.'-'.$diff.
                ' where '.$this->column_right_name.'>'.$fromBoundary;

            //让目标位置后边的节点让出位置
            $sql3_1 = 'update '.$this->table_name.
                ' set '.$this->column_left_name.'='.$this->column_left_name.'+'.$diff.
                ' where '.$this->column_left_name.'>'.$targetBoundary;

            $sql3_2 = 'update '.$this->table_name.
                ' set '.$this->column_right_name.'='.$this->column_right_name.'+'.$diff.
                ' where '.$this->column_right_name.'>'.$targetBoundary;

            //更新源节点的left和right 使其放入目标位置
            $sql4 = 'update '.$this->table_name.
                ' set '.$this->column_left_name.'=('.$this->column_left_name.'-('.$shift.'))*-1,'.
                        $this->column_right_name.'=('.$this->column_right_name.'-('.$shift.'))*-1'.
                ' where '.$this->column_left_name.'<0';
            //把源节点的父节点字段改为目标节点 并更新title intro cover
            $sql5 = 'update '.$this->table_name.
                ' set '.$this->column_father_name.'=?,'.
                        $this->column_title_name.'=?,'.
                        $this->column_intro_name.'=?,'.
                        $this->column_cover_name.'=?'.
                ' where '.$this->column_id_name.'='.$from;
            $arr5 = array(
                array('value'=>$target, 'type'=>\PDO::PARAM_INT),
                array('value'=>$title, 'type'=>\PDO::PARAM_STR),
                array('value'=>$intro, 'type'=>\PDO::PARAM_STR),
                array('value'=>$cover, 'type'=>\PDO::PARAM_STR)
            );
            try{
                $this->db->beginTransaction();
                $this->db->exec($sql1);
                $this->db->exec($sql2_1);
                $this->db->exec($sql2_2);
                $this->db->exec($sql3_1);
                $this->db->exec($sql3_2);
                $this->db->exec($sql4);
                $this->db->prepare($sql5)->bindValue($arr5)->execute();
                $this->db->commit();
            }catch(\PDOException $e){
                $this->db->rollBack();
                die($e->getMessage());
            }
            $this->mapReorder();
            return true;
        }
        return false;
    }

}