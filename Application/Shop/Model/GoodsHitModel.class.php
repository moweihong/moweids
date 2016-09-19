<?php
/**
 * Created by PhpStorm.
 * User: martymei
 * Date: 2016/6/17
 * Time: 10:40
 */
class GoodsHitModel extends Model{

    public function __construct() {
        parent::__construct('goods_hit');
    }

    public function addHit($condition,$way,$goods_id,$user_id){
        $info=$this->where($condition)->find();
        if(empty($info)){
            $data["goods_id"]=$goods_id;
            $data["user_id"]=$user_id;
            $data["add_time"]=strtotime(date("Y-m-d",time()));
            switch($way){
                case 1: $data["way1"]=1;$data["way2"]=0;$data["way3"]=0;break;
                case 2: $data["way1"]=0;$data["way2"]=1;$data["way3"]=0;break;
                case 3: $data["way1"]=0;$data["way2"]=0;$data["way3"]=1;break;
                default:
                    
            }
            $id=$this->insert($data);
        }
        else{
            switch($way){
                case 1: $this->where($condition)->setInc("way1");break;
                case 2: $this->where($condition)->setInc("way2");break;
                case 3: $this->where($condition)->setInc("way3");break;
                default:
                    break;
            }
            $id=$info["hit_id"];
        }
        return $id;
    }

    public function getItem($condition){
        return $this->where($condition)->find();
    }

    public function getHitList($user_id,$start_time=0,$end_time=0,$page_no=1,$page_num=10){
        $info["code"]=0;
        $info["msg"]="OK";

        //计算总数列表
        if(!(empty($start_time)||empty($end_time)))
            $where["add_time"]=array("between",array($start_time,$end_time));
        $where["user_id"]=$user_id;
        $count=$this->where($where)->count("*");
        $info["page_total"]=ceil($count/$page_num);
        $info["record_total"]=$count;


        //先获取hit_list
        $start=($page_no-1)*$page_num;
        $limit="{$start},{$page_num}";

        $hit=$this->where($where)->limit($limit)->order("add_time")->select();
        $hit_id=array();
        foreach($hit as $key=>$val){
            $hit_id[]=$val["hit_id"];
        }
        $model=Model();
        $list_where["hit_id"]=array("in",$hit_id);

        $field="hit_id,sum(orders_cnt) as orders_cnt,sum(goods_pay_price) as orders_amount,sum(r_money) as estimate_commission";
        $hit_list=$model->table("goods_hit_list")->where($list_where)->group("hit_id")->field($field)->select();
        //整理hit_id;
        $hit_list_info=array();
        foreach($hit_list as $key=>$val){
            $hit_list_info[$val["hit_id"]]=$val;
        }
        //计算数据
        $r_info=array();
        foreach($hit as $key=>$val){
            if(empty($r_info[$val["user_id"]."-".$val["add_time"]])){
                $r_info[$val["user_id"]."-".$val["add_time"]]["cps_date"]=date("Y-m-d",$val["add_time"]);
                $r_info[$val["user_id"]."-".$val["add_time"]]["share_goods_cnt"]=1;
                $r_info[$val["user_id"]."-".$val["add_time"]]["hits"]=$val["way1"]+$val["way2"]+$val["way3"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["way1"]=$val["way1"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["way2"]=$val["way2"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["way3"]=$val["way3"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["orders_cnt"]=$hit_list_info[$val["hit_id"]]["orders_cnt"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["orders_amount"]=$hit_list_info[$val["hit_id"]]["orders_amount"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["estimate_commission"]=$hit_list_info[$val["hit_id"]]["estimate_commission"];
            }else{
                $r_info[$val["user_id"]."-".$val["add_time"]]["share_goods_cnt"]+=1;
                $r_info[$val["user_id"]."-".$val["add_time"]]["hits"]+=$val["way1"]+$val["way2"]+$val["way3"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["orders_cnt"]+=$hit_list_info[$val["hit_id"]]["orders_cnt"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["orders_amount"]+=$hit_list_info[$val["hit_id"]]["orders_amount"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["estimate_commission"]+=$hit_list_info[$val["hit_id"]]["estimate_commission"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["way1"]+=$val["way1"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["way2"]+=$val["way2"];
                $r_info[$val["user_id"]."-".$val["add_time"]]["way3"]+=$val["way3"];
            }
        }
        foreach($r_info as $key=>$val){
            $r_info[$key]["introduce_source"]="第三方".$val["way1"].",直属链接".$val["way2"].",二维码".$val["way3"];
            unset($r_info[$key]["way1"]);
            unset($r_info[$key]["way2"]);
            unset($r_info[$key]["way3"]);

        }

        $info["cps_result_list"]=array_values($r_info);

        return $info;
    }
}