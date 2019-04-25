<?php

class Order_detailAction extends CommonAction {

    public function index() {
        $model = D('order_detail');
        $map['order_id'] = $_REQUEST['order_id'];
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->assign('order_id', $_REQUEST['order_id']);
        $this->display();
    }

}

?>