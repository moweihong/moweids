<?php
/**
 * Created by PhpStorm.
 * User: martymei
 * Date: 2016/6/18
 * Time: 10:30
 */

class GoodsHitListModel extends  Model{

    public function __construct() {
        parent::__construct('goods_hit_list');
    }

    public function addItem($info,$hit_id,$time){
        $data["hit_id"]=$hit_id;
        $data["order_id"]=$info["order_id"];
        $data["rec_id"]=$info["rec_id"];
        $data["orders_cnt"]=$info["goods_num"];
        $data["goods_pay_price"]=$info["goods_pay_price"];
        $cps_policy=Model("cps_policy");
        $data["cps_percent"]=$cps_policy->get_percent(1);
        $data["percent"]=$info["share_money"];
        $data["r_money"]=number_format($data["goods_pay_price"]* $data["percent"]* $data["cps_percent"]/10000,2);
        $data["addtime"]=$time;
        
        return $this->insert($data);
    }
}