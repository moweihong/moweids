<?php
/**
 * 用户API类
 *




 */
include_once './ThinkPHP/Library/Org/Curl/CurlUtil.php';
class easypayAPI extends \Org\Curl\CurlUtil{
	public function __construct(){
		$this->config['url'] = C('javaapi'); // 接口地址
	}

	/*
	 * 获取分期购授信额度信息
	 * options
	 * @return [type] [description]
	 */
	public function get_credit_status($options){
	 	$index = __FUNCTION__;
	 	$url = C('risk_control');
	 	$result = $this->getResponse($index, $options, "GET", $url);
	 	return $result;
	}

	/*
	 * 提交分期购授信请求
	 * @param [type] $options [description]
	 */
	public function set_credit_info($options){
		$index = __FUNCTION__;
		$url = C('risk_control');
	 	$result = $this->getResponse($index, $options, "POST", $url);
	 	return $result;
	} 

	/*
	 * 提交风控审批
	 * 在提交授信资料后，如果新浪密码设置成功并成功返回，需要
	 * 调用接口提交授信申请到风控部门
	 * @param [type] $options [description]
	 */
	public function set_credit_status($options){
		$index = __FUNCTION__;
		$url = C('risk_control');
	 	$result = $this->getResponse($index, $options,"POST", $url);
	 	return $result;
	}

	/*
	 * 跳转新浪代扣接口
	 */
	public function set_sina_pay_pwd($options){
		$index = __FUNCTION__;
		$url = C('allwood_url');
	 	$result = $this->getResponse($index, $options, "POST", $url);
	 	return $result;
	}

	/*
	 * 计算商家门店下商品的分期信息
	 * @return [type] [description]
	 */
	public function get_interest_info($options){
		$index = __FUNCTION__;
		$url = C('allwood_url');
	 	$result = $this->getResponse($index, $options, "GET", $url);
	 	return $result;	
	}
    
	/*
	 * 全木行发标接口
	 * @param array $options 发标参数
	 */
	public function fabiao($options){
	    $index = __FUNCTION__;
	    $url = C('allwood_url');
	    $result = $this->getResponse($index, $options, "POST", $url);
	    return $result;
	}

	/*
	 *设置激活状态
	 */
	public function set_usr_activate($options){
		$index = __FUNCTION__;
		$url = C('risk_control');
	    $result = $this->getResponse($index, $options, "POST", $url);
	    return $result;
	}
}