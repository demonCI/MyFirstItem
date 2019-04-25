<?php

class ContainerModel extends CommonModel {

    // 自动验证设置
    protected $_validate = array(
        array('title', 'require', '标题必须！', 1),
        array('number', 'checkNum', '数量必须大于0！', 1, 'function'),
    );
    // 自动填充设置
    protected $_auto = array(
        array('status', '1', self::MODEL_INSERT)
    );

    function checkNum($num) {
        return $num > o ? TRUE : FALSE;
    }

}

?>