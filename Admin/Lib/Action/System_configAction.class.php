<?php

class System_configAction extends CommonAction {

    // 插入数据
    public function index() {
       
        $this->display();
    }

  
    public function _before_insert() {
        ini_set('post_max_size', '256M');
        ini_set('upload_max_filesize', '100M');
        log::write(print_r($_FILES,true), LOG::INFO);
        log::write(print_r($_FILES['img']['error'], true), LOG::INFO);
        log::write(print_r($_FILES['img']['tmp_name'], true), LOG::INFO);
        if (empty($_FILES['img']['tmp_name'])) {
            log::write(111111, LOG::INFO);
            $this->error('请选择上传图片/视频');
        } else {
            $data = $this->upload(); //调用upload函数,上传图片
            log::write(print_r($data, true), LOG::INFO);
            if (isset($data)) {
                $model = D('shop');
                if (false === $model->create()) {
                    $this->error($model->getError());
                }
                $list = $model->add();
                if ($list !== false) {
                    $models = M();
                    $timer = time();
                    $models->query("insert into tb_user set account='" . $_REQUEST['name'] . "',password='" . $passwd . "',pwd='" . $_REQUEST['password'] . "',type=1,ext_table='tb_shop',ext_id='" . $list . "',status=1,create_time='" . $timer . "'");
                    $this->up($list, $data); //调用up函数,添加到数据库
                    $this->success('新增成功');
                }
            } else {
                $this->error('上传图片失败');
            }
        }
    }

    public function upload() {
        ini_set('post_max_size', '256M');
        ini_set('upload_max_filesize', '100M');
        import('ORG.Net.UploadFile');
        $upload = new UploadFile(); // 实例化上传类
        $upload->maxSize = 314572800; // 设置附件上传大小
        $upload->saveRule = 'uniqid';
        $upload->allowExts = array('jpg', 'gif', 'png', 'jpeg', 'mp4', 'flv'); // 设置附件上传类型
        $upload->savePath = $_SERVER['DOCUMENT_ROOT'] . __ROOT__ . "/Public/upload/";
        log::write($_SERVER['DOCUMENT_ROOT'] . __ROOT__ . "/Public/upload/", LOG::INFO);
        $upload->uploadReplace = false; //存在同名是否覆盖
        if ($upload->upload()) {
            $info = $upload->getUploadFileInfo();
            return $info;
        } else {
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

}

?>