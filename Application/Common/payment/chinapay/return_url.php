<?php
/**
 * 网银在线返回地址
 *
 * 




 */
$_GET['act']	= 'payment';
$_GET['op']		= 'return';
$_GET['payment_code'] = 'chinapay';

//赋值，方便后面合并使用支付宝验证方法
$_GET['out_trade_no'] = $_POST['orderId'];
$_GET['extra_common_param'] = $_POST['reqReserved'];
$_GET['trade_no'] = $_POST['queryId'];

require_once(dirname(__FILE__).'/../../../index.php');
?>