<?php
namespace Home\Controller;
use Think\Controller;
use Org\Util\CommonPay;

class PayDemoController extends Controller {
	/**
	 * 银联支付demo
	 * @throws \Org\Util\Exception
	 */
	public function unionPayDemo(){
		$gateway = CommonPay::getInstance('UnionPay_Express');

		$order = [
			'orderId'   => date('YmdHis'), //Your order ID
			'txnTime'   => date('YmdHis'), //Should be format 'YmdHis'
			'orderDesc' => 'My order title', //Order Title
			'txnAmt'    => '100', //Order Total Fee
		];

		$response = $gateway->send($order);
		$response->redirect();
	}

	/**
	 * 支付宝支付demo
	 * @throws \Org\Util\Exception
	 */
	public function alipayDemo(){
		$gateway = CommonPay::getInstance('Alipay_Express');

		$order = [
			'out_trade_no' => date('YmdHis') . mt_rand(1000,9999), //your site trade no, unique
			'subject'      => 'test', //order title
			'total_fee'    => '0.01', //order total fee
		];

		$response = $gateway->send($order);
		$response->redirect();
	}

	/**
	 * 微信支付demo
	 * @throws \Org\Util\Exception
	 */
	public function wechatPayDemo(){
		$gateway = CommonPay::getInstance('WechatPay');

		$order = array (
			'body'             => 'test', //Your order ID
			'out_trade_no'     => date('YmdHis'), //Should be format 'YmdHis'
			'total_fee'        => '1', //Order Title
			'spbill_create_ip' => '114.119.110.120', //Order Total Fee
		);

		/**
		 * @var CreateOrderResponse $response
		 */
		$response = $gateway->send($order);
		var_dump($response->getCodeUrl());
	}

	/**
	 * 支付回调demo
	 */
	public function notifyDemo(){
		$gateway = CommonPay::getInstance('WechatPay');  //UnionPay_Express, Alipay_Express, WechatPay

		$response = $gateway->getResponse([
			'request_params' => file_get_contents('php://input')    //UnionPay_Express:'request_params'=>$_REQUEST  Alipay_Express:'request_params'=> array_merge($_POST, $_GET)
		]);

		if ($response->isPaid()) {
			//支付成功
			//业务逻辑
		}else{
			//支付失败
		}
	}
}