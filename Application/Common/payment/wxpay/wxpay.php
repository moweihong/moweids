<?php 
/**
 * 微信扫码支付
 *
 * 二十四小时在线技术Q：76809326 
 *

 */
class wxpay{

    /**
     * 存放支付订单信息
     * @var array
     */
    private $_order_info = array();
    /**
     * 支付接口标识
     *
     * @var string
     */
    private $code      = 'wxpay';
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
     * 发送至支付宝的参数
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
     * 支付信息初始化
     * @param array $payment_info
     * @param array $order_info
     */
    public function __construct($payment_info = array(), $order_info = array()) {
        define('WXN_APPID', $payment_info['payment_config']['wxpay_appid']);
        define('WXN_MCHID', $payment_info['payment_config']['wxpay_mchid']);
        define('WXN_KEY', $payment_info['payment_config']['wxpay_api_key']);
        $this->_order_info = $order_info;
    }

    /**
     * 组装包含支付信息的url(模式1)
     */
    public function get_payurls() {
        /*require_once BASE_PATH.'/api/payment/wxpay/lib/WxPay.Api.php';
        require_once BASE_PATH.'/api/payment/wxpay/WxPay.NativePay.php';
        require_once BASE_PATH.'/api/payment/wxpay/log.php';*/
        require_once COMMON_PATH.'/payment/wxpay/lib/WxPay.Api.php';
        require_once COMMON_PATH.'/payment/wxpay/lib/WxPay.Notify.php';
        require_once COMMON_PATH.'/payment/wxpay/log.php';
        $logHandler= new CLogFileHandler(BASE_DATA_PATH.'/log/wxpay/'.date('Y-m-d').'.log');
        $logwx = logwx::Init($logHandler, 15);
        $notify = new NativePay();
        return $notify->GetPrePayUrl($this->_order_info['pay_sn']);
    }

    /**
     * 组装包含支付信息的url(模式2)
     */
    public function get_payurl() {
        /*require_once BASE_PATH.'/api/payment/wxpay/lib/WxPay.Api.php';
        require_once BASE_PATH.'/api/payment/wxpay/WxPay.NativePay.php';
        require_once BASE_PATH.'/api/payment/wxpay/log.php';*/

        require_once COMMON_PATH.'/payment/wxpay/lib/WxPay.Api.php';
        require_once COMMON_PATH.'/payment/wxpay/lib/WxPay.Notify.php';
        require_once COMMON_PATH.'/payment/wxpay/log.php';

        $logHandler= new CLogFileHandler(BASE_DATA_PATH.'/log/wxpay/'.date('Y-m-d').'.log');
        $Logwx = Logwx::Init($logHandler, 15);

        //统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($this->_order_info['pay_sn'].'订单');
        $input->SetAttach($this->_order_info['order_type']);
        $input->SetOut_trade_no($this->_order_info['pay_sn']);
        $input->SetTotal_fee($this->_order_info['pay_amount']*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 3600));
        $input->SetGoods_tag('');
        //$input->SetNotify_url(SHOP_SITE_URL.'/api/payment/wxpay/notify_url.php');
        $input->SetNotify_url(COMMON_PATH.'/payment/wxpay/notify_url.php');
        $input->SetTrade_type("NATIVE");
        //$input->SetOpenid($openId);
        $input->SetProduct_id($this->_order_info['pay_sn']);
        $result = WxPayApi::unifiedOrder($input);
//         header("Content-type:text/html;charset=utf-8");
//         print_R($result);exit;
        Logwx::DEBUG("unifiedorder-:" . json_encode($result));
        return $result["code_url"];
    }

    /**
     * 通知地址验证
     *
     * @return bool
     */
    public function notify_verify() {
        /*require_once BASE_PATH.'/api/payment/wxpay/lib/WxPay.Api.php';
        require_once BASE_PATH.'/api/payment/wxpay/lib/WxPay.Notify.php';
        require_once BASE_PATH.'/api/payment/wxpay/log.php';*/

        require_once COMMON_PATH.'/payment/wxpay/lib/WxPay.Api.php';
        require_once COMMON_PATH.'/payment/wxpay/lib/WxPay.Notify.php';
        require_once COMMON_PATH.'/payment/wxpay/log.php';

        $logHandler= new CLogFileHandler(BASE_DATA_PATH.'/log/wxpay/'.date('Y-m-d').'.log');
        $Logwx = Logwx::Init($logHandler, 15);

        $notify = new WxPayNotify();
        return $notify->Handle(true);
    }

    /**
     * 返回地址验证
     *
     * @return bool
     */
    public function return_verify() {
    }

    /**
     * 
     * 取得订单支付状态，成功或失败
     * @param array $param
     * @return array
     */
    public function getPayResult($param){
        return $param['trade_status'] == 'TRADE_SUCCESS';
    }

    /**
     * 
     *
     * @param string $name
     * @return 
     */
    public function __get($name){
        return $this->$name;
    }
}
