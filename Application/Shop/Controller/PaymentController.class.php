<?php
namespace Shop\Controller;
use Think\Controller;
use Org\Util\CommonPay;
class PaymentController extends Controller {
    public function index(){
        $order_type = I('post.order_type/s');
        if ($order_type == 'product_buy') {
            $this->_product_buy();
        }elseif ($order_type == 'factory_buy'){
            $this->_factory_buy();
        }
    }

    private function _factory_buy(){
		$cart_id = I('cart_id/s');
		//取得商品ID和购买数量[cart_id] => 929|2
		$input_buy_items = explode('|', $cart_id);
		if (empty($input_buy_items)){
			$this->error('商品错误');
		}

		$goods_id = $input_buy_items[0];
		$quantity = $input_buy_items[1];
		$order_id = $input_buy_items[2];

        //验证收货地址
        $input_address_id = I('post.address_id', 0, 'int');
        if ($input_address_id <= 0) {
            $this->error('请选择收货地址');
        } else {
            $input_address_info = D('address')->getAddressInfo(array('address_id' => $input_address_id));
            if (!isset($order_id) && $input_address_info['member_id'] != session('member_id')) {
                $this->error('请选择收货地址');
            }
        }
        //收货地址城市编号
        $input_city_id = intval($input_address_info['city_id']);

        //取得商品最新在售信息
        $model_cart = D('cart');
        $goods_info = $model_cart->getGoodsOnlineInfo($goods_id, $quantity);
        if(empty($goods_info)) {
            $this->error('商品不存在');
        }

		$pay_amount = $goods_info['goods_num'] * $goods_info['goods_price'] + $goods_info['goods_freight'];
		/*测试用途，在测试模式在，在线支付永远为1分钱，不管商品的价格如何*/
		// if(APP_DEBUG||(C('MODE') == DEBUG)||(C('MODE') == TEST)){
		// 	$pay_amount = 0.01;
		// }

		if (I('post.payment_code/s') == 'predeposit'){
			$model_payment = D('payment');
			$pd_payment_info = $model_payment->getPaymentOpenInfo(array('payment_code' => 'predeposit'));
			if (empty($pd_payment_info)){
				$this->error('支付方式无效');
			}

			$store_info = D('Store')->getStoreInfo(array('store_id' => session('store_id')));

			$available_pd_amount = floatval($store_info['deposit_avaiable']);
			if ($available_pd_amount <= 0){
				$this->error('余额不足');
			}

			$order_amount = floatval($pay_amount);
			if ($available_pd_amount < $order_amount) {
				$this->error('余额不足');
			}
		}

        $buy_model = D('buy');

        $input = array();
        $input['pay_name']      = 'online';
        $input['pay_message']   = I('post.pay_message/s');
        $input['address_info']  = $input_address_info;
        $input['goods_info']    = $goods_info;
        $input['input_city_id'] = $input_city_id;
        $input['payment_code']  = I('post.payment_code/s');
		$input['reciver_address_id'] = $input_address_id;

        try {
            //开始事务
            $model_cart->startTrans();

            list($pay_sn, $order_list) = $buy_model->createOrderNew($input, session('store_id'), session('store_name'));

            //记录订单日志
            $buy_model->addOrderLog($order_list);

            //提交事务
            $model_cart->commit();

        }catch (Exception $e){

            //回滚事务
            $model_cart->rollback();
            $this->error('订单保存失败');
        }

        $model_order = D('order');
        $order_pay_info = $model_order->getOrderPayInfo(array('pay_sn' => $pay_sn, 'buyer_id' => session('store_id')));
        $order_pay_info['subject'] = iconv('gbk', 'utf-8',"商品购买_").$order_pay_info['pay_sn'];
        $order_pay_info['order_type'] = 'factory_buy';
        $order_pay_info['pay_amount'] = $pay_amount;

        $condition = array();
        $payment_model = D('payment');
        $condition['payment_code'] = I('post.payment_code/s');
        $payment_info = $payment_model->getPaymentOpenInfo($condition);

        $this->_api_pay($order_pay_info, $payment_info);
    }

	/**
	 * 商品购买
	 */
	private function _product_buy(){
		$pay_sn = $_POST['pay_sn'];
		$payment_code = $_POST['payment_code'];
		$period = $_POST['period'];
		$url = U('Shop/Member/myOrder');

		$valid = !preg_match('/^\d{18}$/',$pay_sn) || !preg_match('/^[a-z]{1,20}$/',$payment_code) || in_array($payment_code,array('offline','predeposit'));
		if($valid){
			$this->error('请求参数错误！');
		}

		$model_order = D('Order');
		$condition = array();
		$condition['pay_sn'] = $pay_sn;
		$condition['order_state'] = ORDER_STATE_NEW;
		$order_list = $model_order->getOrderList($condition,'','order_id,order_sn,order_amount,pd_amount,order_amount_delta');
		if (empty($order_list)) {
			$this->error('该订单不存在！');
		}

		$model_payment = D('Payment');
		$result = $model_payment->productBuy($pay_sn, $payment_code, $_SESSION['member_id']);

		if(!empty($result['error'])) {
			$this->error($result['error'], $url);
		}

		if ($payment_code != 'easypay'){
			//第三方API支付
			$this->_api_pay($result['order_pay_info'], $result['payment_info']);
		}else{
			$order_info = $model_order->getOrderInfo(array('pay_sn' => $pay_sn, 'order_state' => ORDER_STATE_NEW));
			if (empty($order_info)) {
				$this->error('订单处理成功！');
			}

			$easypay = $this->getEasypayInfo($order_info['order_amount']);
			if (empty($easypay)){
				$this->error('没有分期信息！');
			}

			$selected_easypay_info = array();
			foreach ($easypay as $key => $value) {
				if ($value['period'] == $period){
					$selected_easypay_info = $value;
					break;
				}
			}

			if (empty($selected_easypay_info)){
				$this->error('没有此分期期数！');
			}

			$order_amount_delta = $order_info['order_amount'] - ($order_info['order_amount'] % 100);//分期购订单支付差额
			$order_amount_delta = $order_info['order_amount'] > $_SESSION['easypay_credit_available'] ? $_SESSION['easypay_credit_available'] : $order_amount_delta;
			if ($order_amount_delta == $order_info['order_amount']){
				$return_status = $this->_api_easypay($pay_sn, $order_info, $selected_easypay_info, $order_amount_delta);
				if ($return_status == 1){
					$url = U('Shop/Payment/buysuccess',array('order_id' => $order_info['order_id']));
                    
					redirect($url);
				}else{
					$this->error('分期支付修改订单失败！');
				}
			}else{
				//补差价
				$data = array();
				//参与分期购的金额（不含利息 手续费)
				$data['order_amount_delta'] = $order_amount_delta;
				//期数
				$data['period']             = $selected_easypay_info['period'];
				//分期年化利率
				$data['interest_rate']      = $selected_easypay_info['interest_rate'];
				//平均到每一期的手续费
				$data['factorage']          = $selected_easypay_info['factorage'];
				//总利息
				$data['interest_total']     = $selected_easypay_info['interest_total'];
				$data['order_type']         = ORDER_TYPE_EASYPAY;

				$update = $model_order->editOrder($data, array('pay_sn' => $pay_sn, 'order_state' => ORDER_STATE_NEW));

				if ($update !== false) {
					$url = U('Shop/Payment/filleasypayprice',array('pay_sn' => $pay_sn));
					redirect($url);
				}
			}
		}
	}

	/**
	 * 分期支付逻辑
	 */
	private function _api_easypay($pay_sn, $order_info, $selected_easypay_info, $order_amount_delta){
		$payment_model = D('Payment');
		$model_order = D('Order');
		$buy_model = D('Buy');

		try {
			$model_order->startTrans();

			$data = array();
			$data['api_pay_state'] = 1;
			$update = $model_order->editOrderPay($data, array('pay_sn' => $pay_sn));
			if ($update === false) {
				return 0;
				//throw new Exception('更新订单状态失败');
			}

			$data = array();
			$data['order_state']	= ORDER_STATE_SUCCESS;
			$data['payment_time']	= TIMESTAMP;
			$data['payment_code']   = 'easypay';

			//订单类型
			$data['order_type']         = ORDER_TYPE_EASYPAY;
			//参与分期购的金额（不含利息 手续费)
			$data['order_amount_delta'] = $order_amount_delta;
			//期数
			$data['period']             = $selected_easypay_info['period'];
			//分期年化利率
			$data['interest_rate']      = $selected_easypay_info['interest_rate'];
			//平均到每一期的手续费
			$data['factorage']          = $selected_easypay_info['factorage'];
			//总利息
			$data['interest_total']     = $selected_easypay_info['interest_total'];
			//购销合同编号
			$data['gxht_code']          = $buy_model->makesn_for_easypay('GXHT');
			//发标协议编号
			$data['fbxy_code']          = $buy_model->makesn_for_easypay('FBXY');
			//借款协议编号
			$data['jkxy_code']          = $buy_model->makesn_for_easypay('JKXY');

			$update = $model_order->editOrder($data, array('pay_sn' => $pay_sn, 'order_state' => ORDER_STATE_NEW));
			if ($update === false) {
				throw new Exception('更新订单状态失败');
			}

			$flag = $payment_model->fabiao($order_info['order_id']);

			if($flag){
				$model_order->commit();
				return 1;
//				$url = urlShop('member', 'orderlist', array('order_state' => ORDER_STATE_SUCCESS));
//				showMessage('订单分期成功', $url);
			}else{
				$model_order->rollback();
				return 0;
//				showMessage('分期支付修改订单失败');
			}
		} catch (Exception $e) {
			$model_order->rollback();
			return 0;
//			showMessage('分期支付修改订单失败');
		}
	}

	/**
	 * 分期支付补差价
	 */
	public function fillEasypayPrice(){
		$pay_sn	= $_GET['pay_sn'];
		if (!preg_match('/^\d{18}$/',$pay_sn)){
			$this->error('该订单不存在1！', U('Shop/Member/myOrder'));
		}

		//查询支付单信息
		$model_order= D('Order');
		$pay_info = $model_order->getOrderPayInfo(array('pay_sn' => $pay_sn));
		if(empty($pay_info)){
			$this->error('该订单不存在！');
		}
		$this->assign('pay_info',$pay_info);

		//取子订单列表
		$condition = array();
		$condition['pay_sn'] = $pay_sn;
		$condition['order_state'] = ORDER_STATE_NEW;
		$order_info = $model_order->getOrderInfo($condition,array(),'order_id,order_amount,goods_amount,shipping_fee,pd_amount,order_amount_delta,order_type');
		if (empty($order_info)) {
			$this->error('未找到需要支付的订单！', U('Shop/Member/myOrder'));
		}

		$pay_amount_online = ncPriceFormat(floatval($order_info['order_amount'])-floatval($order_info['pd_amount'])-floatval($order_info['order_amount_delta']));
		//如果可用额度小于订单总额，差价为超过的部分金额
		$pay_amount_online = $order_info['order_amount'] > $_SESSION['easypay_credit_available'] ?  ncPriceFormat(floatval($order_info['order_amount'])-floatval($_SESSION['easypay_credit_available'])) : $pay_amount_online;

		/*测试用途，在测试模式在，在线支付永远为1分钱，不管商品的价格如何*/
		// if(C('debug') || C('MODE') === "TEST"){
		// 	$pay_amount_online = 0.01;
		// }

		$this->assign('pay_amount_online', $pay_amount_online);

		if ($pay_amount_online > 0){
			$model_payment = D('Payment');
			$condition = $payment_list = array();
			$payment_res = $model_payment->getPaymentOpenList($condition);
			if (!empty($payment_res)) {
				foreach($payment_res as $row){
					$payment_list[$row['payment_code']] = $row;
				}
				unset($payment_list['predeposit']);
				unset($payment_list['offline']);
				unset($payment_list['easypay']);
			}
			if (empty($payment_list)) {
				$this->error('暂未找到合适的支付方式！');
			}
			$this->assign('payment_list',$payment_list);
		}
        $this->display();
		//$this->display('fill_easypay_price');
	}

	/**
	 * 分期支付成功
	 */
	public function buySuccess(){
		$mid = $_SESSION['member_id'];
		$order_id = $_GET['order_id'];
		$order_model = D('Order');
		$order_info = $order_model->where(array('order_id' => $order_id))->field('order_id,order_state,order_type')->find();

		//必须是乐购订单
		if (!$mid || !$order_id || $order_info['order_state'] != ORDER_STATE_HANDLING || $order_info['order_type'] != ORDER_TYPE_EASYPAY){
			$this->error('订单不存在！');
		}

		$condition = array();
		$condition['order_id'] = $order_info['order_id'];
		$fields = 'reciver_name,reciver_info';
		$receive_info = $order_model->getOrderCommonInfo($condition, $fields);
		if (!empty($receive_info)){
			$receive_info['reciver_info'] = unserialize($receive_info['reciver_info']);
		}
		$this->assign('receive_info', $receive_info);
		$this->assign('order_id', $order_id);

		$this->display();
	}

    /**
     * 第三方在线支付接口
     *
     */
    private function _api_pay($order_info, $payment_info) {
        if($payment_info['payment_code'] == 'chinabank' OR $payment_info['payment_code'] == 'chinapay') {
            $gateway = CommonPay::getInstance('UnionPay_Express', 1);

            $order = [
                'orderId'   => $order_info['pay_sn'], //Your order ID
                'txnTime'   => date('YmdHis'), //Should be format 'YmdHis'
                'orderDesc' => '全木行'.$order_info['subject'], //Order Title
                //'txnAmt'    => $order_info['pay_amount']*100, //Order Total Fee
                'txnAmt'    => 1, //测试1分钱
            ];

            $response = $gateway->send($order);
            $response->redirect();

        }else if($payment_info['payment_code'] == 'wxpay') {
	        $gateway = CommonPay::getInstance('WechatPay', 1);

	        //重新生成微信支付订单号
	        $buy_model = D('Buy');
	        $order_model = D('Order');
	        $wx_pay_sn = $buy_model->makePaySn($_SESSION['member_id']);
	        $order_model->editOrder(array('wx_pay_sn' =>$wx_pay_sn), array('pay_sn' => $order_info['pay_sn']));  //更新订单

	        $order = array (
		        'body'             => '全木行'.$order_info['subject'], //Your order ID
		        'out_trade_no'     => $wx_pay_sn, //Should be format 'YmdHis'
		        'total_fee'        => $order_info['pay_amount'] * 100, //Order Title
		        //'total_fee'        => 1, //测试1分钱
		        'attach'           => $order_info['pay_sn'],
		        'spbill_create_ip' => '114.119.110.120', //Order Total Fee
	        );

	        $response = $gateway->send($order);
	        $_SESSION['product_buy_wxpay_url'][$order_info['pay_sn']] = $response->getCodeUrl();

	        $param['pay_sn'] = $order_info['pay_sn'];
	        $param['pay_amount'] = $order_info['pay_amount'];
			$param['order_type'] = $order_info['order_type'];
	        $param = base64_encode(serialize($param));
			
            @header("Location: ".U('Buy/wxpay', array('param' => $param)));

        }else if($payment_info['payment_code'] == 'alipay') {
            $gateway = CommonPay::getInstance('Alipay_Express', 1);

            $order = [
                'out_trade_no' => $order_info['pay_sn'], //your site trade no, unique
                'subject'      => '全木行'.$order_info['subject'], //order title
                'total_fee'    => $order_info['pay_amount'], //order total fee
                //'total_fee'    => '0.01', //测试1分钱
            ];

            $response = $gateway->send($order);
            $response->redirect();
        }else if ($payment_info['payment_code'] == 'predeposit'){
            $this->pdPay($order_info);
        }
    }

	/**
	 *获取分期购信息
	 */
	public function getEasypayInfo($amount){
		//检查是否开启
		if(is_null(C('easybuy_status')) || (C('easybuy_status') == 0)){
			return array();
		}

		$pay_by_period = unserialize(C('pay_by_period'));
		$pay_by_period2 = $pay_by_period;
		$qishu = implode(';', array_keys($pay_by_period));
		$rate = array_values($pay_by_period);
		foreach ($rate as $key => $value) {
			$rate[$key] = number_format($value/100,3,'.','');
		}
		$rate = implode(';', $rate);

		//计算分期费用
		$easypay_api = API('easypay');
		$options['amount']        = $amount;
		$options['interest_type'] = 1;
		$options['periods']       = $qishu;
		$options['interest_rate'] = $rate;
		$options['factorage']     = C('easypay_charge_by_ccfax')/ 100;
		$result = json_decode($easypay_api->get_interest_info($options), true);
		if($result['code'] == 0){
			$pay_by_period = $result['return_param']['interests_list'];
		}

		//封装利息参数
		foreach ($pay_by_period as $key => $value) {
			$pay_by_period[$key]['factorage_rate'] = $options['factorage'];
			$pay_by_period[$key]['interest_rate'] = $pay_by_period2[$value['period']];
		}
		return $pay_by_period;
	}

    /**
     * 通知处理(支付宝异步通知和网银在线自动对账)
     *
     */
    public function notify(){
        switch($_GET['payment_code']){
            case 'Alipay_Express':
	            \Think\Log::lol(json_encode($_POST));
                $success				= 'success';	// 成功返回值
                $fail					= 'fail';		// 失败返回值
                $out_trade_no			= $_POST['out_trade_no'];	// 商户网站唯一订单号
                $trade_no				= $_POST['trade_no'];	// 支付宝交易号
                break;
            case 'UnionPay_Express':
				\Think\Log::lol(json_encode($_POST));
                $success = 'success';
                $fail = 'fail';
                $out_trade_no			= $_POST['orderId'];	// 商户网站唯一订单号
                $trade_no				= $_POST['queryId'];		// 支付宝交易号
                break;
            case 'WechatPay':
                $xml = file_get_contents('php://input');
                $data = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	            \Think\Log::lol(json_encode($data));
                $success = 'SUCCESS';
                $fail = 'FAIL';
                $out_trade_no			= $data->attach;	// 商户网站唯一订单号
                $trade_no				= $data->transaction_id;		// 微信支付订单号
                break;
            default:
                exit('error');
                break;
        }
        $this->notifyResult($success,$fail,$out_trade_no,$trade_no, $_GET['type']);
    }

    /**
     * 异步通知处理
     */
    private function notifyResult($success,$fail,$out_trade_no,$trade_no, $type = 1){
        //参数判断
        if(empty($out_trade_no)) exit($fail);
        if(!preg_match('/^\d{18}$/',$out_trade_no)) exit($fail);

        if ($type == 1){
            //商品购买
            $model_order = D('order');
            $order_pay_info	= $model_order->getOrderPayInfo(array('pay_sn' => $out_trade_no));
            if(!is_array($order_pay_info) || empty($order_pay_info)) exit($fail);
            if (intval($order_pay_info['api_pay_state'])) exit($success);

            //取得订单列表和API支付总金额
            $order_list = $model_order->getOrderList(array('pay_sn' => $out_trade_no,'order_state' => ORDER_STATE_NEW));
            if (empty($order_list)) exit($success);
            $pay_amount = 0;
            foreach($order_list as $order_info) {
                $pay_amount += ncPriceFormat(floatval($order_info['order_amount']) - floatval($order_info['pd_amount']) - floatval($order_info['order_amount_delta']));
            }
            $order_pay_info['pay_amount'] = $pay_amount;
        }else if($type == 2){
            //预存款充值
            $model_pd = D('predeposit');
            $order_pay_info = $model_pd->getPdRechargeInfo(array('pdr_sn' => $out_trade_no));
            $order_pay_info['pay_amount'] = $order_pay_info['pdr_amount'];
            if(!is_array($order_pay_info) || empty($order_pay_info)) exit($fail);
            if (intval($order_pay_info['pdr_payment_state'])) exit($success);
        }else{
		    //经销商充值
	        $model_order = D('Order');
	        $order_pay_info = $model_order->getOrderPayInfo(array('pay_sn' => $out_trade_no));
	        if(!is_array($order_pay_info) || empty($order_pay_info)) exit($fail);
	        if (intval($order_pay_info['api_pay_state'])) exit($success);
	    }

        switch ($_GET['payment_code']){
            case 'Alipay_Express':
                $payment_code = 'alipay';
                break;
            case 'UnionPay_Express':
                $payment_code = 'chinapay';
                break;
            case 'WechatPay':
                $payment_code = 'wxpay';
                break;
        }

        //取得支付方式信息
        $model_payment = D('payment');
        $payment_info = $model_payment->getPaymentOpenInfo(array('payment_code' => $payment_code));
        if(!is_array($payment_info) or empty($payment_info)) exit($fail);

        if ($type == 1){
            $result = $model_payment->updateProductBuy($out_trade_no, $payment_info['payment_code'], $order_list, $trade_no);
            if(!empty($result['error'])) {
                exit($fail);
            }
            exit($success);
        }else if ($type == 2){
            //预存款充值
            $condition = array();
            $condition['pdr_sn'] = $out_trade_no;
            $condition['pdr_payment_state'] = 0;
            $recharge_info = $model_pd->getPdRechargeInfo($condition);
            if (!$recharge_info) {
                exit($fail);
            }
            $condition = array();
            $condition['pdr_sn'] = $recharge_info['pdr_sn'];
            $condition['pdr_payment_state'] = 0;
            $update = array();
            $update['pdr_payment_state'] = 1;
            $update['pdr_payment_time'] = TIMESTAMP;
            $update['pdr_payment_code'] = $payment_info['payment_code'];
            $update['pdr_payment_name'] = $payment_info['payment_name'];
            $update['pdr_trade_sn'] = $trade_no;

            try {
                $model_pd->startTrans();
                //更改充值状态
                $state = $model_pd->editPdRecharge($update,$condition);
                if (!$state) {
                    exit($fail);
                }

                //变更会员预存款
                $data = array();
                $data['member_id'] = $recharge_info['pdr_member_id'];
                $data['member_name'] = $recharge_info['pdr_member_name'];
                $data['amount'] = $recharge_info['pdr_amount'];
                $data['pdr_sn'] = $recharge_info['pdr_sn'];
                $model_pd->changePd('recharge',$data);
                $model_pd->commit();
                exit($success);
            } catch (Exception $e) {
                $model_pd->rollback();
                exit($fail);
            }
        }else{
	        //经销商充值
	        $model_order = D('Order');
	        $data = array();
	        $data['api_pay_state'] = 1;
	        $update = $model_order->editOrderPay($data, array('pay_sn' => $out_trade_no));
	        if (!$update) {
		        throw new Exception('更新订单状态失败');
	        }
	        //存入可用余额
	        $update = M()->execute("update allwood_store set deposit_avaiable = deposit_avaiable + " . $order_pay_info['recharge_money'] . " where member_id = " . $order_pay_info['buyer_id']);
	        if($update === false){
		        \Think\Log::lol('update deposit_available failed, pay_sn ' . $order_pay_info['pay_sn']);
		        exit($fail);
	        }
	        //插入记录
	        $store_info = M('store')->where(array('member_id' => $order_pay_info['buyer_id']))->find();
	        $data['member_id'] = $order_pay_info['buyer_id'];
	        $data['member_name'] = $store_info['seller_name'];
	        $data['store_id'] = $store_info['store_id'];
	        $data['store_name'] = $store_info['store_name'];
	        $data['m_type'] = 2;
	        $data['is_pay'] = 0;
	        $data['business_type'] = 1;
	        //$data['pay_class'] = 'online';
            $data['pay_class'] = $payment_info['payment_name'];
	        $data['money'] = $order_pay_info['recharge_money'];
	        $data['yu_e'] = $store_info['deposit_avaiable'];
	        $data['created_at'] = TIMESTAMP;
			M('money_record')->add($data);

	        exit($success);
        }
    }

    /**
     * 预存款支付
     */
    private function pdPay($order_info) {
        $model_payment = D('payment');
        $pd_payment_info = $model_payment->getPaymentOpenInfo(array('payment_code' => 'predeposit'));
        if (empty($pd_payment_info)){
            $this->error('支付方式无效');
        }

        $store_info = D('Store')->getStoreInfo(array('store_id' => session('store_id')));

        $available_pd_amount = floatval($store_info['deposit_avaiable']);
        if ($available_pd_amount <= 0){
            $this->error('余额不足');
        }

        $model_pd = D('predeposit');

        $order_amount = floatval($order_info['pay_amount']);

        if ($available_pd_amount < $order_amount) {
            $this->error('余额不足');
        }

        try {
            $model_pd->startTrans();

            $data_pd['deposit_avaiable'] = array('exp','deposit_avaiable-'.$order_amount);
            $update = M('store')->where(array('store_id' => session('store_id')))->save($data_pd);
            if (!$update) {
                throw new Exception('操作失败');
            }

            $model_order = D('Order');
            $order_info_id = $model_order->getOrderInfo(array('pay_sn' => $order_info['pay_sn']), array(), 'order_id');

            //记录订单日志(已付款)
            $data = array();
            $data['order_id'] = $order_info_id['order_id'];
            $data['log_role'] = 'buyer';
            $data['log_msg'] = L('order_log_pay');
            $data['log_orderstate'] = ORDER_STATE_PAY;
            $insert = $model_order->addOrderLog($data);
            if (!$insert) {
                throw new Exception('记录订单日志出现错误');
            }

            //订单状态 置为已支付
            $data_order = array();
            $data_order['order_state'] = ORDER_STATE_PAY;
            $data_order['payment_time'] = TIMESTAMP;
            $data_order['payment_code'] = 'predeposit';
            $data_order['pd_amount'] = $order_amount;
            $result = $model_order->editOrder($data_order,array('order_id' => $order_info_id['order_id']));
            if (!$result) {
                throw new Exception('订单更新失败');
            }

            /*//更新预订单状态
            D('offline_provisional_order')->editOfflineOrder(array('is_deal'=>1),array('provisional_order_id'=>array('in',$input['order_id_str'])));
            //更新预订单下商品状态
            D('offline_provisional_order_goods')->editOfflineOrderGoods(array('status'=>1),array('provisional_order_id'=>array('in',$input['order_id_str']),
                'goods_id'=>array('in',$input['goods_id_str'])));*/

            //提交事务
            $model_pd->commit();
            $this->success('余额支付成功', U('Vendor/supplierorder'));

        }catch (Exception $e) {
            $model_pd->rollback();
            $this->error('余额支付失败');
        }
    }

    /**
     * 支付成功
     */
    public function payment_success(){
        if ($_GET['predeposit']) {
            $url = U('Predeposit/index');
        } else if($_GET['store_fund']){
            $url = U('Store_joinin/index');
        }else{
            $url = U('Member/myorder', array('order_state' => ORDER_STATE_SUCCESS));
        }
        $this->success('支付成功', $url);
    }

    /**
     * 生成支付二维码
     */
    public function createQRCode(){
        require_once(__ROOT__.'data/phpqrcode/index.php');
        $PhpQRCode = new \PhpQRCode();
        \QRcode::png(base64_decode($_GET['wxqrcode']),false,'L',8,1);
    }

    /**
     * 判断是否支付
     *
     */
    public function isPayed(){
        $info = array();
        $info['status'] = '0';
        if(!$_POST['pay_sn'] || !$_POST['order_type']){
            echo json_encode($info);
            exit;
        }
        switch ($_POST['order_type']){
	        case 1 :
			case 2 :
			case 4 :
                $model_order = D('Order');
                $condition['pay_sn'] = $_POST['pay_sn'];
                $order_pay_info	= $model_order->getOrderPayInfo($condition);
                if(!empty($order_pay_info)){
                    if($order_pay_info['api_pay_state'] == '1'){
                        $info['status'] = '1';
                    }
                }
            break;
//            case 'store_fund':
//                $model_order = D('StoreFund');
//                $condition['store_fund_sn'] = $_POST['pay_sn'];
//                $order_pay_info	= $model_order->getStoreFundInfo($condition);
//                log::i(' order info = '.encode_json($order_pay_info), 'ali');
//                if(!empty($order_pay_info)){
//                    if($order_pay_info['store_fund_payment_state'] == '1'){
//                        $info['status'] = '1';
//                    }
//                }
//            break;
            default:
                $model_order = D('Predeposit');
                $condition['pdr_sn'] = $_POST['pay_sn'];
                $order_pay_info	= $model_order->getPdRechargeInfo($condition);
                if(!empty($order_pay_info)){
                    if($order_pay_info['pdr_payment_state'] == '1'){
                        $info['status'] = '1';
                    }
                }
            break;
        }

        echo json_encode($info);
    }
}