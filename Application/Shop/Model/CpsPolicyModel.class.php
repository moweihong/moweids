<?php
/**
 * Created by PhpStorm.
 * User: martymei
 * Date: 2016/6/18
 * Time: 10:22
 */
class CpsPolicyModel extends Model{

    public function __construct() {
        parent::__construct('cps_policy');
    }

    /********************************
     * 获取分销提成
     * $type=1 商品分佣
     * $type=2 店铺分佣
     */
    public function get_percent($type){
        if($type==1){
            $where["cps_type"]=0;
        }else{
            $where["cps_type"]=1;
        }
        $where["check_status"]=1;
        $where["begin_time"]=array("lt",time());
        $where["end_time"]=array("gt",time());
        $info=$this->where($where)->order("begin_time asc")->find();
        unset($where["end_time"]);
        $where["is_permanent"]=1;
        $info1=$this->where($where)->order("begin_time asc")->find();

        if(empty($info)&&empty($info1)){
            $percent=0;
        }else if(empty($info)){
            $percent=$info1["commission_rate"];
        }else if(empty($info1)){
            $percent=$info["commission_rate"];
        }
       else  if($info["begin_time"]<$info1["begin_time"]){
            $percent=$info["commission_rate"];
        }else{
            $percent=$info1["commission_rate"];
        }

        return $percent;
    }

    /**********************
     * 新增策略
     */
    public function addCPSPolicy($data){
        $id=$this->insert($data);
        if($id>0)
            return true;
        else
            return false;
    }
    /*********************************
     *修改全木行佣金策略
     */
    public function setCPSPolicy($data,$condition){
        $result=$this->where($condition)->update($data);
        if(empty($result))
            return false;
        else
            return true;
    }
    /********************************
     * 显示策略
     */

    public function  getCPSPolicyList($where,$cur_page,$page_num){
        $info["code"]=0;
        $info["msg"]="返回成功";
        $count=$this->where($where)->count();
        $info["page_total"]=ceil($count/$page_num);
        $info["record_total"]=$count;

        $info["policy_list"]=$this->where($where)->select();

        return $info;



    }


}