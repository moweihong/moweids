<?php

/**
 * 卖家日志模型
 *
 * 
 */
namespace Shop\Model;
use Think\Model;

class SellerLogModel extends Model
{
    protected $tableName = 'seller_log';
    
	/**
	 * 读取列表 
	 * @param array $condition
	 *
	 */
	public function getSellerLogList($condition, $page='', $order='', $field='*') {
        $result = $this->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
	}

    /**
	 * 读取单条记录
	 * @param array $condition
	 *
	 */
    public function getSellerLogInfo($condition) {
        $result = $this->where($condition)->find();
        return $result;
    }

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
	 */
    public function addSellerLog($param){
        return $this->add($param);	
    }
	
	/*
	 * 删除
	 * @param array $condition
	 * @return bool
	 */
    public function delSellerLog($condition){
        return $this->where($condition)->delete();
    }
    
}

