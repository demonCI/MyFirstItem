<?php

class CompanyAction extends CommonAction {

    function index() {
        $model = D('error_list');
        if (!empty($model)) {
            $this->_list($model, $map);
        }
        $this->display();
    }
}

?>