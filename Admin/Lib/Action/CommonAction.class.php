<?php

class CommonAction extends Action {

    public $id;

    function _initialize() {
        @session_start();
        if (empty($_SESSION[C('USER_AUTH_KEY')]) || empty($_SESSION['account']) || ( (empty($_SESSION['Comp'])) && (empty($_SESSION['Com'])) && $_SESSION['administrator'] == '' && empty($_SESSION['Vid']))) {
            unset($_SESSION[C('USER_AUTH_KEY')]);
            unset($_SESSION);
            session_destroy();
            $this->assign("jumpUrl", __URL__ . '/login/');
        }
        import('@.ORG.Util.Cookie');
        // 用户权限检查
        if (C('USER_AUTH_ON') && !in_array(MODULE_NAME, explode(',', C('NOT_AUTH_MODULE')))) {
            import('@.ORG.Util.RBAC');
            if (!RBAC::AccessDecision()) {
                //检查认证识别号
                if (!$_SESSION [C('USER_AUTH_KEY')]) {
                    if ($this->isAjax()) { // zhanghuihua@msn.com
                        $this->ajaxReturn(true, "", 301);
                    } else {
                        //跳转到认证网关
                        redirect(PHP_FILE . C('USER_AUTH_GATEWAY'));
                    }
                }
                // 没有权限 抛出错误
                if (C('RBAC_ERROR_PAGE')) {
                    // 定义权限错误页面
                    redirect(C('RBAC_ERROR_PAGE'));
                } else {
                    if (C('GUEST_AUTH_ON')) {
                        $this->assign('jumpUrl', PHP_FILE . C('USER_AUTH_GATEWAY'));
                    }
                    // 提示错误信息
                    $this->error(L('_VALID_ACCESS_'));
                }
            }
        }
    }

    public function index() {
        //列表过滤器，生成查询Map对象
        $map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $name = $this->getActionName();
        $model = D($name);
//        Log::write(print_r($map,true),LOG::SQL);
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
        return;
    }

    /**
      +----------------------------------------------------------
     * 取得操作成功后要返回的URL地址
     * 默认返回当前模块的默认操作
     * 可以在action控制器中重载
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    function getReturnUrl() {
        return __URL__ . '?' . C('VAR_MODULE') . '=' . MODULE_NAME . '&' . C('VAR_ACTION') . '=' . C('DEFAULT_ACTION');
    }

    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param string $name 数据对象名称
     * @param string $search_field 数据对象允许查询字段
     * @param string $alias 数据表别名
      +----------------------------------------------------------
     * @return HashMap
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected function _search($name = '', $search_field = '', $alias = '') {
        //生成查询条件
//        Log::write(print_r($_REQUEST, true), LOG::SQL);
        if (empty($name)) {
            $name = $this->getActionName();
        }
        if (!empty($alias)) {
            $alias .= '.';
        }
        $model = D($name);
        $map = array();
        if ($search_field == '') {
            foreach ($model->getDbFields() as $key => $val) {
                if (isset($_REQUEST[$val]) && $_REQUEST[$val] != '') {
                    $map[$alias . $val] = $_REQUEST [$val];
                }
            }
        } else {
            $search_field = is_array($search_field) ? $search_field : explode(',', $search_field);
            foreach ($model->getDbFields() as $key => $val) {
                if (isset($_REQUEST[$val]) && $_REQUEST[$val] != '' && in_array($val, $search_field)) {
                    $map[$alias . $val] = $_REQUEST[$val];
                }
            }
        }
//        Log::write(print_r($map, true), LOG::INFO);
        return $map;
    }

    /**
      +----------------------------------------------------------
     * 根据表单生成查询条件
     * 进行列表过滤
      +----------------------------------------------------------
     * @access protected
      +----------------------------------------------------------
     * @param Model $model 数据对象
     * @param HashMap $map 过滤条件
     * @param string $sortBy 排序
     * @param boolean $asc 是否正序
      +----------------------------------------------------------
     * @return void
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    protected function _list($model, $map, $sortBy = '', $asc = false, $countPk = '*') {
        //排序字段 默认为主键名
        if (!empty($_REQUEST ['_order'])) {
            $order = $_REQUEST ['_order'];
        } else {
            $order = !empty($sortBy) ? $sortBy : $model->getPk();
        }
        //排序方式默认按照倒序排列
        //接受 sost参数 0 表示倒序 非0都 表示正序
        if (isset($_REQUEST ['_sort'])) {
//			$sort = $_REQUEST ['_sort'] ? 'asc' : 'desc';
            $sort = $_REQUEST ['_sort'] == 'asc' ? 'asc' : 'desc'; //zhanghuihua@msn.com
        } else {
            $sort = $asc ? 'asc' : 'desc';
        }
        //取得满足条件的记录数
        $count = $model->where($map)->count($countPk);
        //Log::write($model->getLastSql(),LOG::SQL);
        if ($count > 0) {
            import("@.ORG.Util.Page");
            //创建分页对象
            if (!empty($_REQUEST ['listRows'])) {
                $listRows = $_REQUEST ['listRows'];
            } else {
                $listRows = '';
            }
            $p = new Page($count, $listRows);
            //分页查询数据
            $voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($p->firstRow . ',' . $p->listRows)->select();
            Log::write($model->getLastSql(), LOG::SQL);
            //分页跳转的时候保证查询条件
            foreach ($map as $key => $val) {
                if (!is_array($val)) {
                    $p->parameter .= "$key=" . urlencode($val) . "&";
                }
            }
            //分页显示
            $page = $p->show();
            //列表排序显示
            $sortImg = $sort; //排序图标
            $sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
            $sort = $sort == 'desc' ? 1 : 0; //排序方式
            //模板赋值显示
            $this->assign('list', $voList);
            $this->assign('sort', $sort);
            $this->assign('order', $order);
            $this->assign('sortImg', $sortImg);
            $this->assign('sortType', $sortAlt);
            $this->assign("page", $page);
        }

        //zhanghuihua@msn.com
        $this->assign('totalCount', $count);
        $this->assign('numPerPage', $p->listRows);
        $this->assign('currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

        Cookie::set('_currentUrl_', __SELF__);
        return;
    }

    function insert() {
        //B('FilterString');
        $name = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        //ID从tb_id中依据表名来取
        $id = $this->getMaxId($name);
        $this->id = $id;
        $model->__set("id", $id);
        //保存当前数据对象
        $list = $model->add();
        if ($list !== false) { //保存成功
            $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
            $this->success('新增成功!');
        } else {
            //失败提示
            $this->error('新增失败!');
        }
    }

    public function add() {
        $this->display();
    }

    function read() {
        $this->edit();
    }

    function edit() {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_REQUEST[$pk];
        $vo = $model->where("$pk=$id")->find();
        $this->assign('vo', $vo);
        $this->display();
    }

    function update() {
        //B('FilterString');
        $name = $this->getActionName();
        $model = D($name);
        if (false === $model->create()) {
            $this->error($model->getError());
        }
        // 更新数据
        $list = $model->save();
        if (false !== $list) {
            //成功提示
            $this->assign('jumpUrl', Cookie::get('_currentUrl_'));
            $this->success('编辑成功!');
        } else {
            //错误提示
            $this->error('编辑失败!');
        }
    }

    /**
     * 查找带回
     *
     *
     *
     *
     *
     */
    public function lookup($modelname = "") {
        //log::write(print_r($_REQUEST,true),LOG::INFO);
        if ($_REQUEST['modelname'] != "") {
            $modelname = $_REQUEST['modelname'];
        } else {
            $modelname = $this->getActionName();
        }
        //列表过滤器，生成查询Map对象
        $map = $this->_search($modelname);
        $map['status'] = 1;
        //log::write(print_r($map,true),LOG::INFO);
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }

        $model = D($modelname);
        if (!empty($model)) {

            $this->_list($model, $map);
        }
        //	 log::write($model->getLastSql(),LOG::INFO);
        $this->display("$modelname:lookup");
        return;
    }

    /**
     * 查找带回
     * 
     * 
     * 
     * 
     * 
     */
    public function lookup_suggest($modelname = "", $title = "title") {
        //log::write(print_r($_REQUEST,true),LOG::INFO);
        if ($_REQUEST['modelname'] != "") {
            $modelname = $_REQUEST['modelname'];
        }
        if ($_REQUEST['fieldname'] != "") {
            $title = $_REQUEST['fieldname'];
        }

        $model = D($modelname);
        if (!empty($model)) {
            //列表过滤器，生成查询Map对象
            $_REQUEST['status'] = 1;
            $map = array();
            foreach ($model->getDbFields() as $key => $val) {
                if (isset($_REQUEST [$val]) && $_REQUEST [$val] != '') {
                    $map [$val] = $_REQUEST [$val];
                }
            }
            //log::write(print_r($map,true),LOG::INFO);
            $str = "[{\"id\":\"0\", \"title\":\"空\", \"mid\":\"0\"},";
            $mresults = $model->where($map)->field("id,$title")->findAll();
            foreach ($mresults as $mresult) {
                $str .= "{\"id\":\"" . $mresult['id'] . "\", \"title\":\"" . $mresult[$title] . "\", \"mid\":\"" . $mresult['id'] . "\"},";
            }
        }
        //	 log::write("sugg_sql:".$model->getLastSql(),LOG::INFO);
        if (substr($str, strlen($str) - 1, 1) == ",") {
            $str = substr($str, 0, strlen($str) - 1);
        }
        $str .= "]";
        echo $str;
        //	 log::write(print_r($str,true),LOG::INFO);
        return;
    }

    /**
      +----------------------------------------------------------
     * 默认删除操作
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws ThinkExecption
      +----------------------------------------------------------
     */
    public function delete() {
        //删除指定记录
        $name = $this->getActionName();
        $model = M($name);
        if (!empty($model)) {
            $pk = $model->getPk();
            $id = $_REQUEST [$pk];
            if (isset($id)) {
                $condition = array($pk => array('in', explode(',', $id)));
                $list = $model->where($condition)->setField('status', - 1);
                if ($list !== false) {
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error('非法操作');
            }
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
                    //echo $model->getlastsql();
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

    public function clear() {
        //删除指定记录
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            if (false !== $model->where('status=-1')->delete()) { // zhanghuihua@msn.com change status=1 to status=-1
                $this->assign("jumpUrl", $this->getReturnUrl());
                $this->success(L('_DELETE_SUCCESS_'));
            } else {
                $this->error(L('_DELETE_FAIL_'));
            }
        }
        $this->forward();
    }

    /**
      +----------------------------------------------------------
     * 默认禁用操作
     *
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws FcsException
      +----------------------------------------------------------
     */
    public function forbid() {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_REQUEST [$pk];
        $condition = array($pk => array('in', $id));
        $list = $model->forbid($condition);
        if ($list !== false) {
            $this->assign("jumpUrl", $this->getReturnUrl());
            $this->success('状态禁用成功');
        } else {
            $this->error('状态禁用失败！');
        }
    }

    public function checkPass() {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_GET [$pk];
        $condition = array($pk => array('in', $id));
        if (false !== $model->checkPass($condition)) {
            $this->assign("jumpUrl", $this->getReturnUrl());
            $this->success('状态批准成功！');
        } else {
            $this->error('状态批准失败！');
        }
    }

    public function recycle() {
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_GET [$pk];
        $condition = array($pk => array('in', $id));
        if (false !== $model->recycle($condition)) {

            $this->assign("jumpUrl", $this->getReturnUrl());
            $this->success('状态还原成功！');
        } else {
            $this->error('状态还原失败！');
        }
    }

    public function recycleBin() {
        $map = $this->_search();
        $map ['status'] = - 1;
        $name = $this->getActionName();
        $model = D($name);
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
    }

    /**
      +----------------------------------------------------------
     * 默认恢复操作
     *
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @return string
      +----------------------------------------------------------
     * @throws FcsException
      +----------------------------------------------------------
     */
    function resume() {
        //恢复指定记录
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_GET [$pk];
        $condition = array($pk => array('in', $id));
        if (false !== $model->resume($condition)) {
            $this->assign("jumpUrl", $this->getReturnUrl());
            $this->success('状态恢复成功！');
        } else {
            $this->error('状态恢复失败！');
        }
    }

    function saveSort() {
        $seqNoList = $_POST ['seqNoList'];
        if (!empty($seqNoList)) {
            //更新数据对象
            $name = $this->getActionName();
            $model = D($name);
            $col = explode(',', $seqNoList);
            //启动事务
            $model->startTrans();
            foreach ($col as $val) {
                $val = explode(':', $val);
                $model->id = $val [0];
                $model->sort = $val [1];
                $result = $model->save();
                if (!$result) {
                    break;
                }
            }
            //提交事务
            $model->commit();
            if ($result !== false) {
                //采用普通方式跳转刷新页面
                $this->success('更新成功');
            } else {
                $this->error($model->getError());
            }
        }
    }

    /*     * *********************************************************************** */

    // @author lee 2015-05-05
    //从Tb_id表中获取表ID
    function getMaxID($title = "", $step = 1) {
        if ($title == "") {
            $title = $this->getActionName();
        }
        $model = D('Id');
        $id = $model->where("title='" . $title . "'")->getField('id');
        if ($id < 100) {
            $data['id'] = 100;
            $data['title'] = $title;
            $data['pdate'] = date('Y-m-d H:i:s');
            $model->add($data);
        } else {
            $model->where("title='" . $title . "'")->setInc('id', $step);
        }
        $newid = $model->where("title='" . $title . "'")->getField('id');
        return $newid;
    }

    public function int_toABC($k) {
        if ($k == '0') {
            $r = 'A';
        } else if ($k == '1') {
            $r = 'B';
        } else if ($k == '2') {
            $r = 'C';
        } else if ($k == '3') {
            $r = 'D';
        } else if ($k == '4') {
            $r = 'E';
        } else if ($k == '5') {
            $r = 'F';
        } else if ($k == '6') {
            $r = 'G';
        } else if ($k == '7') {
            $r = 'H';
        } else if ($k == '8') {
            $r = 'I';
        } else if ($k == '9') {
            $r = 'J';
        } else if ($k == '10') {
            $r = 'K';
        } else if ($k == '11') {
            $r = 'L';
        } else if ($k == '12') {
            $r = 'M';
        } else if ($k == '13') {
            $r = 'N';
        } else if ($k == '14') {
            $r = 'O';
        } else if ($k == '15') {
            $r = 'P';
        } else if ($k == '16') {
            $r = 'Q';
        } else if ($k == '17') {
            $r = 'R';
        } else if ($k == '18') {
            $r = 'S';
        } else if ($k == '19') {
            $r = 'T';
        } else if ($k == '20') {
            $r = 'U';
        } else if ($k == '21') {
            $r = 'V';
        } else if ($k == '22') {
            $r = 'W';
        } else if ($k == '23') {
            $r = 'X';
        } else if ($k == '24') {
            $r = 'Y';
        } else if ($k == '25') {
            $r = 'Z';
        } else if ($k == '26') {
            $r = '';
        }
        return $r;
    }

    public function dis_cou_one($k) {
        if ($k == '1') {
            $r = '编号';
        } else if ($k == '2') {
            $r = '货架位置';
        } else if ($k == '3') {
            $r = '快递公司';
        } else if ($k == '4') {
            $r = '快递单号';
        } else if ($k == '5') {
            $r = '公司ID';
        } else if ($k == '6') {
            $r = '收货人姓名';
        } else if ($k == '7') {
            $r = '收货人电话';
        } else if ($k == '8') {
            $r = '收货人地址';
        } else if ($k == '9') {
            $r = '到达时间';
        } else if ($k == '10') {
            $r = '用户收件时间';
        } else if ($k == '11') {
            $r = '快件状态';
        } else if ($k == '12') {
            $r = '派送人';
        } else if ($k == '13') {
            $r = '预计达到时间';
        }
        return $r;
    }

//导出excel
    public function toExcel($rs) {
        //获取数组个人信息
        import('@.ORG.Excel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
        $col = 2;
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '编号')
                ->setCellValue('B1', '公司ID')
                ->setCellValue('C1', '快递公司')
                ->setCellValue('D1', '快递单号')
                ->setCellValue('E1', '收货人姓名')
                ->setCellValue('F1', '收货人电话')
                ->setCellValue('G1', '签收人');
        $objPHPExcel->setActiveSheetIndex(0);
        foreach ($rs as $k => $v) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $col, $v['id'])
                    ->setCellValue('B' . $col, getFieldById($v['companyid'], 'biginfo', $title = 'companyid'))
                    ->setCellValue('C' . $col, getFieldById($v['ExpID'], 'express', $title = 'title'))
                    ->setCellValue('D' . $col, $v['ExpNum'])
                    ->setCellValue('E' . $col, $v['Sname'])
                    ->setCellValue('F' . $col, $v['Smobile'])
                    ->setCellValue('G' . $col, getFieldById($v['Vid'], 'Village', $title = 'name'));
            $col++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('收件信息'); //设置sheet标签的名称
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();  //清空缓存
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=收件查询.xls'); //设置文件的名称
        header("Content-Transfer-Encoding:binary");
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function toExcelcopy($rs) {
        //获取数组个人信息
        import('@.ORG.Excel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
        $col = 2;
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '编号')
                ->setCellValue('B1', '社区名称')
                ->setCellValue('C1', '公司名称')
                ->setCellValue('D1', '公司id')
                ->setCellValue('E1', '地址')
                ->setCellValue('F1', '发票抬头')
                ->setCellValue('G1', 'email')
                ->setCellValue('H1', '联系方式')
                ->setCellValue('I1', '备注');
        $objPHPExcel->setActiveSheetIndex(0);
        foreach ($rs as $k => $v) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $col, $v['id'])
                    ->setCellValue('B' . $col, getFieldById($v['Village'], 'Village', $title = 'name'))
                    ->setCellValue('C' . $col, $v['companyname'])
                    ->setCellValue('D' . $col, $v['companyid'])
                    ->setCellValue('E' . $col, $v['address'])
                    ->setCellValue('F' . $col, $v['risename'])
                    ->setCellValue('G' . $col, $v['email'])
                    ->setCellValue('H' . $col, $v['linktel'])
                    ->setCellValue('I' . $col, $v['memo'])
            ;
            $col++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('大客户管理信息'); //设置sheet标签的名称
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();  //清空缓存
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=大客户管理信息.xls'); //设置文件的名称
        header("Content-Transfer-Encoding:binary");
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function toExcelremove($rs) {
        //获取数组个人信息
        import('@.ORG.Excel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
        $col = 2;
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '编号')
                ->setCellValue('B1', '日期')
                ->setCellValue('C1', '快递公司')
                ->setCellValue('D1', '快递单号')
                ->setCellValue('E1', '发货人姓名')
                ->setCellValue('F1', '发货人电话')
                ->setCellValue('G1', '重量')
                ->setCellValue('H1', '目的地')
                ->setCellValue('I1', '备注');
        $objPHPExcel->setActiveSheetIndex(0);
        foreach ($rs as $k => $v) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $col, $v['id'])
                    ->setCellValue('B' . $col, $v['TakingTime'])
                    ->setCellValue('C' . $col, getFieldById($v['ExpID'], 'express', $title = 'title'))
                    ->setCellValue('D' . $col, $v['ExpNum'])
                    ->setCellValue('E' . $col, $v['Fname'])
                    ->setCellValue('F' . $col, $v['Fmobile'])
                    ->setCellValue('G' . $col, $v['Weight'])
                    ->setCellValue('H' . $col, $v['Destination'])
                    ->setCellValue('I' . $col, $v['memo'])
            ;
            $col++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('揽收移交'); //设置sheet标签的名称
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();  //清空缓存
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=揽收移交.xls'); //设置文件的名称
        header("Content-Transfer-Encoding:binary");
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    public function toExcelcard($rs) {
        //获取数组个人信息
        import('@.ORG.Excel.PHPExcel');
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
        $col = 2;
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '日期')
                ->setCellValue('B1', '卡号')
                ->setCellValue('C1', '快递公司')
                ->setCellValue('D1', '余额')
                ->setCellValue('E1', '店面')
                ->setCellValue('F1', '电话')
                ->setCellValue('G1', '单价')
                ->setCellValue('H1', '储值卡类型')
                ->setCellValue('I1', '状态');
        $objPHPExcel->setActiveSheetIndex(0);
        foreach ($rs as $k => $v) {
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $col, $v['date'])
                    ->setCellValue('B' . $col, $v['account'])
                    ->setCellValue('C' . $col, getFieldById($v['Express'], 'express', $title = 'title'))
                    ->setCellValue('D' . $col, $v['balance'] / 100)
                    ->setCellValue('E' . $col, getFieldById($v['Village'], 'Village', $title = 'name'))
                    ->setCellValue('F' . $col, $v['phone'])
                    ->setCellValue('G' . $col, $v['price'])
                    ->setCellValue('H' . $col, getCarflago($v['overdraft']))
                    ->setCellValue('I' . $col, getCardState($v['balance'] / 100, $v['overdraft']));

            $col++;
        }
        $objPHPExcel->getActiveSheet()->setTitle('储值卡查询'); //设置sheet标签的名称
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();  //清空缓存
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename=储值卡查询.xls'); //设置文件的名称
        header("Content-Transfer-Encoding:binary");
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 设置某个字段 0/1
     */
    function setField() {
        //恢复指定记录
        $name = $this->getActionName();
        $model = D($name);
        $pk = $model->getPk();
        $id = $_GET [$pk];
        $status = $_GET['status'];
        $field = $_GET['field'];
        $condition = array($pk => array('in', $id));
        if ($status == 1) {
            $result = $model->resume($condition, $field);
        } else {
            $result = $model->forbid($condition, $field);
        }
        if (false !== $result) {
            $this->assign("jumpUrl", $this->getReturnUrl());
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    //获取当前表的：title统一大写
    function getMaxID_new($title = "", $step = 1) {
        if ($title == "") {
            $title = $this->getActionName();
        }
        $title = strtoupper($title) . "_ID";
        $model = D('Id');
        $id = $model->where("title='" . $title . "'")->getField('id');
        if ($id < 100) {
            $data['id'] = 99 + $step;
            $data['title'] = $title;
            $data['pdate'] = date('Y-m-d H:i:s');
            $model->add($data);
        } else {
            $model->where("title='" . $title . "'")->setInc('id', $step);
        }
        $newid = $model->where("title='" . $title . "'")->getField('id');
        return $newid;
    }

}

?>