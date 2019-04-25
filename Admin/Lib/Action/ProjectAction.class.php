<?php

class ProjectAction extends CommonAction {

    function index() {
        $model = D('project');
        $map['type'] = 2; //公司人员和工程人员在一个表，所以拿tpye来区分，2工程端，3是公司端
        if(!empty($_REQUEST['name'])){
            $map['name'] =$_REQUEST['name'];
        }
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
    }

    public function _before_add() {
        $brandList = D('brand')->select();
        $this->assign('brandList', $brandList);
    }

    public function insert() {
        $model = D('project');
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        $passwd = md5($_REQUEST['password']);
        $model->__set("password", $passwd);
        $model->__set("pw", $_REQUEST['password']);
        $model->__set("type", 2);
        $list = $model->add();
        if ($list !== false) { //保存成功
            $models = M();
            $uid = $this->getMaxId('User');
            $timer = time();
            $models->query("insert into tb_user set account='" . $_REQUEST['phone'] . "',password='" . $passwd . "',pwd='" . $_REQUEST['password'] . "',type=2,ext_table='tb_project',ext_id='" . $list . "',status=1,create_time='" . $timer . "'");
            $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
            $this->success('新增成功!');
        } else {
            //失败提示
            $this->error('新增失败!');
        }
    }

    function update() {
        $model = D('Project');
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        // 更新数据
        $model->__set("password", md5($_REQUEST['password']));
        $model->__set("pw", $_REQUEST['password']);
        $list = $model->save();
        if (false !== $list) {
            $where['ext_id'] = $_REQUEST['id'];
            $where['ext_table'] = 'tb_project';
            $where['type'] = 2;
            $saveData['password'] = md5($_REQUEST['password']);
            $saveData['pwd'] = $_REQUEST['password'];
            $saveData['account'] = $_REQUEST['phone'];
            D('user')->where($where)->save($saveData);
            $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
            $this->success('编辑成功!');
        } else {
            //错误提示
            $this->error('编辑失败!');
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