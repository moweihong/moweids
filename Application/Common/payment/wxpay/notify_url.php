<?php
/**
 * 接收微信支付异步通知回调地址
 *
 * 二十四小时在线技术Q：76809326 
 *

 */
error_reporting(7);
$_GET['act']	= 'payment';
$_GET['op']		= 'notify';
$_GET['payment_code'] = 'wxpay';
require_once(dirname(__FILE__).'/../../../index.php');
