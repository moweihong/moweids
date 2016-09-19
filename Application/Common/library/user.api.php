<?php
/**
 * 用户API类
 *




 */
include_once './ThinkPHP/Library/Org/Curl/CurlUtil.php';
class userAPI extends \Org\Curl\CurlUtil{
	public function __construct(){
		$this->config['url'] = C('javaapi'); // 接口地址
	}
	
	// 登陆接口
	public function login($login_options){
		\Think\log::lol('hello ');
		$result = $this->api("/unify_interface/user/login.do",$login_options, 'POST');
		$result = $this->checkError($result, "/unify_interface/user/login.do", C('javaapi'));
		return $result;
	}

	/**
	 * [注册接口]
	 * @return [json] [description]
	 */
	public function register($register_options){
		$result = $this->api("/unify_interface/user/register.do",$register_options,'POST');
		$result = $this->checkError($result, "/unify_interface/user/register.do", C('javaapi'));
		return $result;

	}

	/**
	 * 查询用户是否已经注册
	 * @return boolean [description]
	 * url:http://182.254.131.15:8080/unify_interface/user/isRegister.do?user_name=quanmuhang
	 */
	public function isRegister($options){
	 	$index = __FUNCTION__;
	 	$result = $this->getResponse($index, $options, 'GET');
	 	return $result;
	}


	/**
	 * 获取用户id
	 * @return [type] [description]
	 * url:http://182.254.131.15:8080/unify_interface/user/getUsrid.do?user_name=quanmuhang
	 */
	public function getUserId($options){
	 	$index = __FUNCTION__;
	 	$result = $this->getResponse($index, $options);
	 	return $result;
	}

	/**
	 * 获取用户信息
	 * @return [type] [description]
	 * url:http://182.254.131.15:8080/unify_interface/user/getUsrinf.do?usr_id=65
	 */
	public function getUserInfo($options){
		$index = __FUNCTION__;
	 	$result = $this->getResponse($index, $options);
	 	return $result;
	}

	/**
	 * 修改用户密码
	 * @return [type] [description]
	 * 
	 */
	public function changePassword($options){
		$index = __FUNCTION__;
	 	$result = $this->getResponse($index, $options, 'POST');
	 	return $result;
	}

	/**
	 * 修改手机号码
	 * 
	 * @return [type] [description]
	 */
	public function changeMobile($options){
		$index = __FUNCTION__;
	 	$result = $this->getResponse($index, $options);
	 	return $result;
	}

	/**
	 * 修改邮箱
	 * @return [type] [description]
	 */
	public function changeEmail($options){
		$index = __FUNCTION__;
	 	$result = $this->getResponse($index, $options);
	 	return $result;
	}



	/**
	 * 查询用户分期购额度
	 * @param  array $arrayUserid
	 * $arr["UsrId"]
	 * @return [json]  {UsrId:"",Loan_limit:"",Loan_useble:""}
	 */
	public function getLimit($arrUserid){
		return $this->returnFalse();

		$result = $this->api("/allwood_finance/installment/getlimit.do",$arrUserid);
		$result = $this->checkError($result, "/allwood_finance/installment/getlimit.do", C('javaapi'));
		return $result;
	}

	/**
	 * 分期购额度申请接口
	 * 
	 * @return [type] [description]
	 */
	public function applyForLimit($options){
		return $this->returnFalse();


		$data['usinf'] = json_encode($options);
		$result = $this->api("/allwood_finance/installment/applyforlimit.do",$data);
		$result = $this->checkError($result, "/allwood_finance/installment/applyforlimit.do", C('javaapi'));
		return $result;
	}

	/**
	 * 提升额度接口
	 * @param  [type] $options [description]
	 * @return [boolean]
	 * true  操作成功
	 * false 操作失败
	 */
	public function elevatelimit($options){
		return $this->returnFalse();


		$data['usinf'] = json_encode($options);
		$result = $this->api("/allwood_finance/installment/elevatelimit.do", $data);
		$result = $this->checkError($result, "/allwood_finance/installment/elevatelimit.do", C('javaapi'));
		return $result;
	}

	/**
	 * 投资购买
	 * @param  [type] $options [description]
	 * @return [type]          [description]
	 */
	public function investpay($options){
		$result = $this->api("/allwood_finance/installment/setInvestBuy.do", $options);
		$result = $this->checkError($result, "/allwood_finance/installment/setInvestBuy.do", C('javaapi'));
		return $result;

	}
	/**
	 * 查询接口
	 * 
	 * @return [type] [description]
	 */
	public function query(){
		return null;
	}


	
	/**
	 * 投资购买
	 * 
	 * @return [boolean]
	 * true 	处理成功
	 * false 	处理失败
	 */
	public function applyForLoan($options){
        $data['applyforloaninfo'] = json_encode($options);
		$result =  $this->api("/allwood_finance/installment/applyforloan.do", $data);
		$result = $this->checkError($result, "/allwood_finance/installment/applyforloan.do", C('javaapi'));
		return $result;
	}

	/**
	 * 申请记录查询
	 * @return [type] [description]
	 * type 0 all
	 * type 1 passed
	 * type 2 failed
	 */
	private function applyHistory($options, $type){
		$options['is_pass'] = $type;
		$result = $this->api("/allwood_finance/installment/getapplyforrecord.do", $options);
		$result = $this->checkError($result, "/allwood_finance/installment/getapplyforrecord.do", C('javaapi'));
		return $result;
	}

	/**
	 * 获取所有申请记录
	 * @param  [type] $options [description]
	 * @return [type]          [description]
	 */
	function applyHistoryAll($options){
		return $this->applyHistory($options, 0);
	}

	/*
	 * 获取申请成功记录
	 * @param  [type] $options [description]
	 * @return [type]          [description]
	 */
	function applyHistoryPassed($options){
		return $this->applyHistory($options, 1);
	}

	/**
	 * 获取申请失败记录
	 * @param  [type] $options [description]
	 * @return [type]          [description]
	 */
	function applyHistoryFailed($options){
		return $this->applyHistory($options, 2);
	}

	/**
	 * 获取历史额度信息
	 * @param  [type] $options [description]
	 * @return [type]          [description]
	 */
	function getCreditRecord($options){
		return $this->returnFalse();
		
		$result = $this->api("/allwood_finance/installment/gethistoricallimitrecord.do", $options);
		$result = $this->checkError($result, "/allwood_finance/installment/gethistoricallimitrecord.do", C('javaapi'));
		return $result;
	}

	/**
	 * 获得上级关系
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
    public function getGetRecommend($id){
        $options["usr_id"]=$id;
        $options["platform_source"]=2;
        $result = $this->api("/unify_interface/user/getRecommend.do", $options);
        return $result;
    }


}