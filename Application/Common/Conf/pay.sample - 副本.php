<?php
/**
 * Created by liuchen.
 * User: liuchen
 * Date: 2016/8/27 0027
 * Time: 17:04
 */

return [
	//支付宝接口参数
	'Alipay_Express' => [
		'partnerId' => '2088121632880106',
		'apiKey' => '2tviib0lu9xc791t39puzeqje7p6cmw8',
		'sellerEmail' => '1958680000@qq.com',
		'returnUrl' => 'http://www.baidu.com',
		'notifyUrl' => 'http://www.baidu.com'
	],
	//银联接口参数
	'UnionPay_Express' => [
		'merId' => '898440353113449',
		'certPath' => 'D:\phpStudy\WWW\tp_wood\pay\unionpay\898440353113449.pfx',
		'certPassword' => '000000',
		'returnUrl' => 'http://www.baidu.com',
		'notifyUrl' => 'http://www.baidu.com'
	],
	//微信接口参数
	'WechatPay' => [
		'appId' => 'wxc63cd14f8efd4911',
		'merId' => '1302262601',
		'apiKey' => '33A75BD979BDC44389B9BC204A3E1428',
		'appSecret' => '38f9224689c8e9980522eda2ce3b00a8',
		'notifyUrl' => 'http://www.baidu.com',
	],
];