<?php
/**
 * 分期购
 *
 *

 */
namespace Shop\Model;
use Think\Model;

class BankModel extends Model{

    protected $tableName   = 'bank';
    
    /*
     * 获取一个银行卡信息
     */
    public function getBank($condition, $field = '*') {
        return $this->field($field)->where($condition)->find();
    }
    
    /*
     * 插入一条银行卡信息
     */
    public function addBank($insert) {
        return $this->add($insert);
    }
    /*
     * 更新银行卡信息
     */
    public function updateBank($condition,$update){
        return $this->where($condition)->save($update);
    }
    /*
     * 删除银行卡信息
     */
    public function delBank($condition){
        return $this->where($condition)->update(array('is_del'=>1));
    }
 
}
