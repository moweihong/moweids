<?php
/**
 * Created by PhpStorm.
 * User: martymei
 * Date: 2016/6/18
 * Time: 11:14
 */
class OrderStoreModel extends  Model{

    public function __construct() {
        parent::__construct('order_store');
    }
    private function getGetRecommend($store_id){
        $model=new Model();
        $store_info=$model->table("store,member")->on("store.member_id=member.member_id")->where(array("store.store_id"=>$store_id))->field("member.mid")->find();
        if(empty($store_info)){
            
            return false;
        }
        $API=API("user");
        $result=$API->getGetRecommend($store_info["mid"]);
        $json=json_decode($result);
        if($json->code==-1){
            
            return false;
        }else{
            $java_info=json_decode($json->resultText);
            $info["recommend_id"]=isset($java_info->recommend_id)?$java_info->recommend_id:0;
            $info["recommend_2_id"]=isset($java_info->recommend_2_id)?$java_info->recommend_2_id:0;
            return $info;
        }
    }
    public function addItem($info,$store_id,$time){
        //这里需要补充获取上级分销人员

        $Recommend=$this->getGetRecommend($store_id);
        if(empty($Recommend["recommend_id"])){
            $Recommend["recommend_id"]=0;
            $Recommend["recommend_2_id"]=0;
        }
        $data["order_id"]=$info["order_id"];
        $data["rec_id"]=$info["rec_id"];
        $data["user1_id"]=$Recommend["recommend_id"];
        $data["user2_id"]=$Recommend["recommend_2_id"];
        $data["orders_cnt"]=$info["goods_num"];
        $data["percent"]=$info["commis_rate"]+C("allwood_commission");//后续改变为店铺
        $cps_policy=Model("cps_policy");
        $data["cps_percent"]=$cps_policy->get_percent(2);

        $data["goods_pay_price"]=$info["goods_pay_price"];
        $data["addtime"]=$time;

        $data["r_money"]=number_format($data["goods_pay_price"]* $data["percent"]* $data["cps_percent"]/10000,2);
        $id=$this->insert($data);
        if(empty($id)){
            Log::record("写销售商品记录失败".var_export($data,true));
        }

        return $id;
    }
    public function getItem($condition){
        $item=$this->where($condition)->find();
        return $item;
    }
}