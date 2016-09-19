<?php
/**
* 流水表
 */
namespace Shop\Model;
use Think\Model;
class MoneyRecordModel extends Model{
    protected $tableName        =   'money_record';
    
    /*
     * 获取最近7天总收入=订单入账+充值
     */
    public  function  getWeekMoney($store_id){
        $t1=strtotime(date("Y-m-d",strtotime("-7 day")));
        $t2=strtotime(date("Y-m-d",time()));
        $condition['store_id'] = $store_id;
        $condition['created_at']=array('between',array($t1,$t2));
        $condition['is_pay'] = 0;
        $money = $this->where($condition)->sum('money');
        return is_null($money)?0:$money;
    }
    /*
     * 获取商铺的收支明细
     */
    public function getByStore($condition,$page='',$field='*',$order='created_at desc')
    {
        return $this->where($condition)->order($order)->field($field)->page($page)->select();
    }
    /*
     * 获取商铺的收支明细总数
     */
    public function getByStoreCount($condition)
    {
        return $this->where($condition)->count();
    }

    /*
     * 乐装入账
     */
    public function incomeEasydeco($order_id, $amount = null){
        /*  type, is_pay, member_id, created_time,amount ,yu_e ,desc, order_sn*/
        if(!is_numeric($order_id))
            return false;
        $order_info = D('order')->getOrderInfo(array('order_id' => $order_id));
        $data['store_id']   = $order_info['store_id'];
        $data['m_type']     = LIUSHUI_TYPE_EASYPAY;
        $data['is_pay']     = 0;//0入账 1出账
        $data['order_id']   = $order_info['order_id'];
        $data['order_sn']   = $order_info['order_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $order_info['order_amount_delta'];

        if(!is_null($amount)){
            $data['money'] =   $amount;
        }
        return $this->add($data);
    }

    /*
     * 乐购入帐
     */
    public function incomeEasypay($order_id, $amount = null){
        /*  type, is_pay, store_id, created_time,amount ,yu_e ,desc, order_sn*/
        if(!is_numeric($order_id))
            return false;
        $order_info = D('order')->getOrderInfo(array('order_id' => $order_id));
        $data['store_id']   = $order_info['store_id'];
        $data['m_type']     = LIUSHUI_TYPE_EASYDECO;
        $data['is_pay']     = 0;//0入账 1出账
        $data['order_id']   = $order_info['order_id'];
        $data['order_sn']   = $order_info['order_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $order_info['order_amount_delta'];

        if(!is_null($amount)){
            $data['money'] =   $amount;
        }
        return $this->add($data);
    }

    /*
     * 订单入账(普通订单，不含乐购乐装订单)
     */
    public function incomeOrder($order_id){
        /*  type, is_pay, store_id, created_time,amount ,yu_e ,desc, order_sn,pay_sn*/
        if(!is_numeric($order_id))
            return false;
        if(strlen($order_id) > 10){
            //order_sn
            $order_info = D('order')->getOrderInfo(array('order_sn' => $order_id));    
        }else{
            //order_id
            $order_info = D('order')->getOrderInfo(array('order_id' => $order_id));    
        }
        

        if(is_null($order_info)){
            throw new \Exception("order {$order_id} not exist", 1);
            
        }
        //不是普通订单，报错
        if($order_info['order_type']!=1){
           throw new \Exception("order type not match", 1);
           return false;
        }

        $data['store_id']   = $order_info['store_id'];
        $data['m_type']     = LIUSHUI_TYPE_ORDER;
        $data['is_pay']     = 0;//0入账 1出账
        $data['order_id']   = $order_info['order_id'];
        $data['order_sn']   = $order_info['order_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $order_info['order_amount'];/*普通订单，入账订单总金额*/

        return $this->add($data);
    }

    /*
     * 订单出账
     */
    public function outcomeOrder($order_id){
        /*  type, is_pay, member_id, created_time,amount ,yu_e ,desc, order_sn,pay_sn*/
        if(!is_numeric($order_id))
            return false;
        $order_info = D('order')->getOrderInfo(array('order_id' => $order_id));

         //不是普通订单，报错
        if($order_info['order_type']!=1)
            return false;

        $data['member_id']   = $order_info['buyer_id'];
        $data['m_type']     = LIUSHUI_TYPE_ORDER;
        $data['is_pay']     = 1;//0入账 1出账
        $data['order_id']   = $order_info['order_id'];
        $data['order_sn']   = $order_info['order_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $order_info['order_amount'];/*普通订单，入账订单总金额*/

        if(!is_null($amount)){
            $data['money'] =   $amount;
        }
        return $this->add($data);
    }

    /*
     * 提货入账（针对工厂）
     */
    public function incomeDis($order_id){
        /*  type, is_pay, store_id, created_time,amount ,yu_e ,desc, order_sn,pay_sn*/
        if(!is_numeric($order_id))
            return false;
        $order_info = D('order')->getOrderInfo(array('order_id' => $order_id));
         //不是工厂订单，报错
        if($order_info['order_type']!=4)
            return false;

        $data['member_id']  = $order_info['store_id'];
        $data['m_type']     = LIUSHUI_TYPE_TIHUO;
        $data['is_pay']     = 0;//0入账 1出账
        $data['order_id']   = $order_info['order_id'];
        $data['order_sn']   = $order_info['order_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $order_info['order_amount'];

        if(!is_null($amount)){
            $data['money'] =   $amount;
        }
        return $this->add($data);
    }

    /*
     * 提货出账
     */
    public function outcomeDis($order_id){
        /*  type, is_pay, store_id, created_time,amount ,yu_e ,desc, order_sn,pay_sn*/
         if(!is_numeric($order_id))
            return false;
        $order_info = D('order')->getOrderInfo(array('order_id' => $order_id));
        //不是工厂订单，报错
        if($order_info['order_type']!=4)
            return false;

        $data['member_id']  = $order_info['buyer_id'];
        $data['m_type']     = LIUSHUI_TYPE_TIHUO;
        $data['is_pay']     = 1;//0入账 1出账
        $data['order_id']   = $order_info['order_id'];
        $data['order_sn']   = $order_info['order_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $order_info['order_amount'];

        if(!is_null($amount)){
            $data['money'] =   $amount;
        }
        return $this->add($data);
    }

    /*
     * 充值入账
     */
    public function incomeRecharge($pay_id){
        if(!is_numeric($pay_id))
            return false;
        $pay_info = D('order')->table('order_pay')->where(array('pay_id' => $pay_id))->find();
        $store_info = D('store')->where(array('member_id' => $pay_info['buyer_id']))->find();
        $data['store_id']   = $store_info['store_id'];
        $data['member_id']  = $pay_info['buyer_id'];
        $data['m_type']     = LIUSHUI_TYPE_RECHARGE;
        $data['is_pay']     = 0;//0入账 1出账
        $data['pay_sn']     = $pay_info['pay_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $pay_info['recharge_money'];

        if(!is_null($amount)){
            $data['money'] =   $amount;
        }
        return $this->add($data);
    }

    /*
     * 提现出账
     */
    public function outcomeWithdraw($pay_id){
        if(!is_numeric($pay_id))
            return false;
        return false;


        $pay_info = D('order')->table('order_pay')->where(array('pay_id' => $pay_id))->find();
        $store_info = D('store')->where(array('member_id' => $pay_info['buyer_id']))->find();
        $data['store_id']   = $store_info['store_id'];
        $data['m_type']     = LIUSHUI_TYPE_RECHARGE;
        $data['is_pay']     = 0;//0入账 1出账
        $data['pay_sn']     = $pay_info['pay_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $pay_info['recharge_money'];

        if(!is_null($amount)){
            $data['money'] =   $amount;
        }
        return $this->add($data);

    }

    /*
     * 抽佣出账
     */
    public function outcomeCommission($order_id){
         if(!is_numeric($order_id))
            return false;
        $order_info = D('order')->getOrderInfo(array('order_id' => $order_id));
        $store_info = D('store')->where(array('store_id' => $order_info['store_id']))->find();
        $ser_charge = $store_info['ser_charge'];
        if(!is_numeric($ser_charge))
            return false;

        $order_type = $order_info['order_type'];
            

        $data['store_id']   = $order_info['store_id'];
        $data['m_type']     = LIUSHUI_TYPE_COMMISSION;
        $data['is_pay']     = 1;//0入账 1出账
        $data['order_id']   = $order_info['order_id'];
        $data['order_sn']   = $order_info['order_sn'];
        $data['created_at'] = TIMESTAMP;
        //区分普通订单，工厂订单，乐装订单和乐购订单
        switch ($order_type) {
            case '1':
            case '4':
                //普通订单
                $data['money']      =   $order_info['order_amount']*$ser_charge;
                break;
            case '2':
            case '3':
                //乐购乐装订单
                $data['money']      =   $order_info['order_amount_delta']*$ser_charge;
                break;
            default:
                //其它订单
                $data['money']      =   $order_info['order_amount']*$ser_charge;
                break;
        }
        

      
        return $this->add($data);
    }

    /*
     *
     */
    public function  cvOrder($order_id_or_order_sn){
        /*获取订单信息，order_id order_amount order_type order_state */

        /*计算订单金额，抽水金额*/
        /*增加账户可用余额*/
        if(is_numeric($order_id_or_order_sn)){
            throw new \Exception("找不到订单信息", 1);
        }

        if(strlen($order_id_or_order_sn) > 10){
            //order_sn
            $order_info = D('order')->getOrderInfo(array('order_sn' => $order_id_or_order_sn));    
        }else{
            //order_id
            $order_info = D('order')->getOrderInfo(array('order_id' => $order_id_or_order_sn));    
        }

        if(is_null($order_info)){
            throw new \Exception("找不到订单信息!", 1);
        }

        if(($order_info['order_state'] != 20)||($order_info['order_type']!=1 )){
            throw new \Exception("订单类型异常!", 1);
        }
       
        $store_info = M('order')->where(array('member_id' => $order_info['store_id']))->find();

        if(is_null($store_info)){
            throw new \Exception("找不到卖家信息", 1);
        }

        /*服务费*/
        $ser_charge = $store_info['ser_charge'];
        if(!is_numeric($ser_charge)){
            throw new Exception("服务费类型异常", 1);
            
        }

        /*增加卖家账户余额*/
        $data_store['deposit_avaiable'] = array('exp','deposit_avaiable+'.($order_info['order_amount']));
        if(!$update = M('store')->where(array('member_id'=>$store_info['member_id']))->save($data_store)){
            throw new \Exception("余额更新失败", 1);
        }

        /*记录订单日志*/
        unset($data);
        $data['store_id']   = $order_info['store_id'];
        $data['store_name'] = $store_info['store_name'];
        $data['m_type']     = LIUSHUI_TYPE_ORDER;
        $data['is_pay']     = 0;//0入账 1出账
        $data['order_id']   = $order_info['order_id'];
        $data['order_sn']   = $order_info['order_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $order_info['order_amount'];/*普通订单，入账订单总金额*/
        if(!($this->add($data))){
            throw new \Exception("订单日志写入失败", 1);
            
        }

        /*扣除佣金*/
        unset($data_store);
        $data_store['deposit_avaiable'] = array('exp','deposit_avaiable-'.($order_info['order_amount']*$ser_charge*1.0/100));
        if(!$update = M('store')->where(array('member_id'=>$store_info['member_id']))->save($data_store)){
            throw new \Exception("抽佣失败", 1);
            
        }
        /*写入佣金日志*/
        unset($data);
        $data['store_id']   = $order_info['store_id'];
        $data['store_name'] = $store_info['store_name'];
        $data['m_type']     = LIUSHUI_TYPE_COMMISSION;
        $data['is_pay']     = 1;//0入账 1出账
        $data['order_id']   = $order_info['order_id'];
        $data['order_sn']   = $order_info['order_sn'];
        $data['created_at'] = TIMESTAMP;
        $data['money']      =   $order_info['order_amount'];/*普通订单，入账订单总金额*/
        if(!($this->add($data))){
            throw new \Exception("抽佣日志写入失败", 1);
        }
    }
}