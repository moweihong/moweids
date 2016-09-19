<?php
/**
 * 店铺模型管理
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;
class StoreExtendModel extends Model {
    public function __construct(){
        parent::__construct('store_extend');
    }

    //获取一条记录
    public function getOneExtend($condition){
        return $this->where($condition)->find();
    }


    //更新一条记录
    public  function updateItem($condition,$update){

        return $this->where($condition)->update($update);
    }

}
