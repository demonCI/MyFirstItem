<?php
class FormAction extends CommonAction {
    function index(){
        $Vid = $_REQUEST['Vid'];
        $pdate = $_REQUEST['pdate'];
        $str=" where pdate = '".$pdate."' and Vid ='".$Vid."'";
        $ExpID = $_REQUEST['ExpID'];
        if($ExpID!="")
        {
                $str.=" AND ExpID='".$ExpID."'";
        }
        $model = D();
        $sql = "select Vid,ExpID,pdate,SUM(come) as come,sum(send) as send,sum(hour_24) as hour_24,sum(hour_72) as hour_72,sum(hour_168) as hour_168,sum(same_date) as same_date,sum(problem) as problem,sum(shouldprice) as shouldprice,sum(faceprice) as faceprice,sum(retail) as retail,sum(company) as company,sum(money) as money,sum(sendmoney) as sendmoney,Elevel,Epid,Epnid,Cid,Cpid,level from  tb_date  $str  GROUP BY ExpID";
        $data = $model->query($sql);
        $this->assign('list',$data);
        $this->display();
    }
	/*function index(){
       if($_REQUEST ['pbegin'] > $_REQUEST ['pend'] ){
			$this->error('请选择正确的查询时间！');
		}else if ($_REQUEST['pbegin'] > 0 && $_REQUEST['pend'] > 0) {

    		$_REQUEST['pdate'] = array(array('egt', $_REQUEST['pbegin'] ), array('elt', $_REQUEST['pend'] ));
		} else if ($_REQUEST ['pbegin'] > 0 && $_REQUEST ['pend'] == "") {
			$this->error ( '请选择结束时间!' );
		} else if ($_REQUEST ['pbegin'] == "" && $_REQUEST ['pend'] > 0) {
			$this->error ( '请选择开始时间!' );
		}
		$map = $this->_search();
        if (method_exists($this, '_filter')) {
            $this->_filter($map);
        }
        $model = D('date');
        Log::write(print_r($map,true),LOG::SQL);
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
        return;
		// $this->assign('list',$data);
		// $this->display();
	}*/
	public function _before_index() {
        $this->assign("vil",getFieldArray("Village","id,name"));
        $this->assign("Exp", getFieldArray('Express'));
    }
       protected function _list($model, $map, $sortBy = '', $asc = false, $countPk = 'nkey') {
        $this->assign("map", $map['VID']);
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
            Log::write($model->getLastSql(),LOG::SQL);
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
        $this->assign('totalCount', $count);
        $this->assign('numPerPage', $p->listRows);
        $this->assign('currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);

        Cookie::set('_currentUrl_', __SELF__);
        return;
	}
	protected function _search() {
        //生成查询条件
        $model = D('date');
        $map = array();
      //  Log::write(print_r($map, true), LOG::INFO);
        foreach ($model->getDbFields() as $key => $val) {
            if (isset($_REQUEST [$val]) && $_REQUEST [$val] != '') {
                $map [$val] = $_REQUEST [$val];
            }
        }
        return $map;
    }
}