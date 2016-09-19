<?php
/**
 * Created by PhpStorm.
 * User: wch
 * Date: 2016/5/10
 * Time: 12:07
 */
namespace Shop\Model;
use Think\Model;
class SellerAccountLogModel extends Model{
   //卖家账户记录日志
   //下单+
   //计算 -
   //提现 -
   //取消 -
   //退款退货 -
   //提佣 -
   //平台服务费 -
   

    /**
     * 订单日志
     * 必填项   member_id
     *          operation type 1
     *          amount 
     *          remaining  查询得到
     *          
     */
    public function OrderLog($options){
        //查询余额
        
        //写入日志
        $options['member_id'] = $_SESSION['member_id'];
        $options['time'] = time();
        $options['type'] = "1";

        $result = $this->insert($options);
        //更新余额
        $deposit_model = Model('store');
        //$deposit_model->;
        //
    }

   /**
    * 结算日志i
    * @return [type] [description]
    */
   public function submitLog(){

   }

   /**
    * 提现日志
    * @return [type] [description]
    */
   public function withdrawLog(){

   }

   /**
    * 取消订单日志
    * @return [type] [description]
    */
   public function cancelOrderLog(){

   }

   /**
    * 退款退货日志
    * @return [type] [description]
    */
   public function goodsmoneybackLog(){

   }

   /**
    * 抽佣日志
    * @return [type] [description]
    */
   public function commissionLog(){

   }

   /**
    * 平台服务费日志
    * @return [type] [description]
    */
   public function platformCostLog(){

   }
}