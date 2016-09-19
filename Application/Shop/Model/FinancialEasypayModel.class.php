<?php
/**
 * 卖家帐号模型
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;
class FinancialEasypayModel extends Model{

    public function __construct(){
        parent::__construct('financial_easypay');
    }

	// public function getSellerList($condition, $page='', $order='', $field='*') {
 //        $result = $this->field($field)->where($condition)->page($page)->order($order)->select();
 //        return $result;
	// }

    public function getFinancialEasypayInfo($condition) {
        $result = $this->where($condition)->find();
        return $result;
    }

	// public function isSellerExist($condition) {
 //        $result = $this->getSellerInfo($condition);
 //        if(empty($result)) {
 //            return FALSE;
 //        } else {
 //            return TRUE;
 //        }
	// }

 //    public function addSeller($param){
 //        return $this->insert($param);	
 //    }
	
 //    public function editSeller($update, $condition){
 //        return $this->where($condition)->update($update);
 //    }
	
 //    public function delSeller($condition){
 //        return $this->where($condition)->delete();
 //    }
	
}
