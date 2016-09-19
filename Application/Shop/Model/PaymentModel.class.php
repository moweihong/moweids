<?php
/**
 * 支付方式
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;
class PaymentModel extends Model {
    /**
     * 开启状态标识
     * @var unknown
     */
    const STATE_OPEN = 1;
    
    public function __construct() {
        parent::__construct('payment');
    }

	/**
	 * 读取单行信息
	 *
	 * @param
	 * @return array 数组格式的返回结果
	 */
	public function getPaymentInfo($condition = array()) {
		return $this->where($condition)->find();

	}

	/**
	 * 读开启中的取单行信息
	 *
	 * @param
	 * @return array 数组格式的返回结果
	 */
	public function getPaymentOpenInfo($condition = array()) {
	    $condition['payment_state'] = self::STATE_OPEN;
	    return $this->where($condition)->find();
	}
	
	/**
	 * 读取多行
	 *
	 * @param 
	 * @return array 数组格式的返回结果
	 */
	public function getPaymentList($condition = array()){
        return $this->where($condition)->select();
	}
	
	/**
	 * 读取开启中的支付方式
	 *
	 * @param
	 * @return array 数组格式的返回结果
	 */
	public function getPaymentOpenList($condition = array()){
	    $condition['payment_state'] = self::STATE_OPEN;
	    return $this->where($condition)->select();
	}

    /**
     * 读取开启中的支付方式
     *
     * @param
     * @return array 数组格式的返回结果
     */
    public function getPaymentUseList($condition = array()){
        $condition['payment_state'] = self::STATE_OPEN;
        return $this->where($condition)->getField('payment_code,payment_name', true);
    }
	
	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @return bool 布尔类型的返回结果
	 */
	public function editPayment($data, $condition){
		return $this->where($condition)->update($data);
	}

	/**
	 * 读取支付方式信息by Condition
	 *
	 * @param
	 * @return array 数组格式的返回结果
	 */
	public function getRowByCondition($conditionfield,$conditionvalue){
	    $param	= array();
	    $param['table']	= 'payment';
	    $param['field']	= $conditionfield;
	    $param['value']	= $conditionvalue;
	    $result	= Db::getRow($param);
	    return $result;
	}

    /**
     * 购买商品
     */
    public function productBuy($pay_sn, $payment_code, $member_id) {
        $condition = array();
        $condition['payment_code'] = $payment_code;
        $payment_info = $this->getPaymentOpenInfo($condition);
        if(!$payment_info) {
            return array('error' => '系统不支持选定的支付方式');
        }

        //验证订单信息
	    $model_order = D('Order');
	    $order_pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$member_id));
	    if(empty($order_pay_info)){
            return array('error' => '该订单不存在');
	    }
	    $order_pay_info['subject'] = iconv('gbk', 'utf-8',"商品购买_").$order_pay_info['pay_sn'];
	    $order_pay_info['order_type'] = 'product_buy';

	    //重新计算在线支付且处于待支付状态的订单总额
        $condition = array();
        $condition['pay_sn'] = $pay_sn;
        $condition['order_state'] = ORDER_STATE_NEW;
        $order_list = $model_order->getOrderList($condition,'','order_id,order_sn,order_amount,pd_amount,order_amount_delta');
        if (empty($order_list)) {
            return array('error' => '该订单不存在');
        }

        //计算本次需要在线支付的订单总金额
        $pay_amount = 0;
        foreach ($order_list as $order_info) {
            $pay_amount += ncPriceFormat(floatval($order_info['order_amount']) - floatval($order_info['pd_amount']) - floatval($order_info['order_amount_delta']));
        }

        /*测试用途，在测试模式在，在线支付永远为1分钱，不管商品的价格如何*/
        // if(C('debug') || C('MODE') === "TEST"){
        //     $pay_amount = 0.01;
        // }

        //如果为空，说明已经都支付过了或已经取消或者是价格为0的商品订单，全部返回
        if (empty($pay_amount)) {
            return array('error' => '订单金额为0，不需要支付');
        }
        $order_pay_info['pay_amount'] = $pay_amount;

        return(array('order_pay_info' => $order_pay_info, 'payment_info' => $payment_info));
    }

    /**
     * 购买订单支付成功后修改订单状态
     */
    public function updateProductBuy($out_trade_no, $payment_code, $order_list, $trade_no) {
	    try {
	        $model_order = D('order');
	        $model_order->startTrans();
			
	        $data = array();
	        $data['api_pay_state'] = 1;
	        $update = $model_order->editOrderPay($data, array('pay_sn' => $out_trade_no));
	        if (!$update) {
	            throw new \Exception('更新订单状态失败');
	        }

            $data = array();
            $order_info = $model_order->getOrderInfo(array("pay_sn" => $out_trade_no));//支付单号
            if ($order_info['order_type'] == ORDER_TYPE_EASYPAY){//分期支付
                $data['order_state'] = ORDER_STATE_HANDLING;
            }elseif ($order_info['order_type'] == ORDER_TYPE_FACTORY){//工厂订单
                $data['order_state'] = ORDER_STATE_PAY;
            }else{
                $data['order_state'] = ORDER_STATE_PAY;
            }
            $data['payment_time']	= $_SERVER['REQUEST_TIME'];
            $data['payment_code']   = $order_info['order_type'] == ORDER_TYPE_EASYPAY ? 'easypay' : $payment_code;

	        $update = $model_order->editOrder($data, array('pay_sn' => $out_trade_no, 'order_state' => ORDER_STATE_NEW, 'is_panel' => 0));
	        if (!$update) {
	            throw new \Exception('更新订单状态失败');
	        }

            if ($payment_code == 'easypay'){
                $flag = $this->fabiao($order_info['order_id']);
                if (!$flag){
                    throw new \Exception('发标失败');
                }
            }

        

            $flag = true;
            foreach($order_list as $orderinfo) {
                //如果有预存款支付的，彻底扣除冻结的预存款
                /*$pd_amount = floatval($order_info['pd_amount']);
                if ($pd_amount > 0) {
                    $data_pd = array();
                    $data_pd['member_id'] = $order_info['buyer_id'];
                    $data_pd['member_name'] = $order_info['buyer_name'];
                    $data_pd['amount'] = $order_info['pd_amount'];
                    $data_pd['order_sn'] = $order_info['order_sn'];
                    $model_pd->changePd('order_comb_pay',$data_pd);
                }*/
                //记录订单日志
                $data = array();
                $data['order_id'] = $orderinfo['order_id'];
                $data['log_role'] = 'buyer';
                $data['log_msg'] = L('order_log_pay').' ( 支付平台交易号 : '.$trade_no.' )';
                $data['log_orderstate'] = ORDER_STATE_PAY;
                $insert = $model_order->addOrderLog($data);
                if (!$insert) {
                    throw new \Exception('记录订单日志出现错误');
                }
                
                if($orderinfo["order_type"] == "2"){//如果是分期购订单
                    $flag = $this->fabiao($orderinfo['order_id']);
                    if(!$flag){
                        break;
                    }
                }  
            }

            $m_record_model = M('money_record');
            $pay_amount = ncPriceFormat(floatval($order_info['order_amount']) - floatval($order_info['pd_amount']) - floatval($order_info['order_amount_delta']));
            if ($order_info['order_type'] == ORDER_TYPE_FACTORY){//工厂订单
                //记录流水
                $store_info = M('store')->where(array('store_id' => $order_info['store_id']))->field('member_id,member_name,store_name,deposit_avaiable')->find();
                $store_yu_e = $store_info['deposit_avaiable'];
                /*
                 * 区分付款方式
                 * 1.余额支付，需要从经销商账户扣款的同时向工厂账户打款
                 * 2.在线支付，需要向工厂账户打款
                 */
        
                /*抽佣*/
                unset($data);
                $data['created_at']    = TIMESTAMP;
                $data['pay_class']     = 'online';
                $data['money']         = $pay_amount*1.0*$store_info['ser_charge']/100;
                $data['business_type'] = 3;//0个人 1经销商 2家装设计公司 3工厂
                $data['is_pay']        = 1;//0:收入 1：支出
                $data['m_type']        = LIUSHUI_TYPE_COMMISSION;//金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购
                $data['member_id']     = $store_info['member_id'];
                $data['member_name']   = $store_info['member_name'];
                $data['store_id']      = $order_info['store_id'];
                $data['order_id']      = $order_info['order_id'];
                $data['order_sn']      = $order_info['order_sn'];
                $data['des']           = "VF订单，全木行抽佣工厂订单";
                $data['store_name']    = $order_info['store_name'];
                $data['yu_e']          = $store_yu_e + ($pay_amount*(100-$store_info['ser_charge'])*1.0/100);
                $insert = $m_record_model->add($data);
                if (!$insert) {
                    throw new \Exception('抽佣写入错误');
                }

                /*工厂入账*/
                $data['created_at']    = TIMESTAMP;
                $data['pay_class']     = 'online';
                $data['money']         = $pay_amount;
                $data['business_type'] = 3;//0个人 1经销商 2家装设计公司 3工厂
                $data['is_pay']        = 0;//0:收入 1：支出
                $data['m_type']        = LIUSHUI_TYPE_TIHUO;//金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购
                $data['member_id']     = $store_info['member_id'];
                $data['member_name']   = $store_info['member_name'];
                $data['order_id']      = $order_info['order_id'];
                $data['order_sn']      = $order_info['order_sn'];
                $data['des']           = "VF订单，经销商打款工厂";
                $data['store_id']      = $order_info['store_id'];
                $data['store_name']    = $order_info['store_name'];
                $data['yu_e']          = $store_yu_e+$pay_amount;

                $insert = $m_record_model->add($data);
                if (!$insert) {
                    throw new \Exception('记录我的收入出现错误');
                }

                unset($data_store);
                $data_store['deposit_avaiable'] = array('exp','deposit_avaiable+'.($pay_amount*(100-$store_info['ser_charge'])*1.0/100));
                if(!$update = M('store')->where(array('store_id'=>$order_info['store_id']))->save($data_store)){
                    throw new \Exception("抽佣失败", 1);
                }

            
                
                
                //判断支付类型，如果是余额支付，需要从经销商账目上扣除
                if($order_info['payment_code'] == "predeposit"){
                    //经销商记录
                    $store_info = M('store')->where(array('store_id' => $order_info['buyer_id']))->field('member_id,member_name,store_name')->find();
                    $pay_amount = ncPriceFormat(floatval($order_info['order_amount']) - floatval($order_info['pd_amount']) - floatval($order_info['order_amount_delta']));
                    $data = array();
                    $data['created_at']    = TIMESTAMP;
                    $data['pay_class']     = 'online';
                    $data['money']         = $pay_amount;
                    $data['business_type'] = 1;//0个人 1经销商 2家装设计公司 3工厂
                    $data['is_pay']        = 1;//0:收入 1：支出
                    $data['m_type']        = LIUSHUI_TYPE_TIHUO;//金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购
                    $data['member_id']     = $store_info['member_id'];
                    $data['member_name']   = $store_info['member_name'];
                    $data['store_id']      = $order_info['buyer_id'];
                    $data['order_id']      = $order_info['order_id'];
                    $data['order_sn']      = $order_info['order_sn'];
                    $data['des']           = "VF订单，全木行扣减经销商账户";
                    $data['store_name']    = $store_info['store_name'];
                    $data['yu_e']          = $store_yu_e - $pay_amount;

                    $insert = $m_record_model->add($data);
                    if (!$insert) {
                        throw new \Exception('记录我的支出出现错误');
                    }
                    
                    /*账户扣除*/    
                    unset($data_store);
                    $data_store['deposit_avaiable'] = array('exp','deposit_avaiable-'.$pay_amount);
                    if(!$update = M('store')->where(array('member_id'=>$order_info['buyer_id']))->save($data_store)){
                        throw new \Exception("c-v订单经销商扣费失败", 1);
                        
                    }
                }
                

            }elseif($order_info['order_type'] == ORDER_TYPE_ORDINARY){//普通订单
                $store_info = M('store')->where(array('store_id' => $order_info['store_id']))->field('member_id,member_name,store_name,ser_charge,deposit_avaiable')->find();
                $store_yu_e = $store_info['deposit_avaiable'];

                if(!is_numeric($store_info['ser_charge'])){
                    throw new \Exception("服务费不合法", 1);
                    
                }
                /*抽佣*/
                unset($data);
                $data['created_at']    = TIMESTAMP;
                $data['pay_class']     = 'online';
                $data['money']         = $pay_amount*1.0*$store_info['ser_charge']/100;
                $data['business_type'] = 1;//0个人 1经销商 2家装设计公司 3工厂
                $data['is_pay']        = 1;//0:收入 1：支出
                $data['m_type']        = LIUSHUI_TYPE_COMMISSION;//金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购
                $data['member_id']     = $store_info['member_id'];
                $data['member_name']   = $store_info['member_name'];
                $data['store_id']      = $order_info['store_id'];
                $data['order_id']      = $order_info['order_id'];
                $data['order_sn']      = $order_info['order_sn'];
                $data['des']           = "普通订单，全木行抽佣经销商";
                $data['store_name']    = $order_info['store_name'];
                $data['yu_e']          = $store_yu_e + ($pay_amount*(100-$store_info['ser_charge'])*1.0/100);
                $insert = $m_record_model->add($data);
                if (!$insert) {
                    throw new \Exception('抽佣写入错误');
                }


                //经销商记录
                $data = array();
                 $data['created_at']    = TIMESTAMP;
                 $data['pay_class']     = 'online';
                 $data['money']         = $pay_amount;
                 $data['business_type'] = 1;//0个人 1经销商 2家装设计公司 3工厂
                 $data['is_pay']        = 0;//0:收入 1：支出
                 $data['m_type']        = LIUSHUI_TYPE_ORDER;//金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购
                 $data['member_id']     = $store_info['member_id'];
                 $data['member_name']   = $store_info['member_name'];
                 $data['store_id']      = $order_info['store_id'];
                 $data['order_id']      = $order_info['order_id'];
                $data['order_sn']      = $order_info['order_sn'];
                $data['des']           = "普通订单，买家打款经销商";
                 $data['store_name']    = $order_info['store_name'];
                 $data['yu_e']          = $store_yu_e + $pay_amount;

                $insert = $m_record_model->add($data);
                if (!$insert) {
                    throw new \Exception('记录我的收入出现错误');
                }
                /*普通订单的结算方式只能是online ，所以只需要向卖家的可用余额账户打款即可*/
                 /*扣除佣金*/
                unset($data_store);
                $data_store['deposit_avaiable'] = array('exp','deposit_avaiable+'.($pay_amount*(100-$store_info['ser_charge'])*1.0/100));
                if(!$update = M('store')->where(array('store_id'=>$order_info['store_id']))->save($data_store)){
                    throw new \Exception("抽佣失败", 1);
                    
                }



            }

            if($flag){
                $model_order->commit();
                return array('success' =>true);
            }else{
                $model_order->rollback();
                return array('error' =>false);
            }
	        
	    } catch (\Exception $e) {
	        $model_order->rollback();
            return array('error' => $e->getMessage());
	    }
    }
    
    /**
          发标通用接口
     */
    public function fabiao($orderid){
        $start = 1;//每一天标的期数开始数字
        $tenderModel = M("tender");
        $today = date("Ymd", time());
        $tender = $tenderModel->where(array("date" => $today, 'type' => 0))->find();
        if($tender){
            $start = $start + $tender["count"];
            $tenderModel->where(array("date" => $today, 'type' => 0))->save(array("count" => $start));
        }else{
            $tenderModel->add(array("date" => $today, "count" => 1, 'type'=>0));
        }

        $fabiaoapi = API('easypay');
        $ordergoodsModel = M("order_goods");
        $imagelist = $ordergoodsModel->where(array("order_id" => $orderid))->field("goods_image,store_id")->select();
        $store_xx = M('order')->find($orderid);


        //查询store ,判断是等额本息还是等本降息
        $condition['store_id'] = $imagelist[0]['store_id'];
        $store_record = M('Store')->find($store_xx['store_id']);


        $interest_type = $store_record['is_discount'] == 1 ? 1 : 0;

        $pic = "";
        if(is_array($imagelist) && count($imagelist)){//获取图片路径
            foreach ($imagelist as $value){
                $pic .= 'http://'.$_SERVER['SERVER_NAME'].thumb(array("goods_image" => $value["goods_image"], "store_id" => $value["store_id"]), 60).",";
            }
        }
        $pic=trim($pic, ",");
        $order=M('order')->
              join('allwood_member on allwood_order.buyer_id = allwood_member.member_id')->
              field("order_sn,order_amount_delta,interest_total,interest_rate,period,factorage,mid,member_id")->
              where(array("order_id" =>$orderid))->
              find();//关联member 获取统一java接口的mid,这里是获取借款人mid
        

        $options = array();
        $options["picpath"] = $pic;
        $options["order_id"] = $order['order_sn'];//订单编号
        $options["borrow_uid"] = $order['mid'];//借款人id,也就是买家的统一平台的java id
        $options["borrow_name"] = "乐购分期".$today.$start."期";// 160713001期";//标题
        $options["borrow_money"] = $order["order_amount_delta"];//借款金额也就是差额
        $options["borrow_interest"] = $order["interest_total"];//总利息
        $options["borrow_interest_rate"] = $order["interest_rate"];//年化率（12.8%写入12.8）
        $options["borrow_duration"] = $order["period"];//借款期限(月数)
        //$options["fee"] = $order["factorage"] / $order["period"];//每期服务费(金额)
        $options["fee"] = $order["factorage"] ;
        $options["borrow_info"] = "为在\"全木行\"电商平台分期购置商品，详见证明资料，
                                        该借款人于".date("Y",time())."年".date("m",time())."月".date("d",time())."日向链金所申请借款。
                                        链金所对借款人进行了全面的背景调查及通过合作的征信机构对其信用情况、还款能力综合评估。
                                        该笔借款总额'{$options["borrow_money"]}'元，期限'{$options["borrow_duration"]}'个月，
                                        预期年化利率为'{$options["borrow_interest_rate"]}'，还款方息为按月等额本息还款";//标的详情
        //添加分期类型  等额本息还是是等本降息
        $options['interest_type'] = $interest_type;
        $result = $fabiaoapi->fabiao($options);
        $result = json_decode($result, true);

        if($result["code"] == 0){//成功
            M("order")->where(array("order_sn" => $order['order_sn']))->save(array("order_state" => ORDER_STATE_HANDLING));//修改订单状态为卖家处理中
            $flag=$this->setEdu($fabiaoapi,$order["mid"],$order["member_id"]);
            if($flag){
                return true;
            }else{
                return false;
            } 
        }else{//失败
            if($start != 1){
                $tenderModel->where(array("date" => $today))->save(array("count" => $start - 1));
            }
            return false;
        }
    }
    
    /**
     * 发标之后更新本地信用额度
     * @param string $memberid
     * @param object $fabiaoapi
     */
    public function setEdu($fabiaoapi,$uid,$memberid){
        $result=$fabiaoapi->get_credit_status(array("usrid"=>$uid));
        $result=json_decode($result,true);
        if($result["code"]=="0"){//成功
            //修改easypay_application 表中的可用额度和总额度
            $easypayModel=M("easypay_application");
            $flag=$easypayModel->where(array("member_id"=>$memberid))->save(array("credit_total"=>intval($result["return_param"]["loan_limit"]),"credit_available"=>intval($result["return_param"]["loan_useble"])));
            if($flag){
              return true;  
            }else{
              return false; 
            }
        }else{
            return false;
        }
    }
}
