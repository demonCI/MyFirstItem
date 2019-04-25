<?php
// 用户模型
class CardModel extends CommonModel {
	
	
    function del_card($cardId) {
		$r = "1";
        $table = $this->tablePrefix.'card';
        $rs = $this->db->query('select balance from '.$table.' where nkey='.$cardId);
        if ($rs[0]['balance'] == '0' || $rs[0]['balance'] == '') {
            $r = "0";
        }
        return $r;
    }
}
?>