<?php

/**
 * Created by PhpStorm.
 * User: StandOpen
 * Date: 15-1-7
 * Time: 9:41
 */
class OrderPush {

    protected $appid;
    protected $secrect;
    protected $accessToken;

    function __construct($appid, $secrect) {
        $this->appid = $appid;
        $this->secrect = $secrect;
        $this->accessToken = $this->getToken($appid, $secrect);
    }

    /**
     * 发送post请求
     * @param string $url
     * @param string $param
     * @return bool|mixed
     */
    function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }

    /**
     * 发送get请求
     * @param string $url
     * @return bool|mixed
     */
    function request_get($url = '') {
        if (empty($url)) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * @return mixed
     * 获取token
     */
    protected function getToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $json_file = C('ACCESS_TOKEN_FILE_WJ');
        $data = json_decode(file_get_contents($json_file));
        if (empty($data) || $data->expire_time < time()) {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->secrect";
            $res = json_decode($this->request_get($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $fp = fopen($json_file, "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }

    /**
     * 发送自定义的模板消息
     * @param $touser
     * @param $template_id
     * @param $url
     * @param $data
     * @param string $topcolor
     * @return bool
     */
    public function doSend($touser, $template_id, $url, $data, $topcolor = '#7B68EE') {
        $template = array(
            'touser' => $touser,
            'template_id' => $template_id,
            'url' => $url,
            'topcolor' => $topcolor,
            'data' => $data
        );
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $this->accessToken;
        log::write(print_r("后台：旧微信请求参数  \t" . json_encode($template), true), LOG::INFO, '', '/data/www/shoufashi/logs/website/wx_push.log');

        $dataRes = $this->request_post($url, urldecode(json_encode($template)));
        log::write(print_r("后台：旧微信返回结果  \t" . json_encode($dataRes), true), LOG::INFO, '', '/data/www/shoufashi/logs/website/wx_push.log');
        if ($dataRes['errcode'] == 0) {
            return true;
        } else {
            return false;
        }
    }

}
