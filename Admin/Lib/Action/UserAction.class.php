<?php

// 后台用户模块
class UserAction extends CommonAction {

    function _filter(&$map) {
        $map['id'] = array('egt', 2);
        $map['account'] = array('like', "%" . $_POST['account'] . "%");
    }

  
    // 插入数据
    public function _before_insert() {
        var_dump($_FILES['pic']['name'])  ;
        exit;
         Log::write('222222'.$_FILES['pic']['name'], LOG::INFO);
        if (!empty($_FILES['pic']['name'])) {
            import('ORG.Net.UploadFile');
            $upload = new UploadFile(); // 实例化上传类
            $upload->maxSize = 314572800; // 设置附件上传大小
            $upload->saveRule = 'uniqid';
            //$upload->allowExts = array('jpg', 'gif', 'png', 'jpeg','mp4','wmv'); // 设置附件上传类型
            $upload->savePath = C('UPLOAD_PATH');
            if (!$upload->upload()) {
                $this->error($upload->getErrorMsg());
            } else {
                $imgs = $upload->getUploadFileInfo();
                $_POST['pic'] = C('UPLOAD_URL') . $imgs[0]['savename'];
            }
        }
    }
    public function insert() {
        // 创建数据对象
        $User = D("User");
        if (!$User->create()) {
            $this->error($User->getError());
        } else {
            // 写入帐号数据
            if ($result = $User->add()) {
                $this->addRole($result);
                $this->success('用户添加成功！');
            } else {
                $this->error('用户添加失败！');
            }
        }
    }

    protected function addRole($userId) {
        //新增用户自动加入相应权限组
        $RoleUser = M("RoleUser");
        $RoleUser->user_id = $userId;
        // 默认加入网站编辑组
        $RoleUser->role_id = 3;
        $RoleUser->add();
    }

    //重置密码
    public function resetPwd() {
        $id = $_POST['id'];
        $password = $_POST['password'];
        if ('' == trim($password)) {
            $this->error('密码不能为空！');
        }
        $User = M('User');
        $User->password = md5($password);
        $User->id = $id;
        $result = $User->save();
        if (false !== $result) {
            $this->success("密码修改为$password");
        } else {
            $this->error('重置密码失败！');
        }
    }

    function foreverdelete() {
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST [$pk];
            if (isset($id)) {
                $condition = array($pk => array('in', explode(',', $id)));
                if (false !== $model->where($condition)->delete()) {
                    //echo $model->getlastsql();
                    
                    $this->success('删除成功！');
                    
                    
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
        }
        $this->index();
    }

    public function delete() {
        $model = D('user');
        $uids = $_REQUEST['uids'];
        $condition = array('id' => array('in', $uids));
        log::write(print_r($condition, true), LOG::INFO);
        if (false !== $model->where($condition)->delete()) {
            echo '1';
        } else {
            echo '2';
        }
    }

    function arr_to_str($arr) {
        foreach ($arr as $v) {
            $v = join(",", $v); //可以用implode将一维数组转换为用逗号连接的字符串，join是别名    
            $temp[] = $v;
        }
        foreach ($temp as $v) {
            $t .= $v . ",";
        }
        $t = substr($t, 0, -1);  //利用字符串截取函数消除最后一个逗号    
        return $t;
    }

}

?>