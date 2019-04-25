<?php

/**
 * Description of OfficeUserModel
 *
 * @author vini
 */
class OfficeUserModel extends CommonModel {

    public $_validate = array(
        array('password', '/^[a-z0-9]{6,12}$/i', '邮局密码格式错误'),
        array('account', 'checkAccount', '手机号已存在，请更换手机号', self::EXISTS_VALIDATE, 'callback', self::MODEL_BOTH),
    );
    public $_auto = array(
        array('password', 'pwdHash', self::MODEL_BOTH, 'callback'),
        array('createTime', 'time', self::MODEL_INSERT, 'function'),
        array('updateTime', 'time', self::MODEL_UPDATE, 'function'),
    );

    protected function pwdHash() {
        if (!empty($_POST['password'])) {
            return pwdHash($_POST['password']);
        } else {
            return false;
        }
    }

    protected function checkAccount() {
        if (isset($_POST['linktel'])) {
            $map['Bid'] = array(array('lt', $_POST['id']), array('gt', $_POST['id']), 'OR');
            $map['type'] = 1; //管理员账号
            return $this->where($map)->getField('account') === $_POST['linktel'] ? false : true;
        } else {
            return true;
        }
    }

}
