<?php

/**
 * Description of BiginfoModel
 *
 * @author vini
 */
class BiginfoModel extends CommonModel {

    public $_validate = array(
        array('companyid', '', '公司ID已注册', self::EXISTS_VALIDATE, 'unique', self::MODEL_BOTH),
    );

}
