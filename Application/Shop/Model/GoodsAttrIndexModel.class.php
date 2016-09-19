<?php
/**
 * 商品与属性对应
 *
 *
 *
 *

 */
namespace Shop\Model;
use Think\Model;

class GoodsAttrIndexModel extends Model {
    public function __construct() {
        parent::__construct('goods_attr_index');
    }
    
    /**
     * 对应列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsAttrIndexList($condition, $field = '*') {
        return $this->where($condition)->field($field)->select();
    }
}