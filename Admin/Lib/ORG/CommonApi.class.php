<?php

/**
 * Description of CommonApi
 * 本地API调用类
 * @author vini
 */
class CommonApi {

    //接口参数配置常量
    const PARAM_SOURCE = 4; //PC后台
    const WX_APPID = 'wx5c116960a3a1dcc8'; //微信APPID
    const ZFB_APPID = '2016090501850893'; //支付宝APPID

    /* 支付类接口 */
    const PAY_ORDER = '/Pay/order'; //支付下单接口
    const PAY_QUERY = '/Pay/query'; //订单查询接口
    const PAY_NOTIFY = '/Pay/notify'; //支付同步通知接口

    /* 业务类接口 */
    //派件业务
    const SERVICE_DISPATCH_PUT_ON = '/Dispatch/putOn'; //上架接口
    const SERVICE_DISPATCH_EXPRESS_SIGN = '/Dispatch/expresSign'; //JD签收接口
    const SERVICE_DISPATCH_VILLAGE_PHONES = '/Dispatch/addPhoneSendRecord'; //记录手机号社区发件信息
    //揽收业务接口
    const TAKING_EXPRESS = '/Taking/getVillageExpress'; //获取支持快递公司列表接口（无LOGO）

    protected static $API_URL_PREFIX = ''; //支付本地API地址
    protected static $API_URL_PREFIX_BUSINESS = ''; //业务本地API地址
    protected static $API_URL_PREFIX_PUSH = ''; //第三方接口本地API地址
    protected static $PLATFORM_API_URL_PREFIX = ''; //本地平台服务层API入口地址
    public $errCode = 4000;
    public $errMsg = "系统内部错误";

    public function __construct() {
        self::$API_URL_PREFIX = C('COMMON_API_URL') . '/index.php';
        self::$API_URL_PREFIX_BUSINESS = C('COMMON_API_URL') . '/service.php';
        self::$API_URL_PREFIX_PUSH = C('COMMON_API_URL') . '/push.php';
        self::$PLATFORM_API_URL_PREFIX = C('PLATFORM_API_URL');
    }

    /*     * ********************************************************************** */
    //                               PART-1 支付模块接口
    /*     * *********************************************************************** */

    /**
     * 支付下单接口
     * @param type $Aid     用户ID
     * @param type $Vid     社区ID
     * @param type $type    支付类型 1-支付宝 2-微信
     * @param type $channel 支付渠道 alipay_app:支付宝APP支付 alipay_wap:支付宝手机网页支付 alipay_pc_direct:支付宝即时到账支付 
     * alipay_qr:支付宝当面付 wx_app:微信APP支付 wx_pub:微信公众号支付 wx_qr:微信扫码支付 wx_wap:微信WAP支付
     * @param type $sort    支付种类 1-快递单费用 2-APP后台储蓄卡充值 3-APP后台揽收支付 4-PC后台现金上缴 5-一体机微信支付
     * @param type $amount  订单金额
     * @param type $extd1   扩展字段1
     * @param type $extd2   扩展字段2
     * @return boolean
     */
    public function pay($Aid, $Vid, $type, $channel, $sort, $amount, $extd1, $extd2) {
        $param['Aid'] = $Aid;
        $param['Vid'] = $Vid;
        $param['source'] = self::PARAM_SOURCE; //订单来源
        $param['type'] = $type; //支付方式 1-支付宝 2-微信
        if ($type == 1) {
            $param['appid'] = self::ZFB_APPID;
        } elseif ($type == 2) {
            $param['appid'] = self::WX_APPID;
        }
        $param['channel'] = $channel; //支付渠道
        $param['sort'] = $sort; //订单种类
        $param['amount'] = $amount; //订单金额
        if ($extd1) {
            $param['extd1'] = $extd1; //扩展字段1
        }
        if ($extd2) {
            $param['extd2'] = $extd2; //扩展字段2
        }
        $param['ip'] = $this->get_client_ip(); //用户IP

        $result = $this->http_post(self::$API_URL_PREFIX . self::PAY_ORDER, $param);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (int) $json['status'] !== 200) {
                $this->errCode = $json['status'];
                $this->errMsg = $json['message'];
                return false;
            }
            return $json['data'];
        }
        return false;
    }

    /**
     * 订单通知接口
     * @param string $order_no    订单号
     * @param int $status      前端支付结果
     * @return boolean
     */
    public function notify($order_no, $status, $type) {
        $param['order_no'] = $order_no;
        $param['result'] = $status;
        $param['type'] = $type; //支付方式 1-支付宝 2-微信
        if ($type == 1) {
            $param['appid'] = self::ZFB_APPID;
        } elseif ($type == 2) {
            $param['appid'] = self::WX_APPID;
        }

        $result = $this->http_post(self::$API_URL_PREFIX . self::PAY_NOTIFY, $param);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (int) $json['status'] !== 200) {
                $this->errCode = $json['status'];
                $this->errMsg = $json['message'];
                return false;
            }
            return $json['data'];
        }
        return false;
    }

    /**
     * 订单查询接口
     * @param type $order_no    订单号
     * @param type $type        支付类型
     * @return boolean          支付结果    1-未支付 2-支付成功 3-支付失败
     */
    public function query($order_no, $type) {
        $param['order_no'] = $order_no;
        $param['type'] = $type; //支付方式 1-支付宝 2-微信
        if ($type == 1) {
            $param['appid'] = self::ZFB_APPID;
        } elseif ($type == 2) {
            $param['appid'] = self::WX_APPID;
        }

        $result = $this->http_post(self::$API_URL_PREFIX . self::PAY_QUERY, $param);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (int) $json['status'] !== 200) {
                $this->errCode = $json['status'];
                $this->errMsg = $json['message'];
                return false;
            }
            return $json['data'];
        }
        return false;
    }

    /*     * ********************************************************************** */
    //                               PART-2 业务模块接口
    /*     * *********************************************************************** */

    /**
     * 上架接口
     * @param int $Aid          上架操作人ID【必填】
     * @param int $Vid          门店ID【必填】
     * @param int $ExpID        快递公司ID【必填】
     * @param string $ExpNum    快递单号【必填】
     * @param string $Smobile   收件人手机号【必填】
     * @param string $ConID     货架号
     * @param string $Sname     收件人姓名
     * @param string $Saddress  收件人地址
     * @param string $isPublic  是否对公件 1-是 0-否
     * @param string $ExpPic    快递单照片
     */
    public function putOn($Aid, $Vid, $ExpID, $ExpNum, $Smobile, $ConID = '', $Sname = '', $Saddress = '', $isPublic = 0, $ExpPic = '') {
        $param['source'] = self::PARAM_SOURCE; //上架渠道
        $param['Aid'] = $Aid;
        $param['Vid'] = $Vid;
        $param['ExpID'] = $ExpID;
        $param['ExpNum'] = $ExpNum;
        $param['Smobile'] = $Smobile;
        $param['ConID'] = $ConID;
        $param['Sname'] = $Sname;
        $param['Saddress'] = $Saddress;
        $param['isPublic'] = $isPublic;
        $param['ExpPic'] = $ExpPic;

        $result = $this->http_post(self::$PLATFORM_API_URL_PREFIX . self::SERVICE_DISPATCH_PUT_ON, $param);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (int) $json['status'] !== 0) {
                $this->errCode = $json['status'];
                $this->errMsg = $json['message'];
                return false;
            }
            return $json['data'];
        }
        return false;
    }

    /**
     * 京东签收接口
     * @param int $Aid          操作人ID【必填】
     * @param int $Did          派件记录ID【必填】
     * @param string $code      取件码【必填】
     */
    public function expressSign($Aid, $Did, $code) {
        $param['source'] = self::PARAM_SOURCE; //操作渠道
        $param['Aid'] = $Aid;
        $param['Did'] = $Did;
        $param['code'] = $code;

        $result = $this->http_post(self::$PLATFORM_API_URL_PREFIX . self::SERVICE_DISPATCH_EXPRESS_SIGN, $param);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (int) $json['status'] !== 0) {
                $this->errCode = $json['status'];
                $this->errMsg = $json['message'];
                return false;
            }
            return $json['data'];
        }
        return false;
    }

    /**
     * 功能：更新社区发件手机号
     * @param int    $Vid       社区编号
     * @param string $Smobile   手机号
     * @param string $Sname     姓名
     * @param string $Saddress  地址
     * @param int    $Bid       大客户ID
     * @return boolean
     */
    public function addPhoneSendRecord($Vid, $Smobile, $Sname = '', $Saddress = '', $Bid = '') {
        $param['Vid'] = $Vid;
        $param['Smobile'] = $Smobile;
        $param['Sname'] = $Sname;
        $param['Saddress'] = $Saddress;
        $param['Bid'] = $Bid;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$PLATFORM_API_URL_PREFIX . self::SERVICE_DISPATCH_VILLAGE_PHONES, $param);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (int) $json['status'] !== 0) {
                $this->errCode = $json['status'];
                $this->errMsg = $json['message'];
                return false;
            }
            return $json['data'];
        }
        return false;
    }

    /**
     * 功能：获取支持的快递公司
     * @param type $Vid 社区编号
     * @param type $type 使用场景类型 1-揽收 2-派件 3-大客户揽收
     * @return array 
     * Eid          快递公司编号
     * title        全称
     * Abbrevtitle  简称
     */
    public function get_express($Vid, $type) {
        $param['Vid'] = $Vid;
        $param['type'] = $type;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::TAKING_EXPRESS, $param);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (int) $json['status'] !== 200) {
                $this->errCode = $json['status'];
                $this->errMsg = $json['message'];
                return false;
            }
            return $json['data'];
        }
        return false;
    }

    /**
     * 日志记录，可被重载。
     * @param mixed $log 输入日志
     * @return mixed
     */
    protected function log($log) {
        Log::write(print_r($log, TRUE), LOG::INFO);
    }

    /**
     * GET 请求
     * @param string $url
     */
    protected function http_get($url) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $this->log("GET URL: " . $url);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $this->log($aStatus);
        if (intval($aStatus["http_code"]) == 200) {
            curl_close($oCurl);
            return $sContent;
        } else {
            $error = curl_errno($oCurl);
            $this->log("curl出错，错误码:$error");
            curl_close($oCurl);
            return false;
        }
    }

    /**
     * POST 请求
     * @param string $url
     * @param array $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    protected function http_post($url, $param, $post_file = false) {
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
        $this->log("POST URL: " . $url);
        $this->log("POST DATA: " . $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        //$this->log($aStatus);
        if (intval($aStatus["http_code"]) == 200) {
            curl_close($oCurl);
            return $sContent;
        } else {
            $error = curl_errno($oCurl);
            $this->log("curl出错，错误码:$error");
            curl_close($oCurl);
            return false;
        }
    }

    /**
     * 获取客户端设备类型
     * @param 
     * @return integer $type 返回类型 0-设备 1-IOS设备 2-安卓设备
     */
    protected function get_device_type() {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 0;
        //分别进行判断
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $type = 1;
        } elseif (strpos($agent, 'android')) {
            $type = 2;
        }
        return $type;
    }

    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @return mixed
     */
    function get_client_ip($type = 0) {
        $type = $type ? 1 : 0;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

}
