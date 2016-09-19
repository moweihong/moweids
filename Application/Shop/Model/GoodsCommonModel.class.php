<?php
namespace Shop\Model;
use Think\Model;

class GoodsCommonModel extends Model
{
    protected $tableName = 'goods_common';
	
	public function goodsCommonList($condition,$field = '*'){
		$list=$this->field($field)->where($condition)->select();
		return $list;
	}
}

