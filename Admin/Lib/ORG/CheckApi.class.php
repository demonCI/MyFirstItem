<?php

/**
 * Description of CheckApi
 * 盘点API接口类
 * 
 * @author vini
 */
include_once 'CommonApi.class.php';

class CheckApi extends CommonApi {

    //盘点业务类接口
    const CHECK_CREATE = '/Check/create'; //创建盘点
    const GET_CURRENT_CHECK = '/Check/getCurrentCheck'; //1.获取当前盘点
    const GET_CHECK_HISTORY = '/Check/getCheckHistory'; //2.获取历史盘点
    const GET_CHECK_RESULT = '/Check/getCheckResult';   //3.获取盘点的扫描结果概况
    const GET_CHECK_RESULT_LIST = '/Check/getCheckResultList'; //4.获取盘点扫描结果列表
    const CHECK_SCAN = '/Check/scan'; //5.盘点扫描操作处理
    const CHECK_CORRECT = '/Check/correct'; //6.盘点更正操作处理
    const GET_CHECK_DEAL_TYPE = '/Check/getDealType'; //7.获取盘点少件处理类型
    const CHECK_DEAL = '/Check/deal'; //8.盘点少件处理
    const CHECK_END = '/Check/end'; //9.结束盘点扫描
    const CHECK_CLOSE = '/Check/close'; //10.关闭盘点

    /* ---------------------- 盘点相关API接口------------------------ */

    /**
     * 1. 功能：获取当前盘点
     * @param int $Vid 社区编号
     * @return Array
     */
    public function getCurrentCheck($Vid) {
        $param['Vid'] = $Vid;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::GET_CURRENT_CHECK, $param);
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
     * 2. 功能：获取历史盘点
     * @param int $Vid 社区编号
     * @return Array
     */
    public function getCheckHistory($Vid, $year = '', $month = '') {
        $param['Vid'] = $Vid;
        if ($year) {
            $param['year'] = $year;
        }
        if ($month) {
            $param['month'] = $month;
        }
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::GET_CHECK_HISTORY, $param);
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
     * 3. 功能：获取盘点的扫描结果概况
     * @param string $checkId   盘点ID
     * @return Array
     */
    public function getCheckResult($checkId) {
        $param['checkId'] = $checkId;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::GET_CHECK_RESULT, $param);
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
     * 4. 功能：获取盘点扫描结果列表
     * @param string $checkId   盘点ID
     * @param int    $type      盘点结果类型  1-多件 2-错放 3-少件
     * @param int    $pageNum   分页页码
     * @return Array
     */
    public function getCheckResultList($checkId, $type, $pageNum) {
        $param['checkId'] = $checkId;
        $param['type'] = $type;
        $param['pageNum'] = $pageNum;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::GET_CHECK_RESULT_LIST, $param);
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
     * 5. 功能：盘点扫描操作处理
     * @param int    $Aid       扫描用户ID
     * @param string $checkId   盘点ID
     * @param string $conKey    当前扫码货架号
     * @param string $expNum    当前扫码快递单号
     * @return boolean  true/false
     */
    public function checkScan($Aid, $checkId, $conKey, $expNum) {
        $param['Aid'] = $Aid;
        $param['checkId'] = $checkId;
        $param['conKey'] = $conKey;
        $param['expNum'] = $expNum;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::CHECK_SCAN, $param);
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
     * 6. 功能：盘点更正操作处理
     * @param int    $Aid       更正操作用户ID
     * @param string $checkId   盘点ID
     * @param string $recordId  盘点结果记录ID
     * @return boolean  true/false
     */
    public function checkCorrect($Aid, $checkId, $recordId) {
        $param['Aid'] = $Aid;
        $param['checkId'] = $checkId;
        $param['recordId'] = $recordId;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::CHECK_CORRECT, $param);
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
     * 7. 功能：获取盘点少件处理类型
     * @return Array
     */
    public function getCheckDealType() {
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::GET_CHECK_DEAL_TYPE, $param);
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
     * 8. 功能：盘点少件处理
     * @param int    $Aid       少件处理操作用户ID
     * @param string $checkId   盘点ID
     * @param string $recordId  盘点结果记录ID
     * @param string $value     处理方式取值
     * @return Array
     */
    public function checkDeal($Aid, $checkId, $recordId, $value) {
        $param['Aid'] = $Aid;
        $param['checkId'] = $checkId;
        $param['recordId'] = $recordId;
        $param['value'] = $value;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::CHECK_DEAL, $param);
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
     * 9. 功能：结束盘点扫描
     * @param int    $Aid       结束扫描操作用户ID
     * @param string $checkId   盘点ID
     * @return Array
     */
    public function end($Aid, $checkId) {
        $param['Aid'] = $Aid;
        $param['checkId'] = $checkId;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::CHECK_END, $param);
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
     * 10. 功能：关闭盘点
     * @param int    $Aid       关闭盘点操作用户ID
     * @param string $checkId   盘点ID
     * @return Array
     */
    public function close($Aid, $checkId) {
        $param['Aid'] = $Aid;
        $param['checkId'] = $checkId;
        $param['resource'] = self::PARAM_SOURCE;

        $result = $this->http_post(self::$API_URL_PREFIX_BUSINESS . self::CHECK_CLOSE, $param);
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

}
