<?php
/**
 * 地区模型
 *
 *
 *
 *

 */
namespace Shop\Model;
use Think\Model;

class AreaModel extends Model{

    
    public function __construct() {
        parent::__construct('area');
    }
    
    public function getAreaList($condition = array(),$fields = '*', $group = '') {
        return $this->where($condition)->field($fields)->limit(false)->group($group)->select();
    }

    public function getAreaInfo($condition = array(),$fields = '*'){
        return $this->where($condition)->field($fields)->find();
    }
    
}