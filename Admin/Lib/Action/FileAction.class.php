<?php

class FileAction extends CommonAction {

    public function _before_index() {
        $this->assign('shopList', D('shop')->select()); //店铺名称
    }

    function index() {
        $model = D('file');
        $map['shop_id']=array('neq',"");
        $map['pic_url'] = array('neq', "");
        $map['type'] = array('eq', "1");
        if (!empty($_REQUEST['shop_id'])) {
            $map['shop_id'] = $_REQUEST['shop_id'];
        }
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
    }

    public function _before_add() {
        $shopList = D('shop')->select();
        $this->assign('shopList', $shopList);
    }


    public function insert() {
        ini_set('post_max_size', '256M');
        ini_set('upload_max_filesize', '100M');
	//log::write(print_r($_FILES,true), LOG::INFO);
        $data = $this->upload(); //调用upload函数,上传图片
	
        $model = D('file');
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        $this->up($_REQUEST['shop_id'], $data); //调用up函数,添加到数据库
        $this->success('新增成功');
    }

    public function upload() {
        ini_set('post_max_size', '256M');
        ini_set('upload_max_filesize', '100M');
	
        //$upload = new \Think\Upload($config);  //实例化上传类
        import('ORG.Net.UploadFile');
        $upload = new UploadFile(); // 实例化上传类
        $upload->maxSize = 314572800; // 设置附件上传大小
        $upload->saveRule = 'uniqid';
	
        $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg', 'mp4', 'flv','dae'); // 设置附件上传类型
        $upload->savePath = $_SERVER['DOCUMENT_ROOT'] . __ROOT__ . "/Public/upload/";
        log::write($_SERVER['DOCUMENT_ROOT'] . __ROOT__ . "/Public/upload/", LOG::INFO);
        $upload->uploadReplace = false; //存在同名是否覆盖
       
        if ($upload->upload()) {
            $info = $upload->getUploadFileInfo();
             log::write(print_r($info,true), LOG::INFO);
            return $info;
	   
        } else {
	exit;
            $this->success('失败');
        }
    }

    function up($list, $data) {
        $model = M('file');
        log::write(print_r(count($data), true) . 'werwrwr', LOG::INFO);
        $str = 'mp4flv';
        if (count($data) > 1) {//多次上传
            for ($i = 0; $i < count($data); $i++) {
                if (strpos($str, $data[$i]['extension']) === false) {
                    $picdata['shop_id'] = $list;
                    $picdata['type'] = 1; //1图片2视频
                    $picdata['pic_url'] = C('WEB_PATH') . $data[$i]['savename'];
                    $picdata['ptime'] = time();
                    $model->data($picdata)->add();
                } else {
                    $videodata['shop_id'] = $list;
                    $videodata['type'] = 2;
                    $videodata['video_url'] = C('WEB_PATH') . $data[$i]['savename'];
                    $videodata['ptime'] = time();
                    $model->data($videodata)->add();
                }
            }
        } else {
            if (strpos($str, $data[0]['extension']) === false) {
                $savedata['type'] = 1; //1图片2视频
                $savedata['pic_url'] = C('WEB_PATH') . $data[0]['savename'];
            } else {
                $savedata['type'] = 2;
                $savedata['video_url'] = C('WEB_PATH') . $data[0]['savename'];
            }
            $savedata['shop_id'] = $list;
            $savedata['ptime'] = time();
            $model->data($savedata)->add();
        }
    }

    public function delete() {
        $model = D('file');
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