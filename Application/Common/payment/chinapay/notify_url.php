<?php
/**
 * 网银在线自动对账文件
 *
 * 




 */
$_GET['act']	= 'payment';
$_GET['op']		= 'notify';
$_GET['payment_code'] = 'chinapay';

//赋值，方便后面合并使用支付宝验证方法
$_POST['out_trade_no'] = $_POST['traceNo'];
$_POST['extra_common_param'] = $_POST['reqReserved'];
$_POST['trade_no'] = $_POST['queryId'];

require_once(dirname(__FILE__).'/../../../index.php');
?>