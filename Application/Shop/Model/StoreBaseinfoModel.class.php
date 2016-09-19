<?php
/**
 * 活动细节
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;
class StoreBaseinfoModel extends Model{
	public function __construct()
	{
		parent::__construct('store_baseinfo');
	}

	//新增baseinfo
	public function addBaseinfo($condition){
		//执行插入操作
		return $this->insert($condition);
	}

	//修改baseinfo
	public function editBaseinfo($conditon,$update){
		return $this->where($conditon)->update($update);
	}

	//获取store信息
	public function getBaseInfo($conditon){
		return $this->where($conditon)->select();
	}

}