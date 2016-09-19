<?php
/**
 * 手机端广告
 *
 * 
 *
 *




 */

namespace Shop\Model;
use Think\Model;

class OfflineProvisionalOrderGoodsModel extends Model{
    public function __construct(){
        parent::__construct('offline_provisional_order_goods');
    }

     
	/**
	 * 删除
	 *
	 * @param array $condition 条件
	 * @return bool 布尔类型的返回结果
	 */
	public function delOfflineOrderGoods($condition){
            return $this->where($condition)->delete();	
	}  
	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @param array $condition 条件
	 * @return bool 布尔类型的返回结果
	 */
	public function editOfflineOrderGoods($param, $condition){
            return $this->where($condition)->update($param);
	}  



}
