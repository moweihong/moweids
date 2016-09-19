<?php
/**
 * 银联支付接口类
 *
 * 




 */
require_once BASE_PATH.'/api/payment/chinapay/lib/acp_service.php';

class chinapay{
	/**
	 * 支付接口标识
	 *
	 * @var string
	 */
    private $code      = 'chinapay';
    /**
	 * 支付接口配置信息
	 *
	 * @var array
	 */
    private $payment;
     /**
	 * 订单信息
	 *
	 * @var array
	 */
    private $order;
    /**
     * 发送至网银在线的参数
     *
     * @var array
     */
    private $parameter;
    /**
     * 订单类型 product_buy商品购买,predeposit预存款充值
     * @var unknown
     */
    private $order_type;
    /**
     * 支付状态
     * @var unknown
     */
    private $pay_result;
    
    public function __construct($payment_info,$order_info){
    	$this->chinapay($payment_info,$order_info);
    }
    public function chinapay($payment_info = array(),$order_info = array()){
    	if(!empty($payment_info) and !empty($order_info)){
    		$this->payment	= $payment_info;
    		$this->order	= $order_info;
    	}
    }
	/**
	 * 支付表单
	 *
	 */
	public function submit(){

		$merId = $this->payment['payment_config']["chinapay_account"];			//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
		$orderId = $this->order['pay_sn'];		//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
		$txnTime = date('YmdHis');		//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
		$txnAmt = $this->order['pay_amount'];		//交易金额，单位分，此处默认取demo演示页面传递的参数
		$reqReserved =$this->order['order_type'];        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据

		/* 交易参数 */
		$params = array(
		
				//以下信息非特殊情况不需要改动
				'version' => '5.0.0',                 //版本号
				'encoding' => 'utf-8',				  //编码方式
				'txnType' => '01',				      //交易类型
				'txnSubType' => '01',				  //交易子类
				'bizType' => '000201',				  //业务类型
				'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  //前台通知地址
				'backUrl' => SDK_BACK_NOTIFY_URL,	  //后台通知地址
				'signMethod' => '01',	              //签名方法
				'channelType' => '07',	              //渠道类型，07-PC，08-手机
				'accessType' => '0',		          //接入类型
				'currencyCode' => '156',	          //交易币种，境内商户固定156
				
				//TODO 以下信息需要填写
				'merId' => $merId,					 //商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
				'orderId' => $orderId,				 //商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
				'txnTime' => $txnTime,				 //订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
                'txnAmt' => $txnAmt*100,                 //交易金额，单位分，此处默认取demo演示页面传递的参数
				'reqReserved' =>$reqReserved,        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据
			);

		AcpService::sign ( $params );
		$uri = SDK_FRONT_TRANS_URL;
		$html_form = AcpService::createAutoFormHtml( $params, $uri );
		echo $html_form;
		exit;
	}

    /**
     * 返回地址验证(同步)
     *
     * @param 
     * @return boolean
     */
    public function return_verify(){
        
        $merId = $this->payment['payment_config']["chinapay_account"];    
        $accNo     =trim($_POST['accNo']);       
        $accessType   =trim($_POST['accessType']);    
        $bizType =trim($_POST['bizType']);  
        $certId =trim($_POST['certId']);    
        $currencyCode  =trim($_POST['currencyCode']);     
        $encoding  =trim($_POST['encoding']);  
        $orderId   =trim($_POST['orderId']);      
        $queryId   =trim($_POST['queryId']);   
        $reqReserved  =trim($_POST['reqReserved']);  
        $respCode  =trim($_POST['respCode']);  
        $respMsg  =trim($_POST['respMsg']);  
        $settleAmt  =trim($_POST['settleAmt']);  
        $settleCurrencyCode  =trim($_POST['settleCurrencyCode']);  
        $settleDate  =trim($_POST['settleDate']);  
        $signMethod  =trim($_POST['signMethod']);  
        $traceNo  =trim($_POST['traceNo']);  
        $traceTime  =trim($_POST['traceTime']);  
        $txnAmt  =intval(trim($_POST['txnAmt']))/100;  // 单位为分
        $txnSubType  =trim($_POST['txnSubType']);  
        $txnTime  =trim($_POST['txnTime']);  
        $txnType  =trim($_POST['txnType']);  
        $version  =trim($_POST['version']);  
        $signature  =trim($_POST['signature']); 

        // 检查商户账号是否一致。
        if ($merId != trim($_POST['merId'])){
            log::i(' mer not equal'.'  merid = '.$merId.'  post mer ='.$_POST['merId'], 'ali');
            return false;
        }

        if($txnAmt != $this->order['pay_amount']){
            log::i(' price not equal'.'  price = '.$txnAmt.'  post price ='.$this->order['pay_amount'], 'ali');
            return false;
        }
        
        /**
         * 判断返回信息，如果支付成功，并且支付结果可信，则做进一步的处理
         */
        if($respCode=="00"){
            $this->order_type = $reqReserved;
            $this->pay_result = true;
            //支付成功，可进行逻辑处理！
            return true;
        }
    }
    
    /**
     * 返回地址验证(异步)
     * @return boolean
     */
    public function notify_verify() {
       return $this->return_verify();    
    }

    /**
     * 取得订单支付状态，成功或失败
     *
     * @param array $param
     * @return array
     */
    public function getPayResult($param){
        return $this->pay_result;
    }

    public function __get($name){
        return $this->$name;
    }
}
