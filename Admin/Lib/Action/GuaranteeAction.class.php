<?php

class GuaranteeAction extends CommonAction {

    function index() {
        $model = D('guarantee');
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
    }

    public function insert() {
        $model = D('guarantee');
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        $list = $model->add();
        if ($list !== false) { //保存成功
            $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
            $this->success('新增成功!');
        } else {
            //失败提示
            $this->error('新增失败!');
        }
    }

    public function foreverdelete() {
        //删除指定记录
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST [$pk];
            if (isset($id)) {
                $condition = array($pk => array('in', explode(',', $id)));
                if (false !== $model->where($condition)->delete()) {
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
        $this->forward();
    }
    public function delete() {
        $model = D('guarantee');
        $uids = $_REQUEST['uids'];
        $condition = array('id' => array('in', $uids));
        log::write(print_r($condition, true), LOG::INFO);
        if (false !== $model->where($condition)->delete()) {
            echo '1';
        } else {
            echo '2';
        }
    }

}

?>