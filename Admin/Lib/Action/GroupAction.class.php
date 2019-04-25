<?php

class GroupAction extends CommonAction {

    /**
      +----------------------------------------------------------
     * 默认排序操作
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     * @throws FcsException
      +----------------------------------------------------------
     */
    public function index() {
        //列表过滤器，生成查询Map对象
        $map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $name = $this->getActionName();
        $model = D($name);
     Log::write(print_r($map,true),LOG::SQL);
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
        return;
    }
    public function sort() {
        $node = M('Group');
        if (!empty($_GET['sortId'])) {
            $map = array();
            $map['status'] = 1;
            $map['id'] = array('in', $_GET['sortId']);
            $sortList = $node->where($map)->order('sort asc')->select();
        } else {
            $sortList = $node->where('status=1')->order('sort asc')->select();
        }
        $this->assign("sortList", $sortList);
        $this->display();
        return;
    }

}

?>