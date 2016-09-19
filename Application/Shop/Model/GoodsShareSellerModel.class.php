<?php

namespace Shop\Model;
use Think\Model;
/**
 * Created by PhpStorm.
 * User: martymei
 * Date: 2016/6/16
 * Time: 13:55
 */
class GoodsShareSellerModel extends Model{

    public function __construct() {
        parent::__construct('goods_share_seller');
    }
    public function add_seller($data){
       $id= $this->insert($data);
    }
    public function isExist($order){
        $where["order_id"]=$order;
        $result=$this->where($where)->find();
        if(empty($result)){
            return 0;
        }else{
            return 1;
        }
    }
    public function getItem($condition){
        return $this->where($condition)->select();
    }

    /**************************************
     * 添加项目
     */
    public function addItem($info,$userId){

        $data["goods_share_id"]=$info["goods_share_id"];
        $data["store_id"]=$info["store_id"];
        $data["goods_id"]=$info["goods_id"];
        $data["order_id"]=$info["order_id"];
        $data["userid"]=$userId;
        $data["seller_id"]=$info["buyer_id"];
        $data["num"]=$info["goods_num"];
        $data["goods_value"]=$info["goods_price"];
        $data["goods_pay_price"]=$info["goods_pay_price"];
        $cps_policy=Model("cps_policy");
        $data["cps_percent"]=$cps_policy->get_percent(1);
        $data["addtime"]=time();
        $data["percent"]=$info["share_money"];
        $data["status"]=0;
        $data["rec_id"]=$info["rec_id"];
        $data["r_money"]=number_format($data["goods_pay_price"]* $data["percent"]* $data["cps_percent"]/10000,2);

        
        return $this->insert($data);
    }

}