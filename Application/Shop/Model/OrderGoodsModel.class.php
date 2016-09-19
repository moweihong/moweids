<?php
/**
 * 订单管理
 *
 *
 *
 *

 */
namespace Shop\Model;
use Think\Model;

class OrderGoodsModel extends Model {
    
    /**
     * 修改订单商品信息
     * 商品状态
     * 运单号
     * 
     * @param  [type] $update    [description]
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function test(){

    }
    public function editGoods($update, $condition)
    {

        //获取商品
        $record = $this->table('order_goods')->where($condition)->find();
        if(!$record){
            return false;
        }

        //修改商品
        $update = array_merge($record, $update);
        
        return $this->table('order_goods')->where($condition)->update($update);
    }
}
