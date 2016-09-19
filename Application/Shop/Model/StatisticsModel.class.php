<?php
/**
 * 店铺统计
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;
class StatisticsModel extends Model{
	/**
	 * 更新统计表
	 *
	 * @param	array $param	条件数组
	 */
	public function updatestat($param){
		if (empty($param)){
			return false;
		}
		$result = $this->table($param['table'])->where($param['where'])->save($param['data']);
		return $result;
	}
}