<?php

class CompanyshopAction extends CommonAction {

    public function index() {
        $model = D('Companyshop');
        $map['companyId'] = $_REQUEST['companyId'];
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->assign('companyId', $_REQUEST['companyId']);
        $this->display();
    }

    public function add() {
        $shopRow = D('shop')->select();
        $this->assign('companyId', $_REQUEST['companyId']);
        $this->assign('shopRow', $shopRow);
        $this->display();
    }

    public function insert() {
        $model = D('companyshop');
        Log::write($_REQUEST['txtHobby'] . 'AAAAA', LOG::INFO);
        $companyId = $_REQUEST['companyId'];
        if(empty($_REQUEST['txtHobby'])){
            $this->error('请选择要添加的店铺');
        }
        $shopId = explode(",", rtrim($_REQUEST['txtHobby'], ","));
        Log::write(print_r($shopId, true), LOG::INFO);
        if (!empty($shopId) && is_array($shopId)) {
            foreach ($shopId as $value) {
                $model->__set('shopId', $value);
                $model->__set('companyId', $companyId);
                $list = $model->add();
            }
        }
        $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
        Log::write(Cookie::get('_currentUrl_') . 'AAA', LOG::INFO);
        $this->success('新增成功!');
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

    public function checkArrive() {
        echo 1;
    }

    public function delete() {
        $model = D('project');
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