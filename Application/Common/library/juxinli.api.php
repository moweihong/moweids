<?php
/**
 * 用户API类
 *




 */
include_once './ThinkPHP/Library/Org/Curl/CurlUtil.php';
class juxinliAPI extends \Org\Curl\CurlUtil{
	public function __construct(){
		$this->config['url'] = C('juxinliapi'); // 接口地址
	}

	/**
	 * 提交聚信立资料
	 * @param $data
	 * 
	 */
	public function post_jxl_userdata($data){
		$index = __FUNCTION__;
		$url = C('juxinliapi').'tsfkxt/juxinli/submitData.do';
		$result  = $this->getResponse($index,$data,"POST",$url);
		return $result;
	}

	/**
	 * 聚信立手机、电商数据提交
	 * @param $data
	 */

	public function post_jxl_otherdata($data){
		$index = __FUNCTION__;
		$url = C('juxinliapi').'tsfkxt/juxinli/setPhoneCheck.do';
		$result = $this->getResponse($index,$data,"POST",$url);
		return $result;
	}

	/**
	 * 聚信立跳过步骤
	 * @param $data
	 */
	public function pass_jxl_step($data){
		$index = __FUNCTION__;
		$url = C('juxinliapi').'tsfkxt/juxinli/setPassCheck.do';
		$result = $this->getResponse($index,$data,"POST",$url);
		return $result;
	}

}