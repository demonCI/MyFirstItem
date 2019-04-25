<?php

// +----------------------------------------------------------------------
// | ThinkPHP
// +----------------------------------------------------------------------
// | Copyright (c) 2007 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$
//公共函数
function getFieldById($id, $moudle, $field = 'name') {
    if (!$id) {
        return '';
    }
    $model = D($moudle);
    $title = $model->where("id='$id'")->getField($field);
    return $title;
}

//数组转XML
function arrayToXml($arr) {
    $xml = '';
    foreach ($arr as $key => $val) {
        if (is_array($val)) {
            $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
        } else {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        }
    }
    return $xml;
}

//POST提交数据
function http_post($url, $param, $post_file = false) {
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    if (is_string($param) || $post_file) {
        $strPOST = $param;
    } else {
        $strPOST = http_build_query($param);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
    $headers = array(
        'Content-Type: application/x-www-form-urlencoded; charset=utf-8'
    );
    curl_setopt($oCurl, CURLOPT_HTTPHEADER, $headers);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        curl_close($oCurl);
        return $sContent;
    } else {
        $error = curl_errno($oCurl);
        curl_close($oCurl);
        return false;
    }
}

//XML转数组
function xmlToArray($xml) {
    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $values;
}

//时间戳转时间
function xxxgetDate($date){
        $ptime = date("Y-m-d H:i:s",$date);
        return $ptime;
}

// 缓存文件
function cmssavecache($name = '', $fields = '') {
    $Model = D($name);
    $list = $Model->select();
    $data = array();
    foreach ($list as $key => $val) {
        if (empty($fields)) {
            $data [$val [$Model->getPk()]] = $val;
        } else {
            // 获取需要的字段
            if (is_string($fields)) {
                $fields = explode(',', $fields);
            }
            if (count($fields) == 1) {
                $data [$val [$Model->getPk()]] = $val [$fields [0]];
            } else {
                foreach ($fields as $field) {
                    $data [$val [$Model->getPk()]] [] = $val [$field];
                }
            }
        }
    }
    $savefile = cmsgetcache($name);
    // 所有参数统一为大写
    $content = "<?php\nreturn " . var_export(array_change_key_case($data, CASE_UPPER), true) . ";\n?>";
    file_put_contents($savefile, $content);
}

function cmsgetcache($name = '') {
    return DATA_PATH . '~' . strtolower($name) . '.php';
}

function getStatus($status) {
    switch ($status) {
        case 1 :
            $showText = '待受理';
            break;
        case 2 :
            $showText = '已受理';
            break;
        case 3 :
            $showText = '维修完成';
            break;
         case 4 :
            $showText = '验收通过';
            break;
        default :
            $showText = '正常';
    }
    return  $showText;
}

function IP($ip = '', $file = 'UTFWry.dat') {
    $_ip = array();
    if (isset($_ip [$ip])) {
        return $_ip [$ip];
    } else {
        import("ORG.Net.IpLocation");
        $iplocation = new IpLocation($file);
        $location = $iplocation->getlocation($ip);
        $_ip [$ip] = $location ['country'] . $location ['area'];
    }
    return $_ip [$ip];
}

function getNodeName($id) {
    if (Session::is_set('nodeNameList')) {
        $name = Session::get('nodeNameList');
        return $name [$id];
    }
    $Group = D("Node");
    $list = $Group->getField('id,name');
    $name = $list [$id];
    Session::set('nodeNameList', $list);
    return $name;
}

function getNodeGroupName($id) {
    if (empty($id)) {
        return '未分组';
    }
    if (isset($_SESSION ['nodeGroupList'])) {
        return $_SESSION ['nodeGroupList'] [$id];
    }
    $Group = D("Group");
    $list = $Group->getField('id,title');
    $_SESSION ['nodeGroupList'] = $list;
    $name = $list [$id];
    return $name;
}

// zhanghuihua@msn.com
function showStatus($status, $id, $callback = "") {
    switch ($status) {
        case 0 :
            $info = '<a href="__URL__/resume/id/' . $id . '/navTabId/__MODULE__" target="ajaxTodo" callback="' . $callback . '">恢复</a>';
            break;
        case 2 :
            $info = '<a href="__URL__/pass/id/' . $id . '/navTabId/__MODULE__" target="ajaxTodo" callback="' . $callback . '">批准</a>';
            break;
        case 1 :
            $info = '<a href="__URL__/forbid/id/' . $id . '/navTabId/__MODULE__" target="ajaxTodo" callback="' . $callback . '">禁用</a>';
            break;
        case - 1 :
            $info = '<a href="__URL__/recycle/id/' . $id . '/navTabId/__MODULE__" target="ajaxTodo" callback="' . $callback . '">还原</a>';
            break;
    }
    return $info;
}

/**
  +----------------------------------------------------------
 * 获取登录验证码 默认为4位数字
  +----------------------------------------------------------
 * @param string $fmode 文件名
  +----------------------------------------------------------
 * @return string
  +----------------------------------------------------------
 */
function build_verify($length = 4, $mode = 1) {
    return rand_string($length, $mode);
}

/*
 * @author vini 推送消息发送
 * +---------------------------------------------------------------
 * @param $msgid 推送的消息ID
 * @param $token 接收方推送标识（IOS:与设备绑定-设备唯一标识，ANDRIOD:与用户绑定-用户ID）
 * @param $type 推送消息类型 1-只在消息中心显示的消息 2-“我”模块中的消息
 * @param $message 要推送的消息数组
 *
 */

function push($msgid, $token, $type, $title, $message) {
    log::write("PUSH111111111:" . $msgid . "-" . $token . "-" . $title . "-" . $message . "-" . $msg_content);
    if (strlen($token) != 64) {
        import('@.ORG.Util.PushAndriod');
        $alias = $token; //用户标识，与用户绑定，所有终端的同一用户都可收到推送消息
        $ANDRIOD = new jpush;
        $msg_content = json_encode(array('message' => array('msg_content' => $message, 'title' => $title, 'content_type' => $type, 'extras' => $msgid)));
        $res = $ANDRIOD->send($msgid, 2, $alias, 2, $msg_content, 'android');
    } else {
        $deviceToken = $token;  //设备标识，与设备绑定，只有用户最后一次登陆的设备可收到推送消息
        $passphrase = '123123';
        $msg_content = json_encode(array('aps' => array('alert' => $message, 'sound' => 'default', 'title' => $title, 'content_type' => $type, 'extras' => $msgid)));
        import('@.ORG.Util.PushIos');
        $IOS = new iospush($deviceToken, $passphrase);
        $res = $IOS->send($msg_content);
    }
    log::write(print_r("PUSH:" . $msgid . "-" . $token . "-" . $title . "-" . $message . "-" . $msg_content, TRUE), LOG::INFO);
    log::write(print_r($res, TRUE), LOG::INFO);
    return $res;
}

/**
 * @author vini 推送消息发送
 * +---------------------------------------------------------------
 * @param $sender 发送者ID
 * @param $receiver 接收者ID
 * @param $module 消息模块来源 1-我的报修 2-保洁预约 3-社区直供 4-我的社区 5-我的快递 6-系统公告
 * @param $type 消息类型
 * @param $title 消息标题
 * @param $content 消息内容
 * @param $isPush 是否推送标识 1-推送 0-不推送
 *
 */
function send_msg($msgid, $sender, $receiver, $module, $type, $title, $content, $isPush = '0') {
    Log::write('send_msg::::::' . $msgid . '---' . $sender . '---' . $receiver . '---' . $module . '---' . $type . '---' . $title . '---' . $content . '');
    $model = M();
    $sql = "INSERT INTO tb_message SET id='$msgid',sender='$sender',receiver='$receiver',type='$type',title='$title',content='$content',is_push='$isPush',ptime=now()";
    Log::write(print_r($sql, true), LOG::SQL);
    $model->query($sql);
    $row = $model->query("SELECT uuid FROM tb_account WHERE id='$receiver' LIMIT 1");
    Log::write($model->getLastSql(), LOG::SQL);
    //消息需要推送时，发出推送
    if ($isPush == 1) {
        $flag = 3;
        $token = $row[0]['uuid'];
        $r = push($msgid, $token, $flag, $title, $content);
    }
    $input = $msgid . '---' . $token . '---' . $flag . '---' . $title . '---' . $content;
    add_log('send_msg', $input, $r);   ///增加日志
}

function getGroupName($id) {
    if ($id == 0) {
        return '无上级组';
    }
    if ($list = F('groupName')) {
        return $list [$id];
    }
    $dao = D("Role");
    $list = $dao->select(array('field' => 'id,name'));
    foreach ($list as $vo) {
        $nameList [$vo ['id']] = $vo ['name'];
    }
    $name = $nameList [$id];
    F('groupName', $nameList);
    return $name;
}

function sort_by($array, $keyname = null, $sortby = 'asc') {
    $myarray = $inarray = array();
    # First store the keyvalues in a seperate array
    foreach ($array as $i => $befree) {
        $myarray [$i] = $array [$i] [$keyname];
    }
    # Sort the new array by
    switch ($sortby) {
        case 'asc' :
            # Sort an array and maintain index association...
            asort($myarray);
            break;
        case 'desc' :
        case 'arsort' :
            # Sort an array in reverse order and maintain index association
            arsort($myarray);
            break;
        case 'natcasesor' :
            # Sort an array using a case insensitive "natural order" algorithm
            natcasesort($myarray);
            break;
    }
    # Rebuild the old array
    foreach ($myarray as $key => $befree) {
        $inarray [] = $array [$key];
    }
    return $inarray;
}

/**
  +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码
 * 默认长度6位 字母和数字混合 支持中文
  +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 其它 混合
 * @param string $addChars 额外字符
  +----------------------------------------------------------
 * @return string
  +----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '') {
    $str = '';
    switch ($type) {
        case 0 :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 1 :
            $chars = str_repeat('0123456789', 3);
            break;
        case 2 :
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3 :
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        default :
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
            break;
    }
    if ($len > 10) { //位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    if ($type != 4) {
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
    } else {
        // 中文随机字
        for ($i = 0; $i < $len; $i ++) {
            $str .= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
        }
    }
    return $str;
}

function pwdHash($password, $type = 'md5') {
    return hash($type, $password);
}

/* zhanghuihua */

function percent_format($number, $decimals = 0) {
    return number_format($number * 100, $decimals) . '%';
}

/**
 * 动态获取数据库信息
 * @param $tname 表名
 * @param $where 搜索条件
 * @param $order 排序条件 如："id desc";
 * @param $count 取前几条数据 
 */
function findList($tname, $where = "", $order, $count) {
    $m = M($tname);
    if (!empty($where)) {
        $m->where($where);
    }
    if (!empty($order)) {
        $m->order($order);
    }
    if ($count > 0) {
        $m->limit($count);
    }
    return $m->select();
}

/*
 * 检查指定目录是否存在，不存在则创建目录
 */
function CreateAllDir($dir) {
    $dir_array = explode("/", $dir);
    $DirName = "";
    for ($i = 0; $i < count($dir_array); $i++) {
        if ($dir_array[$i] != "") {
            $DirName .= "/" . $dir_array[$i];
            if (!is_dir($DirName)) {
                @mkdir($DirName, 0755);
            }
        }
    }
}

?>
