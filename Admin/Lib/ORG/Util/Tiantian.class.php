<?php
header("Content-Type: text/html; charset=UTF-8");
ini_set('display_errors', true);

class Tiantian {

    const API_URL_TEST = 'http://taobao.ttk.cn:8000/InterfacePlatform_test/ApiService';  //测试地址
    const API_URL = 'http://api.ttk.cn:22220/InterfacePlatform/ApiService';  //正式地址
    const appKey = 'SHOUFASHI';
    const appSecrt = 'bGo3U8Igs1';
    const serviceCode = 'ThridExpressTrackPush';

    public function Route($data) {

        $timestamp = time();
        $a['billcode'] = $data['billcode'];
        $a['action'] = $data['action'];
        $a['time'] = date("Y-m-d H:i:s");
        $a['operateSite'] = $data['operateSite'];
        $a['operateMan'] = $data['operateMan'];
        $a['desc'] = $data['desc'];
        if(!empty($data['signMan'])){
            $a['signMan'] = $data['signMan'];
        }
        $tracks['tracks'][] = $a;
        $params = json_encode($tracks);

        $content = self::appKey . self::serviceCode . $params . $timestamp . self::appSecrt;
        $digest = base64_encode(md5($content));

        $date['params'] = $params;
        $date['appKey'] = self::appKey;
        $date['serviceCode'] = self::serviceCode;
        $date['digest'] = $digest;
        $date['timestamp'] = $timestamp;

        $return = $this->http_post(self::API_URL, $date);
        return $return;
    }

    public function http_post($url, $param, $post_file = false) {
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

}


?>