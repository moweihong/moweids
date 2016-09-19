<?php
/**
 * Created by liuchen.
 * User: liuchen
 * Date: 2016/8/27 0027
 * Time: 15:41
 */
namespace Org\Util;
use Omnipay\Omnipay;

/**
 * 支付通用类
 * Class CommonPay
 * @package Org\Util
 */
class CommonPay {
	/**
	 * 类实例的静态成员变量
	 */
	private static $_instance;

	/**
	 * 支付接口名
	 * @string
	 */
	public $ExpressGateway = '';

	/**
	 * Omnipay实例
	 */
	public $gateway = false;

	/**
	 * 支付接口参数
	 */
	private $_params = array();

	/**
	 * 回调请求对象
	 */
	private $_response = false;

	/**
	 * 回调地址
	 */
	private $_notify_url = '';

	/**
	 * 跳转地址
	 */
	private $_return_url = '';

	/**
	 * 单例方法,用于访问实例的公共的静态方法
	 * @param string 支付接口名
	 */
	public static function getInstance($ExpressGateway, $type = 1){
		if(empty($ExpressGateway)){
			throw new Exception("Error: Param ExpressGateway is necessary to create a instance!");
		}

		if(!(self::$_instance instanceof self)){
			self::$_instance = new self($ExpressGateway, $type);
		}
		return self::$_instance;
	}

	/**
	 * 构造函数
	 * @param string 支付接口名
	 */
	private function __construct($ExpressGateway, $type){
		$this->ExpressGateway = $ExpressGateway;
		$this->gateway = Omnipay::create($this->ExpressGateway);  //初始化支付接口
		$this->_params = C($this->ExpressGateway);
		$url_config = C('Notify_Return_Url');
		$this->_notify_url = 'http://'. $_SERVER['HTTP_HOST'] . U($url_config['notifyUrl'], array('type' => $type, 'payment_code' => $ExpressGateway));
		$this->_return_url = 'http://'. $_SERVER['HTTP_HOST'] . U($url_config['returnUrl']);
		$this->setParam();  //设置支付接口参数
	}

	/**
	 * 设置支付接口参数
	 * @param array $params
	 */
	public function setParam(){
		switch ($this->ExpressGateway){
			case 'Alipay_Express':
				$this->setAlipayParams();
				break;
			case 'UnionPay_Express' :
				$this->setUnionParams();
				break;
			case 'WechatPay' :
				$this->setWechatParams();
				break;
			default :
				throw new Exception("Error: Param Error!");
		}
	}

	/**
	 * 支付宝支付接口参数设置
	 */
	private function setAlipayParams(){
		$this->gateway->setPartner($this->_params['partnerId']);
		$this->gateway->setKey($this->_params['apiKey']);
		$this->gateway->setSellerEmail($this->_params['sellerEmail']);
		$this->gateway->setReturnUrl($this->_return_url);
		$this->gateway->setNotifyUrl($this->_notify_url);
	}

	/**
	 * 银联支付接口参数设置
	 */
	private function setUnionParams(){
		$this->gateway->setMerId($this->_params['merId']);
		$this->gateway->setCertPath($this->_params['certPath']);
		$this->gateway->setCertPassword($this->_params['certPassword']);
		$this->gateway->setReturnUrl($this->_return_url);
		$this->gateway->setNotifyUrl($this->_notify_url);
		$this->gateway->setEnvironment('production');
	}

	/**
	 * 微信支付接口参数设置
	 */
	private function setWechatParams(){
		$this->gateway->setAppId($this->_params['appId']);
		$this->gateway->setMchId($this->_params['merId']);
		$this->gateway->setApiKey($this->_params['apiKey']);
		$this->gateway->setNotifyUrl($this->_notify_url);
		$this->gateway->setTradeType('NATIVE');
	}

	/**
	 * 发送支付请求
	 * @param array $options
	 */
	public function send($options){
		return $this->gateway->purchase($options)->send();
	}

	/**
	 * 获取回调结果对象
	 */
	public function getResponse($options){
		$this->_response = $this->gateway->completePurchase(['request_params' => $options])->send();
		return $this->_response;
	}

	/**
	 * 禁止clone
	 */
	public function __clone(){
		throw new Exception('Error: Clone is not allow!');
	}
}