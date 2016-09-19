<?php
namespace Shop\Controller;
use Think\Controller;
class BuyController extends Controller {
	/**
	 * 选择分期购对话框模板
	 * @return [type] [description]
	 */
	public function tpl_easypay_checkOp(){
		$this->display('./easypay_check');
	}

	/**
	 * 分期购订单完成
	 * @return [type] [description]
	 */
	public function easypay_order_completeOp(){
		$hour = date('H', time());
		if(($hour>0)&&($hour<=9)){
			$msg = "分期购订单提交处理中，将在当天10点前处理完成";
		}else if(($hour>9)&&($hour<=18)){
			$msg = "分期购订单提交处理中，将在2小时内处理完成";
		}else{
			$msg = "分期购订单提交处理中，将在第二天10点前处理完成";
		}

		$order_info = D('Order')->getOrderInfo(array('pay_sn' => $_GET['pay_sn']), array('order_common'));
		$this->assign('msg', $msg);
		$this->assign('order_info', $order_info);
		$this->display('./easypay_order_complete');
	}

	/*
	 * 正在等待卖家定价
	 */
	public function neGoing(){
		$pay_sn	= $_GET['pay_sn'];
		 if (!preg_match('/^\d{18}$/',$pay_sn)){
		 	$this->error('该订单不存在！', U('Shop/Member/myOrder'));
		 }

		//查询订单状态
		$model_order= D('Order');
		$condition['pay_sn'] = $pay_sn;
		$condition['buyer_id'] = $_SESSION['member_id'];
		$pay_info = $model_order->getOrderInfo($condition, array('store'), 'order_state,store_id');
		 if(empty($pay_info)){
		 	$this->error('该订单不存在！', U('Shop/Member/myOrder'));
		 }

		if ($pay_info['order_state'] == 10){
			redirect(U('Shop/Buy/checkPrice', array('pay_sn' => $pay_sn)));
		}

		$this->assign('store_tel', $pay_info['extend_store']['store_tel']);
		$this->assign('pay_sn', $pay_sn);
		$this->display();
	}

	public function isGivePrice(){
		if(!$_POST['pay_sn']){
			$return['code'] = 0;
			$return['resultText']['message'] = "没有订单编号";
			exit(json_encode($return));
		}
		$model_order = D('Order');
		$condition['pay_sn'] = $_POST['pay_sn'];
		$condition['buyer_id'] = $_SESSION['member_id'];
		$order_pay_info	= $model_order->getOrderInfo($condition, array(), 'order_state');
		if(!empty($order_pay_info)){
			if($order_pay_info['order_state'] == 1){
				$return['code'] = 0;
				$return['resultText']['message'] = "定价中";
			}else{
				$return['code'] = 1;
				$return['resultText']['message'] = "成功！";
			}
			exit(json_encode($return));
		}
	}

	/*
     * 同意定价，结算
     */
	public function checkPrice(){
		$pay_sn	= $_GET['pay_sn'];
		if (!preg_match('/^\d{18}$/',$pay_sn)){
			$this->error('该订单不存在！', U('Shop/Member/myOrder'));
		}

		//查询支付单信息
		$model_order= D('Order');
		$pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']));
		if(empty($pay_info)){
			$this->error('该订单不存在！', U('Shop/Member/myOrder'));
		}
		$this->assign('pay_info', $pay_info);

		$fields = 'order_id,order_state,order_amount,goods_amount,shipping_fee,order_sn';
		$condition = array();
		$condition['pay_sn'] = $pay_sn;
		$condition['order_state'] = ORDER_STATE_NEW;
		$order_info = $model_order->getOrderInfo($condition, array(), $fields);
		if (empty($order_info)) {
			$this->error('未找到需要支付的订单！', U('Shop/Member/myOrder'));
		}

		$order_info['easypay_amount'] = $order_info['order_amount'] - $order_info['order_amount'] % 100;
		$this->assign('order_info', $order_info);

		$condition = array();
		$condition['order_id'] = $order_info['order_id'];
		$fields = 'goods_id,goods_name,goods_num,goods_price,goods_pay_price';
		$order_goods_list = $model_order->getOrderGoodsList($condition, $fields);
		$this->assign('order_goods_list', $order_goods_list);

		//显示支付接口列表
		//兼容分期购，需要区分对待订单
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
			$this->error('暂未找到合适的支付方式！', U('Shop/Member/myOrder'));
		}
		$this->assign('payment_list', $payment_list);

		//=============分期信息
		$easypay_api = API('easypay');
		$result = json_decode($easypay_api->get_credit_status(array('usrid' => $_SESSION['mid'])), true);
		if($result['code'] == 0){
//			if(!C('debug')){
				//写入数据库
				$application_model = D('Easypayapplication');
				$data = $result['return_param'];
				//总额度
				$arr['credit_total']     = $data['loan_limit'];
				//可用额度
				$arr['credit_available'] = $data['loan_useble'];
				//授信状态
				$arr['credit_status']    = $data['check_flag'];
				$application_model->insert_update($_SESSION['member_id'], $arr);

				$_SESSION['easypay_credit_status'] = $data['check_flag'];
				$_SESSION['easypay_credit_total'] = $data['loan_limit'];
				$_SESSION['easypay_credit_available'] = $data['loan_useble'];
				$_SESSION['easypay_freeze'] = $_SESSION['easypay_credit_status'] === -1 ? 1 : 0;
				$_SESSION['easypay_status_zh'] = str_replace(
					array(0, 1, 3, 2, 5, 4, 6),
					array('未开启', '审批中','审批中', '审批未通过', '已开通', '审批未通过','冻结' ),
					intval($_SESSION['easypay_credit_status']));
//			}
		}

		//if ($_SESSION['easypay_credit_status'] == 5){
		$easypay = $this->_getEasypayInfo($order_info['order_amount']-$order_info['order_amount'] % 100);
		/*参与分期的金额只能是100的整数倍，所以*/
		$this->assign('easypay', $easypay);
		//}
		//
		//

		$fill_easypay_price = $order_info['order_amount'] % 100;
		$this->assign('fill_easypay_price', $fill_easypay_price > 0 ? $fill_easypay_price : 0);

		$condition = array();
		$condition['order_id'] = $order_info['order_id'];
		$fields = 'reciver_name,reciver_info';
		$receive_info = $model_order->getOrderCommonInfo($condition, $fields);
		if (!empty($receive_info)){
			$receive_info['reciver_info'] = unserialize($receive_info['reciver_info']);
		}
		$this->assign('receive_info', $receive_info);

		$this->display('checkprice');
	}

	/**
	 *获取分期购信息
	 */
	private function _getEasypayInfo($amount){
		//检查是否开启
		$easybuy_status = M('setting')->where(array('name' => 'easybuy_status'))->getField('value');
		if(is_null($easybuy_status) || ($easybuy_status == 0)){
			return array();
		}

		$pay_by_period = M('setting')->where(array('name' => 'pay_by_period'))->getField('value');
		$pay_by_period = unserialize($pay_by_period);
		$pay_by_period2 = $pay_by_period;
		$qishu = implode(';', array_keys($pay_by_period));
		$rate = array_values($pay_by_period);
		foreach ($rate as $key => $value) {
			$rate[$key] = number_format($value/100,3,'.','');
		}
		$rate = implode(';', $rate);

		//计算分期费用
		$easypay_charge_by_ccfax = M('setting')->where(array('name' => 'easypay_charge_by_ccfax'))->getField('value');
		$easypay_api = API('easypay');
		$options['amount']        = $amount;
		$options['interest_type'] = 1;//1：贴息 0：不贴息
		$options['periods']       = $qishu;
		$options['interest_rate'] = $rate;
		$options['factorage']     = $easypay_charge_by_ccfax/ 100;
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
	 * 提交询价申请
	 */
	public function buyStep1Nego(){
		\Think\Log::ext_log('==='.json_encode($_POST).'   get = '.json_encode($_GET), 'api');

		$model_buy = D('Buy');
		$result = $model_buy->buyStep1_xunjia($_POST['cart_id'], $_POST['ifcart'], $_SESSION['member_id'], $_SESSION['store_id']);
		if($_GET['ajax']){
			//如果是异步请求，返回json
			$res['code'] = 1;
			$res['resultText']['message'] = "操作成功!";
			$res['resultText']['url'] = U('/shop/buy/negoing');
			$this->ajaxReturn($res);
		}
		if (count($result['store_cart_list']) > 1){
			$this->error("只能选择一家商品的商品!");
		}

		$this->assign('address_info', $result['address_info']);
		$this->assign('store_cart_list', $result['store_cart_list']);
		$this->assign('store_cart_list_serialize', json_encode($result['store_cart_list']));
		$this->assign('ifcart', $result['ifcart']);
		$this->display();
	}

	/**
	 * 立即购买工厂商品
	 */
	public function buy_step1_fact(){
		$model_payment = D('payment');
		$condition = array();
		$payment_list = $model_payment->getPaymentUseList($condition);

		if (!empty($payment_list)) {
			unset($payment_list['offline']);
			unset($payment_list['easypay']);
		}
		if (empty($payment_list)) {
			$this->error('暂未找到合适的支付方式');
		}
		$this->assign('payment_list', $payment_list);

		$model_buy = D('buy');
		$goods_info = $model_buy->buy_step1_to_fact(I('cart_id'), session('store_id'));
		if (!empty($goods_info['error'])){
			$this->error($goods_info['error']);
		}

		$address_model = D('address');

		$buy_items = explode('|', I('cart_id'));
		$order_id = $buy_items[2];

		$default_receive_info = array();
		if (isset($order_id)){
			$model_order = D('Order');
			$condition = array();
			$condition['order_id'] = $order_id;
			$buyer_address_id = $model_order->getOrderCommonInfo($condition, 'reciver_address_id');
			if (empty($buyer_address_id['reciver_address_id'])) {
				$this->error('没有买家收货地址');
			}
			$buyer_address_info = $address_model->getAddressInfo(array('address_id' => $buyer_address_id['reciver_address_id']));
			$this->assign('buyer_address_info', $buyer_address_info);
			$default_receive_info = $buyer_address_info;
		}

		//收货地址
		$field = 'address_id,true_name,area_info,address,mob_phone,tel_phone,is_default';
		$address_info = $address_model->getAddressList(array('member_id' => session('member_id'), 'tesu_deleted' => 0), 'is_default desc,address_id desc', $field);
		
		if ($address_info[0]['is_default'] == 1){
			$default_receive_info = $address_info[0];
		}

		$this->assign('default_receive_info', $default_receive_info);
		$this->assign('address_info', $address_info);
		$this->assign('cart_id', I('cart_id'));
		$this->assign('goods_info', $goods_info);
		$this->display();
	}


	//预订单结算第一步
	public function buy_pre_step1() {
		$model_buy = Model('buy');

		//计算运费
		$result = $model_buy->buyPreStep1($_SESSION['member_id'], $_SESSION['store_id']);
		$con['customer_id']=$_SESSION['mid'];
		$con['offline_provisional_order_goods.goods_id']=array('in',$_POST['goods_id_str']);
		$con['offline_provisional_order_goods.provisional_order_id']=array('in',$_POST['order_id_str']);
		$con['offline_provisional_order_goods.status']=0;//0未成交，1已成交，2已删除
		$order_list=Model('member')->getPreOrderList($con);//echo "<pre>";print_r($order_list);exit;
		$total_shipping_fee='';
		if($_POST['goods_id_str']){
			foreach(explode(',',$_POST['goods_id_str']) as $k1=>$v1){
				$fee=Model('goods')->where(array('goods_id'=>$v1))->get_field('goods_freight');
				$order_id_arr=Model('offline_provisional_order_goods')->where(array('goods_id'=>$v1))->field('provisional_order_id')->select();//一个商品可能对应多个订单
				foreach($order_id_arr as $k2=>$v2){
					if(in_array($v2['provisional_order_id'],explode(',',$_POST['order_id_str']))){
						$fee_arr[$v2['provisional_order_id']][]=$fee;
					}
				}

			}
			foreach($fee_arr as $k=>$v){
				foreach($order_list as $k2=>$v2){
					$order_list[$k]['every_order_fee']=max($v);
				}
				$total_shipping_fee+=max($v);
			}

		}
		// echo "<pre>";print_r($fee_arr);exit;
		//输出用户默认收货地址
		Tpl::output('address_info', $result['address_info']);
		//显示使用预存款支付及会员预存款
		Tpl::output('available_pd_amount', $result['available_predeposit']);
		Tpl::output('amount', $_POST['amount']);
		Tpl::output('order_list', $order_list);
		Tpl::output('total_shipping_fee', $total_shipping_fee);
		Tpl::output('total_goods_price', $_POST['total_goods_price']);
		Tpl::output('goods_id_str', $_POST['goods_id_str']);//print_r($_POST['goods_id_str']);exit;
		Tpl::output('order_id_str', $_POST['order_id_str']);//print_r($_POST['order_id_str']);exit;
		$model_member = Model('member');
		$orig_paypwd = $model_member->getfby_member_id($_SESSION['member_id'], 'member_paypwd');
		// $orig_paypwd = $model_member->getfby_member_id($_SESSION['member_id'], 'member_passwd');
		Tpl::output('orig_paypwd', $orig_paypwd);

		//标识 购买流程执行第几步
		Tpl::output('buy_step','step2');
		Tpl::showpage('buy_pre_step1');
	}

	/**
	 * 订单预处理
	 * @param  [type] $investpay_only [description]
	 * @param  [type] $easypay_only   [description]
	 * @return [type]                 [description]
	 */
	private function beforeBuy($investpay_only, $easypay_only){
		if($investpay_only)
			$this->investpaySubmit();
		if($easypay_only)
			$this->easypaySubmit();
	}

	/**
	 * 提交分期购买
	 * @return [type] [description]
	 */
	private function easypaySubmit(){
		$qishu = $_POST['qishu'];
		//如果是分期购
		//需要向远端提交借款请求
		$api =API('user');

		$cart_id = $_POST['cart_id'][0];
		list($goodsid, $quantity) = explode('|', $cart_id);

		if(intval($goodsid) == 0){
			showMessage("商品ID必须为数字", "/index.php",'html', 'error');
		}

		$goodsModel = Model();
		$where['goods_id'] = $goodsid;
		$goods = $goodsModel->table('goods,goods_common')
			->join('left join')
			->on('goods.goods_commonid = goods_common.goods_commonid')
			->where($where)->find();
		if(!$goods){
			showMessage("商品信息不存在！", "/index.php",'html', 'error');
		}


		$model_goods = Model('goods');
		$goods_detail = $model_goods->getGoodsDetail($goodsid, '*');
		$goods_info = $goods_detail['goods_info'];
		if (empty($goods_info)) {
			showMessage(L('goods_index_no_goods'), '', 'html', 'error');
		}


		if($goods_info['promotion_type']=="xianshi"){
			$tmp = $goods_info['promotion_price'];
		}else if($goods_info['promotion_type']=="groupbuy"){
			$tmp = $goods_info['promotion_price'];
		}else{
			$tmp = $goods_info['goods_price'];
		}


		$goodsname = $goods['goods_name'];
		$goodsprice = $goods['goods_price'];
		$goods_commonid = $goods['goods_commonid'];

		//查询finalcial easypay 表
		unset($where);
		$where['goods_commonid'] = $goods_commonid;
		$where['status'] = "1"; //开启状态
		$easypayModel = Model('financial_easypay');
		$easypayRecord = $easypayModel->where($where)->find();
		if(!$easypayRecord){
			showMessage("该商品未配置分期购信息！", "/index.php",'html', 'error');
		}
		$note = unserialize($easypayRecord['note']);
		$config = $note[$qishu];

		//获取goodsid
		//获取goodscommonid
		//获取finalcial easypay 表格信息
		$options['usrid']                = $_SESSION['mid'];
		$options['loan_money']           = $tmp;
		$options['transfer_account']     = "000000";
		$options['borrow_name']          = $goodsname;
		$options['borrow_interest_rate'] = $config['rate'];
		$options['borrow_duration']      = $config['duration'];
		$options['is_setmonthday']       = "1";
		//$options['colligate']            = $config['colligate'];
		$options['colligate']            = "1";
		$options['borrow_info']          = $easypayRecord['description']?$easypayRecord['description']:"";
		$options['picpath']              = $easypayRecord['images']?$easypayRecord['images']:"";

		$result  = json_decode($api->applyForLoan($options), true);
		if($result['code'] != "0"){
			showMessage("分期购买失败!".$result['resultText'],"/index.php",'html','error');
		}
	}

	/**
	 * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
	 *
	 */
	public function buyStep2() {
		$model_buy = D('Buy');
		$result = $model_buy->buyStep2($_POST, $_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_email']);

		if(!empty($result['error'])) {
			showMessage($result['error'], '', 'html', 'error');
		}

		$tmp_return['code'] = 1;
		//$tmp_return['resultText']['url'] = urlShop('buy', 'pay', array('pay_sn' => $result['pay_sn']));
		$tmp_return['resultText']['url'] = urlShop('buy', 'negoing', array('pay_sn' => $result['pay_sn']));
		exit(json_encode($tmp_return));
		//转向到商城支付页面
		//$pay_url = 'index.php?act=buy&op=pay&pay_sn='.$result['pay_sn'];
		//redirect($pay_url);
	}

	public function buy_pre_step2Op(){
		$model_buy = Model('buy');
		$con['customer_id']=$_SESSION['mid'];
		$con['offline_provisional_order_goods.goods_id']=array('in',$_POST['goods_id_str']);
		$con['offline_provisional_order_goods.provisional_order_id']=array('in',$_POST['order_id_str']);
		$con['offline_provisional_order_goods.status']=0;//0未成交，1已成交，2已删除
		//同一订单商品价格相加
		$amount=trim($_POST['amount']);
		$arr=explode('|',$amount);
		foreach($arr as $k=>$v){
			$json_arr=  json_decode(stripslashes(html_entity_decode($v)),true);
			$new[$json_arr['order_id']][]=$json_arr;

		}
		foreach($new as $k1=>$v1){
			foreach($v1 as $k2=>$v2){
				if($k1==$v2['order_id']){
					$new2[$k1]['price']+=$v2['price']*$v2['num'];
					//$new2[$k1]['num']+=$v2['num'];
				}
			}
		}
		//echo "<pre>";print_r($new);print_r($new2);exit;
		//echo "<pre>";print_r($_POST);exit;
		//订单商品列表
		$order_list=Model('member')->getPreOrderList($con);
		if($order_list){
			foreach($order_list as $k1=>$v1){
				foreach($new2 as $k2=>$v2){
					if($k1==$k2){
						$order_list[$k1]['goods_amount']=$v2['price'];
						foreach($v1['goods_list'] as $k3=>$v3 ){
							$order_list[$k1]['shipping_fee_arr'][]=$v3['goods_freight'];
						}
						$order_list[$k1]['shipping_fee']=max($order_list[$k1]['shipping_fee_arr']);//取子订单中商品最大运费作为子订单运费

					}

				}
			}
		}
		//echo "<pre>";print_r($order_list);echo count($order_list);exit;
		$result = $model_buy->buyPreStep2($_POST,$order_list, $_SESSION['member_id'], $_SESSION['member_name'], $_SESSION['member_email']);
		if(!empty($result['error'])) {
			showMessage($result['error'], '', 'html', 'error');
		}
		$tmp_return = jsonReturn();
		//$tmp_return['resultText']['url'] = urlShop('buy', 'pay', array('pay_sn' => $result['pay_sn']));
		//exit(json_encode($tmp_return));
		header('location:'.urlShop('buy', 'pay', array('pay_sn' => $result['pay_sn'])));
	}

	/**
	 * 下单时支付页面
	 */
	public function payOp() {
		$pay_sn	= $_GET['pay_sn'];
		if (!preg_match('/^\d{18}$/',$pay_sn)){
			showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
		}

		//查询支付单信息
		$model_order= Model('order');
		$pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']));
		if(empty($pay_info)){
			showMessage(Language::get('cart_order_pay_not_exists'),'','html','error');
		}
		Tpl::output('pay_info',$pay_info);
		//取子订单列表
		$condition = array();
		$condition['pay_sn'] = $pay_sn;
		$condition['order_state'] = array('in',array(ORDER_STATE_NEW,ORDER_STATE_PAY));
		$order_list = $model_order->getOrderList($condition,'','order_id,order_state,payment_code,order_amount,goods_amount,shipping_fee,pd_amount,order_sn,order_amount_delta,order_type,period');
		if (empty($order_list)) {
			showMessage('未找到需要支付的订单','index.php?act=member_order','html','error');
		}

		//重新计算在线支付金额
		$pay_amount_online = 0;
		$pay_amount_offline = 0;
		$pay_amount_easypay = 0;
		//订单总支付金额(不包含货到付款)
		$pay_amount = 0;

		//判断是否需要进入线上支付的流程

		$go_online = false;
		$easypay_order = false;
		foreach ($order_list as $key => $order_info) {
			//计算相关支付金额
			if ($order_info['order_type'] == ORDER_TYPE_EASYPAY) {
				$easypay_order = true;
				$pay_amount_easypay += floatval($order_info['order_amount_delta']);
				$pay_amount += floatval($order_info['order_amount_delta']);
				$pay_amount_online += ncPriceFormat(floatval($order_info['order_amount'])-floatval($order_info['pd_amount'])-floatval($order_info['order_amount_delta']));
			}else if ($order_info['payment_code'] == 'offline') {
				//线下支付
				$pay_amount_offline += floatval($order_info['order_amount']);
			} else {
				//订单走线上支付流程
				if ($order_info['order_state'] == ORDER_STATE_NEW) {
					//订单总金额 - 预付款金额 - 分期购金额
					$pay_amount_online += ncPriceFormat(floatval($order_info['order_amount'])-floatval($order_info['pd_amount'])-floatval($order_info['order_amount_delta']));
				}
				$pay_amount += floatval($order_info['order_amount']);
			}

			//显示支付方式与支付结果
			if ($order_info['payment_code'] == 'easypay') {
				$order_list[$key]['payment_state'] = '分期购';
				if(0== ncPriceFormat(floatval($order_info['order_amount'])-floatval($order_info['pd_amount'])-floatval($order_info['order_amount_delta']))){
					//完全支付
					$this->_fabiao($order_info);
				}else{
					//需要在线支付
				}
			}else if ($order_info['payment_code'] == 'offline') {
				$order_list[$key]['payment_state'] = '货到付款';
				if(0== ncPriceFormat(floatval($order_info['order_amount'])-floatval($order_info['pd_amount'])-floatval($order_info['order_amount_delta']))){
					//完全支付
					$this->_fabiao($order_info);
				}else{
					//需要在线支付
				}

			}else if ($order_info['payment_code'] == 'predeposit') {
				//
				$order_list[$key]['payment_state'] = '预存款支付';
				if(0== ncPriceFormat(floatval($order_info['order_amount'])-floatval($order_info['pd_amount'])-floatval($order_info['order_amount_delta']))){
					//完全支付
					$this->_fabiao($order_info);
				}else{
					//需要在线支付
				}
			} else {
				$order_list[$key]['payment_state'] = '在线支付';
				if (floatval($order_info['pd_amount']) > 0) {
					if ($order_info['order_state'] == ORDER_STATE_PAY) {
						$order_list[$key]['payment_state'] .= " ( 已使用预存款完全支付，支付金额 ￥ {$order_info['pd_amount']} )";
						//订单使用预付款完全支付，判断订单是否是分期购订单，如果是①发标 ②设置订单状态为 卖家处理中(50)
						if($order_info['order_type'] != ORDER_TYPE_EASYPAY)
							break;
						$this->_fabiao($order_info);

					} else {
						$order_list[$key]['payment_state'] .= " ( 已使用预存款部分支付，支付金额 ￥ {$order_info['pd_amount']} )";
					}
				}
			}
		}
		Tpl::output('order_list',$order_list);

		//如果线上线下支付金额都为0，转到支付成功页
		if (empty($pay_amount_online) && empty($pay_amount_offline)) {
			if($easypay_order){
				redirect('index.php?act=buy&op=easypay_order_complete&pay_sn='.$pay_sn.'&pay_amount='.ncPriceFormat($pay_amount));
			}else{
				redirect('index.php?act=buy&op=pay_ok&pay_sn='.$pay_sn.'&pay_amount='.ncPriceFormat($pay_amount));
			}
		}

		//输入订单描述
		if (empty($pay_amount_online)) {
			$order_remind = '下单成功，我们会尽快为您发货，请保持电话畅通！';
		} elseif (empty($pay_amount_offline)) {
			$order_remind = '请您及时付款，以便订单尽快处理！';
		} else {
			$order_remind = '部分商品需要在线支付，请尽快付款！';
		}
		Tpl::output('order_remind',$order_remind);
		Tpl::output('pay_amount_online',ncPriceFormat($pay_amount_online));
		Tpl::output('pd_amount',ncPriceFormat($pd_amount));

		//显示支付接口列表
		//兼容分期购，需要区分对待订单

		if ($pay_amount_online > 0) {
			$model_payment = Model('payment');
			$condition = array();
			$payment_list = $model_payment->getPaymentOpenList($condition);
			if (!empty($payment_list)) {
				unset($payment_list['predeposit']);
				unset($payment_list['offline']);
			}
			if (empty($payment_list)) {
				showMessage('暂未找到合适的支付方式','index.php?act=member_order','html','error');
			}
			Tpl::output('payment_list',$payment_list);
		}

		//标识 购买流程执行第几步
		Tpl::output('buy_step','step3');
		Tpl::showpage('buy_step2');
	}

	private function _fabiao($order_info){
		if(is_null($order_info['order_id']))
			return false;
		if(is_null($order_info['order_type'])||($order_info['order_type'] != ORDER_TYPE_EASYPAY))
			return false;

		try{
			Model('payment')->fabiao($order_info['order_id']);
		}catch(Exception $e){

			//发标失败，记录日志
			//设置订单状态为异常
		}
	}

	/**
	 * 预存款充值下单时支付页面
	 */
	public function pd_payOp() {
		$pay_sn	= $_GET['pay_sn'];
		if (!preg_match('/^\d{18}$/',$pay_sn)){
			showMessage(Language::get('para_error'),'index.php?act=predeposit','html','error');
		}

		//查询支付单信息
		$model_order= Model('predeposit');
		$pd_info = $model_order->getPdRechargeInfo(array('pdr_sn'=>$pay_sn,'pdr_member_id'=>$_SESSION['member_id']));
		if(empty($pd_info)){
			showMessage(Language::get('para_error'),'','html','error');
		}
		if (intval($pd_info['pdr_payment_state'])) {
			showMessage('您的订单已经支付，请勿重复支付','index.php?act=predeposit','html','error');
		}
		Tpl::output('pdr_info',$pd_info);

		//显示支付接口列表
		$model_payment = Model('payment');
		$condition = array();
		$condition['payment_code'] = array('not in',array('offline','predeposit'));
		$condition['payment_state'] = 1;
		$payment_list = $model_payment->getPaymentList($condition);
		Tpl::output('payment_list',$payment_list);

		//标识 购买流程执行第几步
		Tpl::output('buy_step','step3');
		Tpl::showpage('predeposit_pay');
	}

	/**
	 * 预存款充值下单时支付页面
	 */
	public function submit_store_fundOp() {
		$pay_sn = $_GET['pay_sn'];
		if (!preg_match('/^\d{18}$/',$pay_sn)){
			showMessage(Language::get('para_error'),'index.php?act=predeposit','html','error');
		}

		//查询支付单信息
		$model_order= Model('store_fund');
		$pd_info = $model_order->getPdRechargeInfo(array('store_fund_sn'=>$pay_sn,'store_fund_member_id'=>$_SESSION['member_id']));
		if(empty($pd_info)){
			showMessage(Language::get('para_error'),'','html','error');
		}
		if (intval($pd_info['store_fund_payment_state'])) {
			showMessage('您的订单已经支付，请勿重复支付','index.php?act=predeposit','html','error');
		}
		Tpl::output('pdr_info',$pd_info);

		//显示支付接口列表
		$model_payment = Model('payment');
		$condition = array();
		$condition['payment_code'] = array('not in',array('offline','predeposit'));
		$condition['payment_state'] = 1;
		$payment_list = $model_payment->getPaymentList($condition);
		Tpl::output('payment_list',$payment_list);

		//标识 购买流程执行第几步
		Tpl::output('buy_step','step3');
		Tpl::showpage('store_fund');
	}

	/**
	 * 微信支付页面
	 */
	public function wxpay(){
		$param = I('param');
		$param = unserialize(base64_decode($param));

		$order_model = D('Order');

		$buyer_id = $param['order_type'] == 'factory_buy' || $param['order_type'] == ORDER_TYPE_FACTORY ? session('store_id') : session('member_id');

		$orderinfo = $order_model->getOrderInfo(array('pay_sn' => $param['pay_sn'], 'buyer_id' => $buyer_id), array('order_goods'));

		$orderinfo['goods_name'] = $orderinfo['extend_order_goods'][0]['goods_name'];
		$orderinfo['wxqrcode'] = $_SESSION['product_buy_wxpay_url'][$param['pay_sn']];
		$orderinfo['pay_amount'] = $param['pay_amount'];
		$orderinfo['pay_sn'] = $param['pay_sn'];

		$this->assign('orderinfo', $orderinfo);
		$this->assign('order_type', $param['order_type']);
		$this->assign('buy_step', 'step3');
		$this->display('buy_wxpay');
	}

	/**
	 * 支付成功页面
	 */
	public function payOk() {
		$pay_sn	= $_GET['pay_sn'];
		// if (!preg_match('/^\d{18}$/',$pay_sn)){
		// 	showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
		// }

		//查询支付单信息
		$model_order= Model('order');
		//$pay_info = $model_order->getOrderPayInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']));
		//cai
		$pay_info = $model_order->getOrderInfo(array('pay_sn'=>$pay_sn,'buyer_id'=>$_SESSION['member_id']));
		// if(empty($pay_info)){
		// 	showMessage(Language::get('cart_order_pay_not_exists'),'index.php?act=member_order','html','error');
		// }
		$this->assign('pay_info',$pay_info);
		$this->assign('buy_step','step4');
		$this->display();
	}

	/**
	 * 加载买家收货地址
	 *
	 */
	public function load_addrOp() {
		$model_addr = Model('address');
		//如果传入ID，先删除再查询
		if (!empty($_GET['id']) && intval($_GET['id']) > 0) {
			$model_addr->delAddress(array('address_id'=>intval($_GET['id']),'member_id'=>$_SESSION['member_id']));
		}
		$list = $model_addr->getAddressList(array('member_id'=>$_SESSION['member_id']));
		$city=$this->_getCity();
		foreach ($list as $k=>$v){
			$list[$k]['province_id']=$city[$v['city_id']];
		}
		Tpl::output('address_list',$list);
		Tpl::showpage('buy_address.load','null_layout');
	}
	/**
	 * 返回 市ID => 省ID 对应关系数组
	 *
	 * @return array
	 */
	private function _getCity()
	{
		return array(36 => 1, 39 => 9, 40 => 2, 62 => 22, 73 => 3, 74 => 3, 75 => 3, 76 => 3, 77 => 3, 78 => 3, 79 => 3, 80 => 3, 81 => 3, 82 => 3, 83 => 3, 84 => 4, 85 => 4, 86 => 4, 87 => 4, 88 => 4, 89 => 4, 90 => 4, 91 => 4, 92 => 4, 93 => 4, 94 => 4, 95 => 5, 96 => 5, 97 => 5, 98 => 5, 99 => 5, 100 => 5, 101 => 5, 102 => 5, 103 => 5, 104 => 5, 105 => 5, 106 => 5, 107 => 6, 108 => 6, 109 => 6, 110 => 6, 111 => 6, 112 => 6, 113 => 6, 114 => 6, 115 => 6, 116 => 6, 117 => 6, 118 => 6, 119 => 6, 120 => 6, 121 => 7, 122 => 7, 123 => 7, 124 => 7, 125 => 7, 126 => 7, 127 => 7, 128 => 7, 129 => 7, 130 => 8, 131 => 8, 132 => 8, 133 => 8, 134 => 8, 135 => 8, 136 => 8, 137 => 8, 138 => 8, 139 => 8, 140 => 8, 141 => 8, 142 => 8, 162 => 10, 163 => 10, 164 => 10, 165 => 10, 166 => 10, 167 => 10, 168 => 10, 169 => 10, 170 => 10, 171 => 10, 172 => 10, 173 => 10, 174 => 10, 175 => 11, 176 => 11, 177 => 11, 178 => 11, 179 => 11, 180 => 11, 181 => 11, 182 => 11, 183 => 11, 184 => 11, 185 => 11, 186 => 12, 187 => 12, 188 => 12, 189 => 12, 190 => 12, 191 => 12, 192 => 12, 193 => 12, 194 => 12, 195 => 12, 196 => 12, 197 => 12, 198 => 12, 199 => 12, 200 => 12, 201 => 12, 202 => 12, 203 => 13, 204 => 13, 205 => 13, 206 => 13, 207 => 13, 208 => 13, 209 => 13, 210 => 13, 211 => 13, 212 => 14, 213 => 14, 214 => 14, 215 => 14, 216 => 14, 217 => 14, 218 => 14, 219 => 14, 220 => 14, 221 => 14, 222 => 14, 223 => 15, 224 => 15, 225 => 15, 226 => 15, 227 => 15, 228 => 15, 229 => 15, 230 => 15, 231 => 15, 232 => 15, 233 => 15, 234 => 15, 235 => 15, 236 => 15, 237 => 15, 238 => 15, 239 => 15, 240 => 16, 241 => 16, 242 => 16, 243 => 16, 244 => 16, 245 => 16, 246 => 16, 247 => 16, 248 => 16, 249 => 16, 250 => 16, 251 => 16, 252 => 16, 253 => 16, 254 => 16, 255 => 16, 256 => 16, 257 => 16, 258 => 17, 259 => 17, 260 => 17, 261 => 17, 262 => 17, 263 => 17, 264 => 17, 265 => 17, 266 => 17, 267 => 17, 268 => 17, 269 => 17, 270 => 17, 271 => 17, 272 => 17, 273 => 17, 274 => 17, 275 => 18, 276 => 18, 277 => 18, 278 => 18, 279 => 18, 280 => 18, 281 => 18, 282 => 18, 283 => 18, 284 => 18, 285 => 18, 286 => 18, 287 => 18, 288 => 18, 289 => 19, 290 => 19, 291 => 19, 292 => 19, 293 => 19, 294 => 19, 295 => 19, 296 => 19, 297 => 19, 298 => 19, 299 => 19, 300 => 19, 301 => 19, 302 => 19, 303 => 19, 304 => 19, 305 => 19, 306 => 19, 307 => 19, 308 => 19, 309 => 19, 310 => 20, 311 => 20, 312 => 20, 313 => 20, 314 => 20, 315 => 20, 316 => 20, 317 => 20, 318 => 20, 319 => 20, 320 => 20, 321 => 20, 322 => 20, 323 => 20, 324 => 21, 325 => 21, 326 => 21, 327 => 21, 328 => 21, 329 => 21, 330 => 21, 331 => 21, 332 => 21, 333 => 21, 334 => 21, 335 => 21, 336 => 21, 337 => 21, 338 => 21, 339 => 21, 340 => 21, 341 => 21, 342 => 21, 343 => 21, 344 => 21, 385 => 23, 386 => 23, 387 => 23, 388 => 23, 389 => 23, 390 => 23, 391 => 23, 392 => 23, 393 => 23, 394 => 23, 395 => 23, 396 => 23, 397 => 23, 398 => 23, 399 => 23, 400 => 23, 401 => 23, 402 => 23, 403 => 23, 404 => 23, 405 => 23, 406 => 24, 407 => 24, 408 => 24, 409 => 24, 410 => 24, 411 => 24, 412 => 24, 413 => 24, 414 => 24, 415 => 25, 416 => 25, 417 => 25, 418 => 25, 419 => 25, 420 => 25, 421 => 25, 422 => 25, 423 => 25, 424 => 25, 425 => 25, 426 => 25, 427 => 25, 428 => 25, 429 => 25, 430 => 25, 431 => 26, 432 => 26, 433 => 26, 434 => 26, 435 => 26, 436 => 26, 437 => 26, 438 => 27, 439 => 27, 440 => 27, 441 => 27, 442 => 27, 443 => 27, 444 => 27, 445 => 27, 446 => 27, 447 => 27, 448 => 28, 449 => 28, 450 => 28, 451 => 28, 452 => 28, 453 => 28, 454 => 28, 455 => 28, 456 => 28, 457 => 28, 458 => 28, 459 => 28, 460 => 28, 461 => 28, 462 => 29, 463 => 29, 464 => 29, 465 => 29, 466 => 29, 467 => 29, 468 => 29, 469 => 29, 470 => 30, 471 => 30, 472 => 30, 473 => 30, 474 => 30, 475 => 31, 476 => 31, 477 => 31, 478 => 31, 479 => 31, 480 => 31, 481 => 31, 482 => 31, 483 => 31, 484 => 31, 485 => 31, 486 => 31, 487 => 31, 488 => 31, 489 => 31, 490 => 31, 491 => 31, 492 => 31, 493 => 32, 494 => 32, 495 => 32, 496 => 32, 497 => 32, 498 => 32, 499 => 32, 500 => 32, 501 => 32, 502 => 32, 503 => 32, 504 => 32, 505 => 32, 506 => 32, 507 => 32, 508 => 32, 509 => 32, 510 => 32, 511 => 32, 512 => 32, 513 => 32, 514 => 32, 515 => 32, 516 => 33, 517 => 33, 518 => 33, 519 => 33, 520 => 33, 521 => 33, 522 => 33, 523 => 33, 524 => 33, 525 => 33, 526 => 33, 527 => 33, 528 => 33, 529 => 33, 530 => 33, 531 => 33, 532 => 33, 533 => 33, 534 => 34, 45055 => 35);
	}
	/**
	 * 选择不同地区时，异步处理并返回每个店铺总运费以及本地区是否能使用货到付款
	 * 如果店铺统一设置了满免运费规则，则运费模板无效
	 * 如果店铺未设置满免规则，且使用运费模板，按运费模板计算，如果其中有商品使用相同的运费模板，则两种商品数量相加后再应用该运费模板计算（即作为一种商品算运费）
	 * 如果未找到运费模板，按免运费处理
	 * 如果没有使用运费模板，商品运费按快递价格计算，运费不随购买数量增加
	 */
	public function change_addrOp() {
		//区分2种情况，购物车购买和立即购买，二者重算运费过程中查询当前运送方式的位置不同需要做区分
		//购物车直接从数据库查询
		//立即购买需要传入参数
		$if_cart = $_POST['if_cart'];
		if($if_cart){
			//购物车
			$this->_change_addr_db();
		}else{
			//立即购买
			$this->_change_addr_buynow();
		}

	}

	private function  _change_addr_db(){
		$model_buy = Model('buy');
		$data = $model_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $_SESSION['member_id']);
		if(!empty($data)) {
			exit(json_encode($data));
		} else {
			exit();
		}
	}

	private function _change_addr_buynow(){
		$model_buy = Model('buy');

		$data = $model_buy->changeAddr($_POST['freight_hash'], $_POST['city_id'], $_POST['area_id'], $_SESSION['member_id'], true);
		if(!empty($data)) {
			exit(json_encode($data));
		} else {
			exit();
		}
	}

	/**
	 * 解密
	 * @param string $string
	 * @param int $member_id
	 * @param number $ttl
	 */
	public function buyDecrypt($string, $member_id, $ttl = 0) {
		$buy_key = sha1(md5($member_id.'&'.MD5_KEY));
		if (empty($string)) return;
		$string = base64_decode(decrypt(strval($string), $buy_key, $ttl));
		return ($tmp = @unserialize($string)) ? $tmp : $string;
	}

	/**
	 * 修改商品的默认运送方式
	 * 1 物流 2 快递 3 其它
	 *
	 * ps:更改发货方式分2中情况
	 * 1.从购物车进入结算页面,直接修改数据库
	 * 2.点击立即购买，直接返回，在页面form表单中
	 * 记录发货方式
	 *
	 * 2中情况的区分是通过if_cart字段来区分的
	 * @return [type] [description]
	 */
	public function change_transport_typeOp(){
		$return = jsonReturn();
		$if_cart = $_POST['if_cart'];
		if($if_cart){
			//修改购物车
			$this->_change_transport_typedb();
		}else{
			//不做修改，原路返回
			exit(json_encode($return));
		}

	}

	/**
	 * 修改购物车运送方式
	 * @return [type] [description]
	 */
	private  function _change_transport_typedb(){
		$return = jsonReturn();

		//接收参数
		$freight_hash = $_POST["freight_hash"];
		$cart_id      = $_POST['cart_id'];

		//用户未登录
		$member_id = $_SESSION['member_id'];
		if(is_null($member_id)){
			$return['code'] = 0;
			$return['resultText']['message'] = "未登录";
			exit(json_encode($return));
		}

		//查询购物车
		$condition['buyer_id'] = $member_id;
		$condition['cart_id'] = $cart_id;
		$cart_record = Model('cart')->find($cart_id);
		if(is_null($cart_record)){
			$return['code'] = 0;
			$return['resultText']['message'] = "购物车中没有此商品";
			exit(json_encode($return));
		}

		//更新购物车
		//只有store_id 在freight_list 划分为需要重算运费的，才有必要重算
		$freight_list = $this->buyDecrypt($freight_hash, $member_id);
		$neeed_calc = $freight_list['nocalced'];
		if(!in_array($cart_record['store_id'], array_keys($neeed_calc))){
			$return['resultText']['message'] = "成功，商品无需重算，原路返回";
			exit(json_encode($return));
		}

		//更新数据库
		$cart_record['transport_type'] = $_POST['transporttype'];
		$result = Model('cart')->update($cart_record, array('cart_id'=>$cart_id));
		if($result){
			$return['resultText']['message'] = "更新成功，购物车已更新";
			exit(json_encode($return));
		}else{
			$return['code'] = 0;
			$return['resultText']['message'] = "失败，购物车写入错误";
			exit(json_encode($return));
		}

		$return['resultText']['message'] = "执行成功";
		exit(json_encode($return));
	}

	/**
	 * 添加新的收货地址
	 *
	 */
	public function add_addrOp(){
		$model_addr = Model('address');
		if (chksubmit()){
			//验证表单信息
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
				array("input"=>$_POST["true_name"],"require"=>"true","message"=>Language::get('cart_step1_input_receiver')),
				array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>Language::get('cart_step1_choose_area')),
				array("input"=>$_POST["address"],"require"=>"true","message"=>Language::get('cart_step1_input_address'))
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				$error = strtoupper(CHARSET) == 'GBK' ? Language::getUTF8($error) : $error;
				exit(json_encode(array('state'=>false,'msg'=>$error)));
			}
			$data = array();
			$data['member_id'] = $_SESSION['member_id'];
			$data['true_name'] = $_POST['true_name'];
			$data['area_id'] = intval($_POST['area_id']);
			$data['city_id'] = intval($_POST['city_id']);
			$data['area_info'] = $_POST['area_info'];
			$data['address'] = $_POST['address'];
			$data['tel_phone'] = $_POST['tel_phone'];
			$data['mob_phone'] = $_POST['mob_phone'];
			//转码
			$data = strtoupper(CHARSET) == 'GBK' ? Language::getGBK($data) : $data;
			$insert_id = $model_addr->addAddress($data);
			if ($insert_id){
				exit(json_encode(array('state'=>true,'addr_id'=>$insert_id)));
			}else {
				exit(json_encode(array('state'=>false,'msg'=>Language::get('cart_step1_addaddress_fail','UTF-8'))));
			}
		} else {
			Tpl::showpage('buy_address.add','null_layout');
		}
	}

	/**
	 * 修改收货地址
	 *
	 */
	public function edit_addrOp(){
		$model_addr = Model('address');
		if (chksubmit()){
			//验证表单信息
			$obj_validate = new Validate();
			$obj_validate->validateparam = array(
				array("input"=>$_POST["true_name"],"require"=>"true","message"=>Language::get('cart_step1_input_receiver')),
				array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>Language::get('cart_step1_choose_area')),
				array("input"=>$_POST["address"],"require"=>"true","message"=>Language::get('cart_step1_input_address'))
			);
			$error = $obj_validate->validate();
			if ($error != ''){
				$error = strtoupper(CHARSET) == 'GBK' ? Language::getUTF8($error) : $error;
				exit(json_encode(array('state'=>false,'msg'=>$error)));
			}
			$data = array();
			$data['true_name'] = $_POST['true_name'];
			$data['area_id'] = intval($_POST['area_id']);
			$data['city_id'] = intval($_POST['city_id']);
			$data['area_info'] = $_POST['area_info'];
			$data['address'] = $_POST['address'];
			$data['tel_phone'] = $_POST['tel_phone'];
			$data['mob_phone'] = $_POST['phone'];
			//转码
			$data = strtoupper(CHARSET) == 'GBK' ? Language::getGBK($data) : $data;
			$w_condition['address_id']=$_POST['address_id'];
			$w_condition['member_id']=$_SESSION['member_id'];

			$update_id = $model_addr->editAddress($data,$w_condition);
			if ($update_id){
				exit(json_encode(array('state'=>true,'addr_id'=>$_POST['address_id'])));
			}else {
				exit(json_encode(array('state'=>false,'msg'=>Language::get('cart_step1_addaddress_fail','UTF-8'))));
			}
		} else {
			Tpl::showpage('buy_address.add.new','null_layout');
		}
	}

	/**
	 * AJAX验证登录密码
	 */
	public function check_pd_pwdOp(){
		if (empty($_GET['password'])) exit('0');
		$buyer_info	= Model('member')->infoMember(array('member_id' => $_SESSION['member_id']));
		echo $buyer_info['member_paypwd'] === md5($_GET['password']) ? '1' : '0';
	}
}