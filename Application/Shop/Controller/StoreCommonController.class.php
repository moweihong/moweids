<?php
namespace Shop\Controller;
use Think\Controller;
use Org\Util\CommonPay;
class StoreCommonController extends Controller {
    /*
     * 检查用户是否登录,如果没有，跳转前台登录页面
     */
    protected $store_id;
    //店铺信息
    protected $store_info = array();
    
    public function __construct() {
        parent::__construct();
        //没有登录，跳转到登录界面
        if ($_SESSION['is_login'] !== '1'){
            $this->redirect('login/index');
        }else{
            if(is_null($_SESSION['store_id'])){
                $this->redirect('StartBusiness/index');
            }
        }
        // 验证店铺是否存在
        $model_store = D('Store');
        $this->store_info = $model_store->getStoreInfoByID($_SESSION['store_id']);
        if (empty($this->store_info)) {
            $this->error('店铺不存在');
        }
   }


    public function index(){
        //店铺信息
        if($_SESSION['com_type'] == 1){
            //经销商
            $this->redirect('Vendor/index');
        }else if($_SESSION['com_type'] == 2){
            //装修公司
            $this->redirect('Decorate/index');
        }else if($_SESSION['com_type'] == 3){
            //工厂
            $this->redirect('Factory/index');
        }else{
            $this->redirect('Vendor/index');
        }
    }
    /*
     * 基本信息设置
     */
    public function profile(){
        //echo json_encode($_SESSION);
        //die;
        //主营类目
        $store_major_business = M('StoreMajorBusiness');
        $condition = $_SESSION['store_id'];
        $major = $store_major_business->where($condition)->select();
        $store_model = M();

        //店铺信息
        $field = 'store.*,store_joinin.major_business, store_joinin.com_type, store_joinin.contacts_phone, store_joinin.company_address_detail,store_joinin.province_id, store_joinin.city_id, store_joinin.area_id2';
        $join = 'LEFT JOIN allwood_store_joinin as store_joinin on store.member_id=store_joinin.member_id';
        $store_record = $store_model->table('allwood_store as store')->join($join)->where(array('store.member_id' => $_SESSION['member_id']))->field($field)->select();
        $store_record = $store_record[0];
        $store_record['store_open'] = date('Y-m-d H:i:s', $store_record['store_time']);
        $pos = strpos($store_record['company_phone'], "-");
        if ($pos === false) {
            $store_record['tel'] = $store_record['company_phone'];
        } else {
            $store_record['area_code'] = substr($store_record['company_phone'], 0, $pos);
            $store_record['tel'] = substr($store_record['company_phone'], $pos + 1);
        }

        $ma = unserialize($store_record['major_business']);
        if(!empty($ma)){
            $major = $store_major_business->where(array('sc_id' => array('in', $ma)))->select();
        }


        //退货设置
        $daddress_model = M('Daddress');
        unset($condition);
        $condition['store_id'] = $_SESSION['store_id'];
        $add_record = $daddress_model->where($condition)->find();
        $pos = strpos($add_record['telphone'], "-");
        if ($pos === false) {
            $add_record['tel'] = $add_record['telphone'];
        } else {
            $add_record['area_code'] = substr($add_record['telphone'], 0, $pos);
            $add_record['tel'] = substr($add_record['telphone'], $pos + 1);
        }

        $this->assign('add_info', $add_record);
        $this->assign('store_info', $store_record);
        $this->assign('major', $major);
        
        $this->display();
    }
      
    /***
     *检查店铺名称是否存在
     */

    public function checkStorename()
    {
        $store_model=M('Store');
        $condition['store_name'] = $_REQUEST['store_name'];
        $tmp[] = $_SESSION['store_id'];
        $condition['store_id'] = array('not in', $tmp);
        $name_exist = $store_model->where($condition)->select();
        if($name_exist){
            $this->jsonFail('你修改的店铺名称已存在！');
        }else{
            $this->jsonSucc('suc');
        }
    }
    
    /***
     *首页基本信息ajax修改
     */
    public function storeSettingAjax()
    {
        //根据POST参数将数据进行分类
        //base,connect,refund
        $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
        //如果存在post_type,说明前端对表单进行提交,对session进行校验
        if ($type) {
            $store_id = $_SESSION['store_id'];
            $store_model = M('Store');
            switch ($type) {
                case 'base':
                    $this->_save_base($_POST);

                    break;
                case 'connect':
                    $this->_save_contact($_POST);
                    break;
                case 'refund':
                    $this->_save_daddr($_POST);
                    exit;
                    break;
                case 'service':
                    $this->_save_qqwangwang($_POST);
                    exit;
                    break;
                default:
                    $this->jsonFail('请求参数不正确!');
            }
        }
        $this->jsonFail('请求参数不正确!');
    }

    /**
     * 保存退货地址
     */
    private function _save_daddr($post)
    {
       // var_dump($post);exit;
        if (!isset($_POST) || empty($post)) {
            //
        }
        //收货人
        $update['receiver'] = $post['refund_receiver'];
        //手机号码
        $update['mobile_phone'] = $post['refund_phone'];
        //座机号码
        $update['telphone'] = $post['refund_tel'];
        //省id
        $update['province_id'] = $post['refund_province_id'];
        //市id
        $update['city_id'] = $post['refund_city_id'];
        //区id
        $update['area_id'] = $post['refund_area_id'];
        //详细地址
        $update['address'] = $post['refund_address'];
        //区域信息
        $update['area_info'] = $post['store_area_info'];
        
        //获取退货地址
        $daddress_model = M('Daddress');
        $condition['store_id'] = $_SESSION['store_id'];
        $daddress_record = $daddress_model->where($condition)->find();
        if (!$daddress_record) {
            //没有记录，新增记录
            $update['store_id'] = $_SESSION['store_id'];
            if (!$daddress_model->add($update)) {
                $this->jsonFail("数据库保存失败!");
            } else {
                $this->jsonSucc("新增地址成功!");
            }
        } else {
            //存在记录，更新记录

            //收货人
            $daddress_record['receiver'] = $post['refund_receiver'];
            //手机号码
            $daddress_record['mobile_phone'] = $post['refund_phone'];
            //座机号码
            $daddress_record['telphone'] = $post['refund_tel'];
            //省id
            $daddress_record['province_id'] = $post['refund_province_id'];
            //市id
            $daddress_record['city_id'] = $post['refund_city_id'];
            //区id
            $daddress_record['area_id'] = $post['refund_area_id'];
            //详细地址
            $daddress_record['address'] = $post['refund_address'];
            //区域信息
            $daddress_record['area_info'] = $post['store_area_info'];
            $daddress_model->save($daddress_record, array('address_id' => $daddress_record['address_id']));
            $this->jsonSucc("地址更新成功!");
        }

        echo $this->jsonSucc("数据库更新成功");
    }

    /**
     * 保存保存店铺的联系信息
     *
     * @param      <type>  $_post  The post
     */
    private function _save_contact($_post)
    {
        //固定电话
        $store_tel = $_POST['store_tel'];
        //手机
        $store_phone = $_POST['store_phone'];
        //区域id
        $store_area_id = intval($_POST['store_area_id']);
        //市id
        $store_city_id = intval($_POST['store_city_id']);
        //省id
        $store_province_id = $_POST['store_province_id'];
        //详细地址
        $store_address = $_POST['store_address'];

        //区域信息保存到store_joinin 表
        $store_id = $_SESSION['store_id'];
        $store_joinin_model = D('StoreJoinin');
        $update_date['province_id'] = $store_province_id;
        $update_date['area_id2'] = $store_area_id;
        $update_date['city_id'] = $store_city_id;
        $update_date['company_address_detail'] = $store_address;
        $update_date['contacts_phone'] = $store_phone;
        $update_date['company_phone'] = $store_tel;

        $result_store_join = $store_joinin_model->modify($update_date, array('member_id' => $_SESSION['member_id']));

        $store_model = D('Store');
        unset($update_date);
        $update_date['area_id'] = $store_area_id;
        $update_date['province_id'] = $store_province_id;
        $update_date['city_id'] = $store_city_id;
        $update_date['area_info'] = $store_address;
        $update_date['store_tel'] = $store_phone;
        $update_date['company_phone'] = $store_tel;

        $result_store = $store_model->editStore($update_date, array('member_id' => $_SESSION['member_id']));

        $this->jsonSucc("地址更新成功!");


    }

    /**
     * 存储基本信息.
     *
     * @param        $_post  The post
     */
    private function _save_base($_post)
    {
        //店铺名
        $store_name = trim($_POST['store_name']);
        //店铺logo
        $store_logo = $_POST['store_logo'];
        //店铺描述
        $store_desc = $_POST['store_description'];
        $store_logo = str_replace(C('host') . DS, "", $store_logo);
        $store_model = D('Store');
        $condition['store_name'] = $store_name;
        $tmp[] = $_SESSION['store_id'];
        $condition['store_id'] = array('not in', $tmp);
        $name_exist = $store_model->where($condition)->select();


        if (!empty($name_exist)) {
            //店铺名存在
            $this->jsonFail('店铺名已存在!');
        }
        //查询店铺名称是否已经存在,如果已经存在,则不进行修改
        $name_condition['store_id'] = $_SESSION['store_id'];
        $store_name_check = $store_model->getStoreInfo($name_condition);
        //检查是否还有修改的机会,并且和原来不重名,否则一律不修改
        $is_update = false;
        if ($store_name_check['store_name'] != $store_name && $store_name_check['is_modify_name'] == 0 && $store_name) {
            $update_date['store_name'] = $store_name;
            $update_date['is_modify_name'] = 1;
            $is_update = true;
        }
        $update_date['store_label'] = $store_logo;
        $update_date['store_description'] = $store_desc;
        $result = $store_model->editStore($update_date, array('store_id' => $_SESSION['store_id']));
        unset($update_date);
        if ($is_update === true) {
            //如果触发了修改店铺名称的代码,则通过joinin表
            $store_joinin_model = D('StoreJoinin');
            $update_date['store_name'] = $store_name;
            $store_joinin_model->modify($update_date, array('member_id' => $_SESSION['member_id']));
        }
        $this->jsonArr(array('is_update'=>$is_update));
    }
    
    /*
     *  保存qq 和旺旺
     */
    private function _save_qqwangwang($_post){
        if(is_null($_SESSION['store_id']))
            jsonFail("找不到店铺记录!");
        $model_class = D('Store');
        $param['store_qq']    = $_POST['service_qq'];
        $param['store_ww']    = $_POST['service_wang'];
        $param['qq_nickname'] = $_POST['service_name1'];
        $param['ww_nickname'] = $_POST['service_name2'];
        try {
            $model_class->startTrans();
            $model_class->editStore($param, array('store_id' => $_SESSION['store_id']));
            $model_class->commit();
            $this->jsonSucc('修改成功');
        } catch (\Exception $exc) {
            $model_class->rollback();
            $this->jsonFail('修改失败');
        }
    }
    
    /*
     * 广告管理
     */
    public function advManage(){
        $store_info = D('Store')->getStoreInfoByID($_SESSION['store_id']);        
        $store_info['store_slide'] = explode(',', $store_info['store_slide']);
        $store_info['store_slide_url'] = explode(',', $store_info['store_slide_url']);
        $this->assign('store_info', $store_info);
        $this->display();
    }
    
    /*
     * 保存店招
     */
    public function saveSdvset(){
        if(is_null($_SESSION['store_id']))
            $this->jsonFail('store id can not be empty');
        $store = D('Store');
        $store_info =$store->getStoreInfoByID($_SESSION['store_id']);
        if (!empty($_POST['banner'])){
            $banner = C('TMPL_PARSE_STRING')['__UPLOAD__'].'/shop/store/'.$store_info['store_banner'];
            if($banner!=null){
                $banner = substr($banner, 1);
                if(file_exists($banner)){
                    //更新图片 删除之前的图片
                    unlink($banner);
                }
            }
            @unlink(ltrim(C('TMPL_PARSE_STRING')['__UPLOAD__'].'/shop/store/','/').$store_info['store_banner']);
        }
        $banner = str_replace('/data/upload/shop/store/', "", $_POST['banner']);
        $param['store_banner'] = empty($banner) ? $store_info['store_banner'] : $banner;
        $store->editStore($param, array('store_id' => $_SESSION['store_id']));
        //成功
        $this->jsonSucc();
    }
    
    public function saveBanner(){
        if(is_null($_POST['banner']))
            $this->jsonFail('至少上传一张图片');
        
        $model_store = D('Store');
        $model_upload = D('Upload');
        $banner = $_POST['banner'];
        foreach ($banner as $key => $value) {
            if((stripos($value['url'],'http://')===false) && (stripos($value['url'],'https://')=== false)){
                $this->jsonFail($value['url'].':链接地址格式不正确,请以http://格式开头！');
            }
            $_POST['image_path'][] = str_replace('/data/upload/shop/store/slide/', '', $value['m_pic']);
            $_POST['image_url'][]  = $value['url'];
        }

        $update = array();
        $update['store_slide']      = implode(',', $_POST['image_path']);
        $update['store_slide_url']  = implode(',', $_POST['image_url']);

        $model_store->editStore($update, array('store_id' => $_SESSION['store_id']));
        $this->jsonSucc();
       
        
        // 删除upload表中数据
//        if(!$model_upload->delByWhere(array('upload_type'=>7,'store_id'=>$_SESSION['store_id'])))
//            $this->jsonFail(' update upload table failed');      
        
    }
    
    /*
     * 充值
     */
    public function recharge()
    {
	    $model_payment = D('Payment');
	    $payment_res = $model_payment->getPaymentOpenList();
	    if (!empty($payment_res)) {
		    foreach($payment_res as $row){
			    $payment_list[$row['payment_code']] = $row;
			    $payment_list[$row['payment_code']]['payment_class'] = $row['payment_code'];
		    }
		    isset($payment_list['chinapay']) && $payment_list['chinapay']['payment_class'] = 'ylpay';
		    unset($payment_list['predeposit']);
		    unset($payment_list['offline']);
		    unset($payment_list['easypay']);
	    }
	    $this->assign('payment_list', $payment_list);

        $model_store = D('Store');
        $store_info =$model_store-> getStoreInfo(array('store_id' => session('store_id')), 'deposit_avaiable');
        $this->assign('yu_e', $store_info['deposit_avaiable']);

        $this->display();
    }

	/**
	 * 充值提交
	 */
	public function rechargeSub(){
		$money = I('post.money');
		$payment_code= I('post.payment_code');

		$order_model = D('Order');
		$pay_sn = $this->makePaySn($_SESSION['member_id']);
		$order_pay = array();
		$order_pay['pay_sn'] = $pay_sn;
		$order_pay['buyer_id'] = $_SESSION['member_id'];
		$order_pay['recharge_money'] = $money;
		$order_model->addOrderPay($order_pay);

		$this->_api_pay(array('pay_sn' => $pay_sn, 'pay_amount' => $money), array('payment_code' => $payment_code));
	}

	/**
	 * 生成支付单编号(两位随机 + 从2000-01-01 00:00:00 到现在的秒数+微秒+会员ID%1000)，该值会传给第三方支付接口
	 * 长度 =2位 + 10位 + 3位 + 3位  = 18位
	 * 1000个会员同一微秒提订单，重复机率为1/100
	 * @return string
	 */
	public function makePaySn($member_id) {
		return mt_rand(10,99)
		. sprintf('%010d',time() - 946656000)
		. sprintf('%03d', (float) microtime() * 1000)
		. sprintf('%03d', (int) $member_id % 1000);
	}

	/**
	 * 第三方在线支付接口
	 *
	 */
	private function _api_pay($order_info, $payment_info) {
		if($payment_info['payment_code'] == 'chinabank' OR $payment_info['payment_code'] == 'chinapay') {
			$gateway = CommonPay::getInstance('UnionPay_Express', 3);

			$order = [
				'orderId'   => $order_info['pay_sn'], //Your order ID
				'txnTime'   => date('YmdHis'), //Should be format 'YmdHis'
				'orderDesc' => '全木行'.$order_info['subject'], //Order Title
				'txnAmt'    => $order_info['pay_amount']*100, //Order Total Fee
				//'txnAmt'    => 1, //测试1分钱
			];

			$response = $gateway->send($order);
			$response->redirect();

		}else if($payment_info['payment_code'] == 'wxpay') {
			$gateway = CommonPay::getInstance('WechatPay', 3);

			$order = array (
				'body'             => '全木行'.$order_info['subject'], //Your order ID
				'out_trade_no'     => $order_info['pay_sn'], //Should be format 'YmdHis'
				//'total_fee'        => $order_info['pay_amount'] * 100, //Order Title
				'total_fee'        => 1, //测试1分钱
				'attach'           => $order_info['pay_sn'],
				'spbill_create_ip' => '114.119.110.120', //Order Total Fee
			);

			$response = $gateway->send($order);
			$order_info['wxqrcode'] = $response->getCodeUrl();
			$_SESSION['recharge_wxpay_url'][$order_info['pay_sn']] = $order_info['wxqrcode'];

			$param['pay_sn'] = $order_info['pay_sn'];
			$param = base64_encode(serialize($param));

			@header("Location: ".U('StoreCommon/rechargeWxPay', array('param' => $param)));
		}else if($payment_info['payment_code'] == 'alipay') {
			$gateway = CommonPay::getInstance('Alipay_Express', 3);

			$order = [
				'out_trade_no' => $order_info['pay_sn'], //your site trade no, unique
				'subject'      => '全木行'.$order_info['subject'], //order title
				'total_fee'    => $order_info['pay_amount'], //order total fee
				//'total_fee'    => '0.01', //测试1分钱
			];

			$response = $gateway->send($order);
			$response->redirect();
		}else if ($payment_info['payment_code'] == 'predeposit'){
			$this->pdPay($order_info);
		}
	}

	/**
	 * 微信充值页面
	 */
	public function rechargeWxPay(){
		$param = I('param');
		$param = unserialize(base64_decode($param));
		$order_model = D('Order');
		$order_info = $order_model->getOrderPayInfo(array('pay_sn' => $param['pay_sn'], 'buyer_id' => $_SESSION['member_id']));
		$order_info['wxqrcode'] = $_SESSION['recharge_wxpay_url'][$param['pay_sn']];
		$order_info['pay_amount'] = $order_info['recharge_money'];
		$this->assign('orderinfo', $order_info);
		$this->display('recharge_wx_pay');
	}

	/**
	 * 是否充值成功
	 */
	public function rechargeIsPayed(){
		$pay_sn = I('pay_sn');
		if($pay_sn){
			$order_model = D('Order');
			$order_pay = $order_model->getOrderPayInfo(array('pay_sn' => $pay_sn, 'buyer_id' => $_SESSION['member_id']));
			if(!empty($order_pay) && $order_pay['api_pay_state'] == 1){
				$this->ajaxReturn(array('code' => 1));
			}else{
				$this->ajaxReturn(array('code' => 0));
			}
		}
	}
    
    /**
     * 提交申请
     * @return [type] [description]
     */
    public function submitWithdraw(){
        if(!isset($_POST) || empty($_POST)){
            $this->jsonFail('请求参数错误');
        }
        
        //判断提现金额是否小于可提现金额
        $store_info = M('store')->where(array('store_id' => $_SESSION['store_id']))->find();
        $bank = M('bank')->where(array('id'=>$_POST['bank_id'],'member_id'=>$_SESSION['member_id']))->find();
        if(empty($store_info)||empty($bank)){
            $this->jsonFail('请求参数错误');            
        }else{
            $available = $store_info['deposit_avaiable'];
            $money = $_POST['money'];
            if($money <100 || $money > 1000 || !preg_match('/^\d+$/i', $money) || $money > $available){
                $this->jsonFail('提现金额要大于100小于1000的整数，且不能超过可提现金额！');
            }
        }
        
        //插入表
        $model_pd = D('Predeposit');
        try {
            $model_pd->startTrans();
            $member = M('member')->where(array('member_id'=>$_SESSION['member_id']))->find();
            $store = M('store')->where(array('member_id'=>$_SESSION['member_id']))->find();

            //插入提现表
            $data['pdc_sn'] = $model_pd->makeSn();
            $data['pdc_bank_id'] = $_POST['bank_id'];
            $data['pdc_member_id'] = $_SESSION['member_id'];
            $data['pdc_store_id'] = $_SESSION['store_id'];
            $data['pdc_store_name'] = $store['store_name'];
            $data['pdc_member_name']      = $member['member_name'];
            $data['pdc_amount']      = $money;
            $data['pdc_tx_type']      = 1;//商家提现
            $data['pdc_bank_name']      = $bank['bankname'];
            $data['pdc_bank_no']      = $bank['banknum'];
            $data['pdc_company']      = $bank['company'];
            $data['pdc_bank_user']      = $bank['username'];
            $data['pdc_add_time']      = time();   

            $model_pd->addPdc($data);
            //插入账户明细表
            // $data_history['member_id'] = $_SESSION['member_id'];
            // $data_history['bill_state'] = 2;
            // $data_history['amount'] = $money;
            // $data_history['add_time'] = $data['pdc_add_time'];
            // $data_history['remark'] = '提现';
            // $data_history['tesu_description'] = 'pdc_sn:'.$data['pdc_sn'];            
            // $model_pd->addHistory($data_history);
            
            //更改预存款
            $data_member['member_id'] = $_SESSION['member_id'];
            $data_member['member_name'] = $member['member_name'];
            $data_member['store_id'] = $_SESSION['store_id'];
            $data_member['order_sn'] = $data['pdc_sn'];
            $data_member['amount'] = $money;
            $model_pd->changeStore('cash_apply',$data_member);
            $model_pd->commit();
            $this->jsonSucc();
        } catch (Exception $e) {
            $model_pd->rollback();
            $this->jsonFail('提现失败！');
        }
    }
    
    /*
     *提现 
     */
    public function withdraw()
    {
        //获取个人银行卡信息,可用余额信息，保证金信息
        $store = M('store')->where(array('store_id'=>$_SESSION['store_id']))->find();
        $list = M('bank')->where(array('member_id'=>$_SESSION['member_id'],'is_del'=>0))->select();
        $this->assign('deposit_avaiable', $store['deposit_avaiable']);
        $this->assign('list', $list);
        $this->display();
    }
    
    
    /*
     *添加银行卡 
     */
    public function addDepositCard()
    {
        if(empty($_GET['bank_id'])){
            $bank = NULL;
        }else{
            $bank = M('bank')->where(array('member_id'=>$_SESSION['member_id'],'id'=>$_GET['bank_id'],'is_del'=>0))->find();
        }
        $member = M('member')->where(array('member_id'=>$_SESSION['member_id']))->find();
        
        $bank_list = M('bank_type')->where(array('state'=>1))->select();
        
        $this->assign('mobile',$member['mobile']);
        $this->assign('bank',$bank);
        $this->assign('bank_list',$bank_list);
        $this->display();
    }
    
    /*
     * 保存银行卡
     * type:0：save  1：update  2：del 3：设置默认 4:提现审核失败重新编辑提现信息
     */
    public function saveBank(){
        if(!empty($_POST)){
            $type = $_POST['type'];
            $condition['id'] = $_POST['bank_id'];
            $condition['member_id'] = $_SESSION['member_id'];
            $condition['is_del'] = 0;
            $bank_model = D('Bank');
            switch ($type) {
                case 0:
                case 1:
                case 4:
                    $param['bank_type'] = $_POST['card_name'];
                    $param['bankname'] = $_POST['bank_name'];
                    $param['username'] = $_POST['card_username'];
                    $param['banknum'] = $_POST['card_num'];
                    $param['mobile'] = $_POST['phone'];
                    $param['company'] = $_POST['card_companyname'];
                    //参数校验
                    $this->verifyCode($param['mobile'], $_POST['code_num']);
                    
                    $this->bankSaveValid($param);

                    if($type == 0){
                        $param['member_id'] = $_SESSION['member_id'];
                        $param['created_at'] = time();
                        $result = $bank_model->addBank($param);
                    }else{
                        $param['updated_at'] = time();
                        //更新银行卡信息
                        $result = $bank_model->updateBank($condition,$param);
                        if($type == 4){
                            //更新审核失败的提现信息
                            $model_pd = Model('predeposit');
                            $update['pdc_bank_name'] = $param['bankname'];
                            $update['pdc_bank_no'] = $param['banknum'];
                            $update['pdc_company'] = $param['company'];
                            $update['pdc_bank_user'] = $param['username'];
                            $update['pdc_bank_user'] = $param['username'];
                            $update['pdc_add_time'] = time();
                            $update['pdc_payment_state'] = 0;
                            $update['tesu_description'] = '重新申请';
                            $result = $model_pd->editPdCash($update,array('pdc_id'=>$_POST['pc_id'],'pdc_store_id'=>$_SESSION['store_id'],'pdc_payment_state'=>2));
                        }
                    }
                    break;
                case 2:
                    $result = $bank_model->delBank($condition);
                    break;
                case 3:
                    //取消默认银行卡
                     $update['is_default'] = 0;
                    $bank_model->updateBank(array('member_id'=>$_SESSION['member_id'],'is_default'=>1),$update);
                    //设置默认银行卡
                     $update['is_default'] = 1;
                    $result = $bank_model->updateBank($condition,$update);
                    break;
                default:
                    break;
            }
            $this->jsonSucc();

        }
    }

    /*
     * 保存银行卡验证
     */
    private function bankSaveValid($param) {
        $model_bank =  D('Bank');
        $rules = array(
             array('bankname','require','银行不能为空且必须小于30个字！'),
             array('bankname','1,30','银行不能为空且必须小于30个字！',0,'length'),
             array('username','require','姓名不能为空且必须小于30个字！'),
             array('username','1,30','姓名不能为空且必须小于30个字！',0,'length'),
             array('banknum','require','银行卡号不能为空且必须介于16到19个数字之间！'),
             array('banknum','16,19','银行卡号不能为空且必须介于16到19个数字之间！',0,'length'),
             array('mobile','require','手机号不能为空且必须为11个数字！'),
             array('mobile',11,'手机号不能为空且必须为11个数字！',0,'length'),            
             array('company','require','公司名称不能为空且必须小于30个字！'),
             array('company','1,30','公司名称不能为空且必须小于30个字！',0,'length'),
        );
        
        if(!$model_bank->validate($rules)->create($param)){
            //返回验证失败的信息
            $this->jsonFail($model_store_joinin->getError());
        }

        $bank = $model_bank->getBank(array('member_id'=>array('neq',$_SESSION['member_id']),'is_del'=>0,'banknum'=>$param['banknum']));
        if(!empty($bank)){
            $this->jsonFail('卡号已被使用！');
        }
    }
    
    /*
     * 发送短信验证码
     */
    public function sendCode()
    {
        $to = I('post.to_mobile');
        $verify_time = $_SESSION['ts_'.$to]['verify_time'];
        if(!empty($verify_time)){
            $last_time = time()-$verify_time;
            if($last_time < 55){
                $this->jsonFail('间隔时间太短,'.(60-$last_time).'s后可重新获取！');
            }else{
                if(sendRegis($to)){
                    $this->jsonSucc('发送成功！');
                }else{
                    $this->jsonFail('发送失败！');
                }
            }
        }else{
            if(sendRegis($to)){
                $this->jsonSucc('发送成功！');
            }else{
                $this->jsonFail('发送失败！');
            }
        }
    }

    /*
     * 验证码验证
     */
    public function verifyCode($mobile,$code)
    {
        $seconds = time() - $_SESSION['ts_'.$mobile]['verify_time'];
        if(empty($code) || $_SESSION['ts_'.$mobile]['verify_code'] != $code || $seconds > 60){
            $this->jsonFail('验证码错误！');
        }
    }


    /*
     * 商家的商品下架
     */
    public function GoodsOnlineToOffline(){
        // <input type="hidden" id="deleteUrl" value="/index.php?act=store_goods_online&op=turn_to_on_off&type=off" data-type="goodslist" data-msg="商品" data-isreload="no" />
        $this->jsonFail('这家伙很懒，什么都没留下');
    }

    /*
     * 发布商品 第一步
     */
    public function goodsPubStep1(){
        $this->display();
    }

    /*
     * 发布商品
     */
    public function goodsPub(){
        // 实例化商品分类模型
        $model_goodsclass = D('GoodsClass');
        // 商品分类
        $goods_class = $model_goodsclass->getGoodsClass($_SESSION['store_id'], 0, 1, 1);
        // 常用商品分类
//        $model_staple = Model('goods_class_staple');
//        $param_array = array();
//        $param_array['member_id'] = $_SESSION['member_id'];
//        $staple_array = $model_staple->getStapleList($param_array);
//        Tpl::output('staple_array', $staple_array);
        $this->assign('goods_class', $goods_class);
        //获取商家体验店信息
        $brick_model = D('BrickStore');
        $conditon_brickstore['store_id'] = $_SESSION['store_id'];
        $brick_store = $brick_model->getAllBrickStore($conditon_brickstore);
        $this->assign('brickstore', $brick_store);

        //获取商家运费模版
        $transport_model = D('Transport');
        $conditon_transport['store_id'] = $_SESSION['store_id'];
        $transport = $transport_model->getAllTransportInfo($conditon_transport);
        $this->assign('transport', $transport);

        $gc_id = isset($_GET['gc_id']) ? $_GET['gc_id'] : '';
        // 更新常用分类信息
        $goods_class = $model_goodsclass->getGoodsClassLineForTag($gc_id);
        // 获取类型相关数据
        if ($goods_class['type_id'] > 0) {
            $typeinfo = D('Type')->getAttr($goods_class['type_id'], $_SESSION['store_id'], $gc_id);
            list($spec_json, $spec_list, $attr_list, $brand_list) = $typeinfo;
            $this->assign('sign_i', count($spec_list));
            $this->assign('spec_list', $spec_list);
            $this->assign('attr_list', $attr_list);
            $this->assign('brand_list', $brand_list);
        }
        $this->display();
    }
    
    /*
     * 发布商品第二步
     */
    public function addGoodsStep2()
    {
       // $this->_checkstore();//三方店铺验证，商品数量，有效期
        // 实例化商品分类模型
        $model_goodsclass = D('GoodsClass');
        // 商品分类
        //$goods_class = $model_goodsclass->getGoodsClass($_SESSION['store_id']);
        // 常用商品分类
        $model_staple = D('GoodsClassStaple');
        $param_array = array();
        $param_array['member_id'] = $_SESSION['member_id'];
        $staple_array = $model_staple->getStapleList($param_array);
        $this->assign('staple_array', $staple_array);
       // $this->assign('goods_class', $goods_class);
        //获取商家体验店信息
        $brick_model = D('BrickStore');
        $conditon_brickstore['store_id'] = $_SESSION['store_id'];
        $brick_store = $brick_model->getAllBrickStore($conditon_brickstore,0,0,false);//不缓存
        $this->assign('brickstore', $brick_store);

        //获取商家运费模版
        $transport_model = D('Transport');
        $conditon_transport['store_id'] = $_SESSION['store_id'];
        $transport = $transport_model->getAllTransportInfo($conditon_transport,false);//不缓存        
        $this->assign('transport', $transport);
        
        $gc_id = isset($_GET['gc_id']) ? $_GET['gc_id'] : '';
        $goods_class = $model_goodsclass->getOneGoodsClass($gc_id);

        $typeinfo = $this->getAttributeList($goods_class,$gc_id);
        list($spec_list, $attr_list, $brand_list) = $typeinfo;
        $this->assign('sign_i', count($spec_list));
        $this->assign('spec_list', $spec_list);
        $this->assign('attr_list', $attr_list);
        $this->assign('brand_list', $brand_list);

        $this->display();
    }
    
    public function getAttributeList($goods_class, $gc_id)
    {
        $spec_list = array();
        $attr_list = array();
        $brand_list = array();
        if(!empty($goods_class['spec'])){
            $spec_model  = D('Spec');
            $spec_id = json_decode($goods_class['spec']);
            $spec_info = $spec_model->getSpecInfo($spec_id[0],'sp_id,sp_name');
            $spec_list = $spec_model->getSpecValueList(array('sp_id'=>$spec_id[0], 'gc_id'=>$gc_id, 'store_id' => $_SESSION['store_id']));
            $spec_arr = array();
            foreach ($spec_list as $key=>$val){
                $spec_temp = array();
                $spec_temp['sp_value_id'] = $val['sp_value_id'];
                $spec_temp['sp_value_name'] = $val['sp_value_name'];
                $spec_arr[$spec_info['sp_id']]['value'][] = $spec_temp;
            }
            $spec_arr[$spec_info['sp_id']]['sp_id'] = $spec_info['sp_id'];
            $spec_arr[$spec_info['sp_id']]['sp_name'] = $spec_info['sp_name'];
            $spec_list = $spec_arr;
        }
        if(!empty($goods_class['attr'])){            
            $attrs= json_decode($goods_class['attr'],TURE);
            $list = array(); 
            foreach ($attrs as $key=>$val){
                foreach($val as $k=>$v){
                    $list[$key]['attr_id'] =  $key;
                    $attribute = M('attribute')->where(array('attr_id'=>$key,'type_id'=>0))->find();
                    $list[$key]['attr_name'] =  $attribute['attr_name'];
                    $temp_arr['attr_value_id'] = $v;
                    $temp_arr['attr_value_name'] = $k;
                    $list[$key]['value'][] =  $temp_arr;
                }
            }
            $attr_list = $list;
        }
       
//        if(!empty($goods_class['brand'])){
//            $brand_id = json_decode($goods_class['brand']);
//            $brand_list = D('Brand')->getBrandPassList(array('brand_id'=>array('in',$brand_id)),'brand_id,brand_name');      
//        }
        //改为获取全部品牌
        $brand_list = D('Brand')->getBrandPassList(array('tesu_deleted'=>0),'brand_id,brand_name');
        return array($spec_list, $attr_list, $brand_list);
    }

    /****
     *
     * 发布商品提交表单
     */
    public function publishGoodsAjax()
    {
        // 验证表单
        $this->_checkPublishForm();

        //获取图片相关的数组,包含图片处理
        $img_ori = $this->_handlerGoodsImg();
        $model_goods = D('Goods');
        $model_type = D('Type');
		$model_setting = D('setting');
        $goods_verify = $model_setting->getRowSetting('goods_verify');
        $common_array = array();
        $time =time();
        $common_array['goods_name']        = $_POST['g_name'];//商品名称
        $common_array['goods_jingle']      = $_POST['g_jingle'];//宣传语
        $common_array['gc_id']             = intval($_POST['gc_id']);//分类id
        $common_array['gc_name']           = $_POST['gc_name'];//分类名
        $common_array['brand_id']          = empty($_POST['b_id'])?0:$_POST['b_id'];//品牌id
        $common_array['brand_name']        = $_POST['b_name'];//品牌名字
        $common_array['type_id']           = intval($_POST['type_id']);//类型id
        $common_array['goods_image']       = is_array($img_ori) ? $img_ori[0] : '';//获取图片数据
        $common_array['goods_price']       = floatval($_POST['g_price']);//商品价格
        $common_array['goods_marketprice'] = floatval($_POST['g_marketprice']);//市场价
        $common_array['goods_costprice']   = floatval($_POST['g_costprice']);//成本价
        $common_array['goods_discount']    = (floatval($_POST['g_price']) / floatval($_POST['g_marketprice'])) * 100;//折扣
        $common_array['goods_serial']      = $_POST['g_serial'];//商家编号
        $common_array['goods_attr']        = serialize($_POST['attr']);//商品属性
        $common_array['goods_body']        = html_entity_decode($_POST['editorValue']) ?: '';//富文本编辑框内容
        $common_array['goods_commend']     = intval($_POST['iscommend']) ?: 0;//是否推荐,推荐商品显示在店铺首页
        $common_array['goods_state']       = ($this->store_info['store_state'] != 1) ? 0 : intval($_POST['g_state']);            // 店铺关闭时，商品下架
        $common_array['goods_addtime']     = $time;
        $common_array['goods_selltime']    = $time;
        $common_array['tesu_offline_time'] = $time;
        $common_array['goods_verify']      = $goods_verify['value'] == 1 ? 10 : 1;
        $common_array['store_id']          = $_SESSION['store_id'];
        $common_array['store_name']        = $_SESSION['store_name'];
        $common_array['spec_name']         = is_array($_POST['spec']) ? serialize($_POST['sp_name']) : serialize(null);//规格name
        $common_array['spec_value']        = is_array($_POST['spec']) ? serialize($_POST['sp_val']) : serialize(null);//规格value
        $common_array['goods_vat']         = 0;
        $common_array['areaid_1']          = intval($_POST['province_id']);//商品所在地省份
        $common_array['areaid_2']          = intval($_POST['city_id']);//商品所在地市区
        $common_array['transport_id']      = ($_POST['freight'] == '0') ? '0' : intval($_POST['freight']); // 运费模板
        $common_array['transport_title']   = $_POST['transport_title'];//运费名称
        $common_array['goods_freight']     = floatval($_POST['g_freight']);
        $common_array['goods_stcids']      = ',0,';//',' . implode(',', array_unique([])) . ',';    // 首尾需要加,
        $common_array['plateid_top']       = intval($_POST['plate_top']) > 0 ? intval($_POST['plate_top']) : 0;
        $common_array['plateid_bottom']    = intval($_POST['plate_bottom']) > 0 ? intval($_POST['plate_bottom']) : 0;
        $common_array['goods_volume']      = $_POST['volume'];//体积
        $common_array['goods_weight']      = $_POST['weight'];//重量
        $common_array['customization']     = $_POST['iscustom'];//是否可定制
        $common_array['brick_store']       = isset($_POST['brickstore']) ? intval($_POST['brickstore']) : 0;
        //工厂上传商品
        if($_SESSION['com_type'] == 3){
            $common_array['is_offline'] = 2;
        }
        
        //添加常用类目
        $this->_addStapleClass($common_array['gc_id']);
        // 保存数据
        $common_id = $model_goods->addGoods($common_array, 'goods_common');

        //保存图片数据
        $save_image = $this->_saveGoodsImg($img_ori, $common_id, $_SESSION['store_id']);

        if ($save_image === false) {
            //图片上传生成失败,中断操作
            $common_array['goods_image'] = $save_image;
            $this->jsonFail('图片上传失败,请重试!');
        } else {
            //上传成功
        }
        //生成体验店相关信息
        //$this->_createBrickGoods($common_id, $common_array['brick_store']);  没有体验店注释掉
        if ($common_id) {
            // 生成商品二维码
//            require_once(BASE_RESOURCE_PATH . DS . 'phpqrcode' . DS . 'index.php');
//            $PhpQRCode = new PhpQRCode();
//            $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $_SESSION['store_id'] . DS);
            // 商品规格
            if (is_array($_POST['spec'])) {
                foreach ($_POST['spec'] as $value) {
                    $goods = array();
                    $goods['goods_commonid'] = $common_id;
                    //$goods['goods_name'] = $common_array['goods_name'] . ' ' . implode(' ', $value['sp_value']);
                    $goods['goods_name'] = $common_array['goods_name'];
                    $goods['goods_jingle'] = $common_array['goods_jingle'];
                    $goods['store_id'] = $common_array['store_id'];
                    $goods['store_name'] = $_SESSION['store_name'];
                    $goods['gc_id'] = $common_array['gc_id'];
                    $goods['brand_id'] = $common_array['brand_id'];
                    $goods['goods_marketprice'] = $common_array['goods_marketprice'];
                    $goods['goods_image'] = $common_array['goods_image'];
                    $goods['goods_state'] = $common_array['goods_state'];
                    $goods['goods_verify'] = $common_array['goods_verify'];
                    $goods['goods_addtime'] = $time;
                    $goods['goods_edittime'] = $time;
                    $goods['areaid_1'] = $common_array['areaid_1'];
                    $goods['areaid_2'] = $common_array['areaid_2'];
                    $goods['transport_id'] = $common_array['transport_id'];
                    $goods['goods_freight'] = $common_array['goods_freight'];
                    $goods['goods_vat'] = $common_array['goods_vat'];
                    $goods['goods_commend'] = $common_array['goods_commend'];
                    $goods['goods_stcids'] = $common_array['goods_stcids'];

                    $goods['goods_volume'] = $_POST['volume'];//体积
                    $goods['goods_weight'] = $_POST['weight'];//重量
                    $goods['customization'] = $_POST['iscustom'];//是否可定制

                    $goods['goods_serial'] = $common_array['goods_serial'];
                    $goods['goods_price'] = abs($value['price']);
                    $goods['color_id'] = intval($value['color']);
                    $goods['goods_storage'] = abs($value['sku']);
                    $goods['goods_spec'] = serialize($value['sp_value']);
                    //工厂上传商品
                    if($_SESSION['com_type'] == 3){
                        $goods['is_offline'] = 2;
                    }
                    $goods_id = $model_goods->addGoods($goods);
                    $model_type->addGoodsType($goods_id, $common_id, array('cate_id' => $_POST['cate_id'], 'type_id' => $_POST['type_id'], 'attr' => $_POST['attr']));
                    // 生成商品二维码
//                    $PhpQRCode->set('date', urlShop('goods', 'index', array('goods_id' => $goods_id)));
//                    $PhpQRCode->set('pngTempName', $goods_id . '.png');
//                    $PhpQRCode->init();
                }
            } else {
                //没有多种规格的情况下生成商品
                $goods = array();
                $goods['goods_commonid'] = $common_id;
                $goods['goods_name'] = $common_array['goods_name'];
                $goods['goods_jingle'] = $common_array['goods_jingle'];
                $goods['store_id'] = $common_array['store_id'];
                $goods['store_name'] = $_SESSION['store_name'];
                $goods['gc_id'] = $common_array['gc_id'];
                $goods['brand_id'] = $common_array['brand_id'];
                $goods['goods_price'] = $common_array['goods_price'];
                $goods['goods_marketprice'] = $common_array['goods_marketprice'];
                $goods['goods_serial'] = $common_array['goods_serial'];
                $goods['goods_spec'] = serialize(null);
                $goods['goods_storage'] = intval($_POST['g_storage']);
                $goods['goods_image'] = $common_array['goods_image'];
                $goods['goods_state'] = $common_array['goods_state'];
                $goods['goods_verify'] = $common_array['goods_verify'];
                $goods['goods_addtime'] = $time;
                $goods['goods_edittime'] = $time;
                $goods['areaid_1'] = $common_array['areaid_1'];
                $goods['areaid_2'] = $common_array['areaid_2'];
                $goods['color_id'] = 0;
                $goods['transport_id'] = $common_array['transport_id'];
                $goods['goods_freight'] = $common_array['goods_freight'];
                $goods['goods_vat'] = $common_array['goods_vat'];
                $goods['goods_commend'] = $common_array['goods_commend'];
                $goods['goods_stcids'] = $common_array['goods_stcids'];
                $goods['goods_volume'] = $_POST['volume'];//体积
                $goods['goods_weight'] = $_POST['weight'];//重量
                $goods['customization'] = $_POST['iscustom'];//是否可定制
                //工厂上传商品
                if($_SESSION['com_type'] == 3){
                    $goods['is_offline'] = 2;
                }
                $goods_id = $model_goods->addGoods($goods);
                
                $res = $model_type->addGoodsType($goods_id, $common_id, array('cate_id' => $_POST['cate_id'], 'type_id' => $_POST['type_id'], 'attr' => $_POST['attr']));
                // 生成商品二维码
//                $PhpQRCode->set('date', urlShop('goods', 'index', array('goods_id' => $goods_id)));
//                $PhpQRCode->set('pngTempName', $goods_id . '.png');
//                $PhpQRCode->init();
            }
            // 记录日志
            $this->recordSellerLog('添加商品，平台货号:' . $common_id);

            //生成店铺导航栏,仅仅在生成商品的时候生成
            //$this->_createNavigation($_POST['ct1'], $_SESSION['store_id'], $_POST['cate_id']);

            $this->jsonSucc('添加成功!');
        } else {
            $this->jsonFail('添加失败!');
        }
    }
    
   /***
     *
     * 编辑商品
     */
    public function editGoodsAjax()
    {
        $common_id = intval($_POST ['commonid']);
        if ($common_id <= 0) {
            $this->jsonFail('商品不存在');
        }
        $this->_checkPublishForm();
        $img_ori = $this->_handlerGoodsImg();
        $model_goods = D('Goods');
		$model_setting = D('Setting');
        $goods_verify = $model_setting->getRowSetting('goods_verify');
          
        $update_common = array();
        $time = time();
        $update_common['goods_name'] = $_POST['g_name'];//商品名称
        $update_common['goods_jingle'] = $_POST['g_jingle'];//宣传语
        $update_common['gc_id'] = intval($_POST['gc_id']);//分类id
        $update_common['gc_name'] = $_POST['gc_name'];//分类名
        $update_common['type_id'] = intval($_POST['type_id']);//类型id
        $update_common['brand_id'] = empty($_POST['b_id'])?0:$_POST['b_id'];//品牌id
        $update_common['brand_name'] = $_POST['b_name'];//品牌名字
        $update_common['goods_image'] = is_array($img_ori) ? $img_ori[0] : '';//默认图片存到common和goods表中
        $update_common['goods_price'] = floatval($_POST['g_price']);//商品价格
        $update_common['goods_marketprice'] = floatval($_POST['g_marketprice']);//市场价
        $update_common['goods_costprice'] = floatval($_POST['g_costprice']);//成本价
        $update_common['goods_discount'] = (floatval($_POST['g_price']) / floatval($_POST['g_marketprice'])) * 100;//折扣
        $update_common['goods_serial'] = $_POST['g_serial'];//商家编号
        $update_common['goods_attr'] = serialize($_POST['attr']);//商品属性
        $update_common['goods_body'] = html_entity_decode($_POST['editorValue']) ?: '';//富文本编辑框内容
        $update_common['goods_commend'] = intval($_POST['iscommend']) ?: 0;//是否推荐,推荐商品显示在店铺首页
        $update_common['goods_state'] = ($this->store_info['store_state'] != 1) ? 0 : intval($_POST['g_state']);            // 店铺关闭时，商品下架
        $update_common['goods_selltime'] = $time;//商品上架时间不修改
        $update_common['goods_addtime'] = $time;//发布时间更新最新时间
        $update_common['goods_verify'] = intval($_POST['g_state'])===0 ? 1:($goods_verify['value'] == 1 ? 10 : 1);
        $update_common['store_id'] = $_SESSION['store_id'];
        $update_common['store_name'] = $_SESSION['store_name'];
        $update_common['spec_name'] = is_array($_POST['spec']) ? serialize($_POST['sp_name']) : serialize(null);//规格name
        $update_common['spec_value'] = is_array($_POST['spec']) ? serialize($_POST['sp_val']) : serialize(null);//规格value
        $update_common['goods_vat'] = intval($_POST['g_vat']);
        $update_common['areaid_1'] = intval($_POST['province_id']);//商品所在地省份
        $update_common['areaid_2'] = intval($_POST['city_id']);//商品所在地市区
        $update_common['transport_id'] = ($_POST['freight'] == '0') ? '0' : intval($_POST['freight']); // 运费模板
        $update_common['transport_title'] = $_POST['transport_title'];//运费名称
        $update_common['goods_freight'] = floatval($_POST['g_freight']);
        $update_common['goods_stcids'] = ',0,';//',' . implode(',', array_unique([])) . ',';    // 首尾需要加,
        $update_common['plateid_top'] = intval($_POST['plate_top']) > 0 ? intval($_POST['plate_top']) : '';
        $update_common['plateid_bottom'] = intval($_POST['plate_bottom']) > 0 ? intval($_POST['plate_bottom']) : '';
        $update_common['goods_volume'] = $_POST['volume'];//体积
        $update_common['goods_weight'] = $_POST['weight'];//重量
        $update_common['customization'] = $_POST['iscustom'];//是否可定制
        $update_common['brick_store'] = isset($_POST['brickstore']) ? intval($_POST['brickstore']) : 0;
        //$update_common['is_offline'] = 0;//线上
        $return = $model_goods->editGoodsCommon($update_common, array('goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']));//编辑修改goods_common的公共属性
        //保存图片数据
        $save_image = $this->_saveGoodsImg($img_ori, $common_id, $_SESSION['store_id'], true);
        //echo "<pre>";print_r($_POST['sp_val']);exit;
        if ($save_image === false) {
            //图片上传生成失败,中断操作
            $this->jsonFail('图片上传失败,请重试!');
        } else {
            //上传成功
        }
        if ($return) {
            // 清除原有规格数据
            $model_type = D('Type');
            $model_type->delGoodsAttr(array('goods_commonid' => $common_id));

            // 生成商品二维码
//            require_once(BASE_RESOURCE_PATH . DS . 'phpqrcode' . DS . 'index.php');
//            $PhpQRCode = new PhpQRCode();
//            $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $_SESSION['store_id'] . DS);
            $goodsdeletelist=$model_goods->field("goods_id")->where(array("goods_commonid"=>$common_id))->select();//将要删除的goodsid
            
            $goodsid_arr=array();
            if($goodsdeletelist){
                foreach ($goodsdeletelist as $va){
                    $goodsid_arr[]=$va["goods_id"];
                }
            }
            // 更新商品规格
            $goodsid_array = array();
            $colorid_array = array();
            if (is_array($_POST ['spec'])) {
                foreach ($_POST['spec'] as $value) {
                    $goods_info = $model_goods->getGoodsInfo(array('goods_id'=> $value['goods_id'], 'goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']), 'goods_id');
                    if (!empty($goods_info)) {
                        $goods_id = $goods_info['goods_id'];
                        $update = array();
                        $update['goods_commonid'] = $common_id;
                        //$update['goods_name'] = $update_common['goods_name'] . ' ' . implode(' ', $value['sp_value']);
                        $update['goods_name'] = $update_common['goods_name'];
                        $update['goods_jingle'] = $update_common['goods_jingle'];
                        $update['store_id'] = $_SESSION['store_id'];
                        $update['store_name'] = $_SESSION['store_name'];
                        $update['brand_id'] = $update_common['brand_id'];
                        $update['goods_marketprice'] = $update_common['goods_marketprice'];
                        $update['goods_spec'] = serialize($value['sp_value']);
                        $update['goods_state'] = $update_common['goods_state'];
                        $update['goods_verify'] = $update_common['goods_verify'];
                        $update['goods_edittime'] = $time;
                        $update['goods_addtime'] = $time;
                        $update['areaid_1'] = $update_common['areaid_1'];
                        $update['areaid_2'] = $update_common['areaid_2'];
                        $update['transport_id'] = $update_common['transport_id'];
                        $update['goods_freight'] = $update_common['goods_freight'];
                        $update['goods_vat'] = $update_common['goods_vat'];
                        $update['goods_commend'] = $update_common['goods_commend'];
                        $update['goods_stcids'] = $update_common['goods_stcids'];
                        $update['goods_serial'] = $update_common['goods_serial'];

                        $update['goods_volume'] = $_POST['volume'];//体积
                        $update['goods_weight'] = $_POST['weight'];//重量
                        $update['customization'] = $_POST['iscustom'];//是否可定制
                        $update['goods_price'] = abs($value['price']);
                        $update['color_id'] = intval($value['color']);
                        $update['goods_storage'] = abs($value['sku']);
                        $update['goods_spec'] = serialize($value['sp_value']);

                        $model_goods->editGoods($update, array('goods_id' => $goods_id));
                        // 生成商品二维码
//                        $PhpQRCode->set('date', urlShop('goods', 'index', array('goods_id' => $goods_id)));
//                        $PhpQRCode->set('pngTempName', $goods_id . '.png');
//                        $PhpQRCode->init();
                    } else {
                        $insert = array();
                        $insert['goods_commonid'] = $common_id;
                        //$insert['goods_name'] = $update_common['goods_name'] . ' ' . implode(' ', $value['sp_value']);
                        $insert['goods_name'] = $update_common['goods_name'];
                        $insert['goods_jingle'] = $update_common['goods_jingle'];
                        $insert['store_id'] = $_SESSION['store_id'];
                        $insert['store_name'] = $_SESSION['store_name'];
                        $insert['gc_id'] = $update_common['gc_id'];                        
                        $insert['brand_id'] = $update_common['brand_id'];
                        $insert['goods_marketprice'] = $update_common['goods_marketprice'];
                        $insert['goods_spec'] = serialize($value['sp_value']);
                        $insert['goods_image'] = $update_common['goods_image'];
                        $insert['goods_state'] = $update_common['goods_state'];
                        $insert['goods_verify'] = $update_common['goods_verify'];
                        $insert['goods_addtime'] = $time;
                        $insert['goods_edittime'] = $time;
                        $insert['areaid_1'] = $update_common['areaid_1'];
                        $insert['areaid_2'] = $update_common['areaid_2'];
                        $insert['transport_id'] = $update_common['transport_id'];
                        $insert['goods_freight'] = $update_common['goods_freight'];
                        $insert['goods_vat'] = $update_common['goods_vat'];
                        $insert['goods_commend'] = $update_common['goods_commend'];
                        $insert['goods_stcids'] = $update_common['goods_stcids'];
                        $insert['goods_serial'] = $update_common['goods_serial'];

                        $insert['goods_volume'] = $_POST['volume'];//体积
                        $insert['goods_weight'] = $_POST['weight'];//重量
                        $insert['customization'] = $_POST['iscustom'];//是否可定制

                        $insert['goods_price'] = abs($value['price']);
                        $insert['color_id'] = intval($value['color']);
                        $insert['goods_storage'] = abs($value['sku']);
                        $insert['goods_spec'] = serialize($value['sp_value']);

                        $goods_id = $model_goods->addGoods($insert);

                        // 生成商品二维码
//                        $PhpQRCode->set('date', urlShop('goods', 'index', array('goods_id' => $goods_id)));
//                        $PhpQRCode->set('pngTempName', $goods_id . '.png');
//                        $PhpQRCode->init();
                    }
                    $goodsid_array[] = intval($goods_id);
                    $colorid_array[] = intval($value['color']);
                    $model_type->addGoodsType($goods_id, $common_id, array('cate_id' => $_POST['cate_id'], 'type_id' => $_POST['type_id'], 'attr' => $_POST['attr']));
                } 
            } else {
                //存在spec规格的情况下,并且存在common_id的情况,直接更新goods表
                $goods_info = $model_goods->getGoodsInfo(array('goods_spec' => serialize(null), 'goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']), 'goods_id');
                if (!empty($goods_info)) {
                    $goods_id = $goods_info['goods_id'];
                    $update = array();
                    $update['goods_commonid'] = $common_id;
                    $update['goods_name'] = $update_common['goods_name'];
                    $update['goods_jingle'] = $update_common['goods_jingle'];
                    $update['store_id'] = $_SESSION['store_id'];
                    $update['store_name'] = $_SESSION['store_name'];
                    $update['gc_id'] = $update_common['gc_id'];
                    $update['brand_id'] = $update_common['brand_id'];
                    $update['goods_price'] = $update_common['goods_price'];
                    $update['goods_marketprice'] = $update_common['goods_marketprice'];
                    $update['goods_serial'] = $update_common['goods_serial'];
                    $update['goods_spec'] = serialize(null);
                    $update['goods_storage'] = intval($_POST['g_storage']);
                    $update['goods_state'] = $update_common['goods_state'];
                    $update['goods_verify'] = $update_common['goods_verify'];
                    $update['goods_edittime'] = $time;
                    $update['areaid_1'] = $update_common['areaid_1'];
                    $update['areaid_2'] = $update_common['areaid_2'];
                    $update['color_id'] = 0;
                    $update['transport_id'] = $update_common['transport_id'];
                    $update['goods_freight'] = $update_common['goods_freight'];
                    $update['goods_vat'] = $update_common['goods_vat'];
                    $update['goods_commend'] = $update_common['goods_commend'];
                    $update['goods_stcids'] = $update_common['goods_stcids'];

                    $update['goods_volume'] = $_POST['volume'];//体积
                    $update['goods_weight'] = $_POST['weight'];//重量
                    $update['customization'] = $_POST['iscustom'];//是否可定制

                    $model_goods->editGoods($update, array('goods_id' => $goods_id));
                    // 生成商品二维码
//                    $PhpQRCode->set('date', urlShop('goods', 'index', array('goods_id' => $goods_id)));
//                    $PhpQRCode->set('pngTempName', $goods_id . '.png');
//                    $PhpQRCode->init();
                } else {
                    //不存在spec规格的情况下,插入新的goods表数据
                    $insert = array();
                    $insert['goods_commonid'] = $common_id;
                    $insert['goods_name'] = $update_common['goods_name'];
                    $insert['goods_jingle'] = $update_common['goods_jingle'];
                    $insert['store_id'] = $_SESSION['store_id'];
                    $insert['store_name'] = $_SESSION['store_name'];
                    $insert['gc_id'] = $update_common['gc_id'];
                    $insert['brand_id'] = $update_common['brand_id'];
                    $insert['goods_price'] = $update_common['goods_price'];
                    $insert['goods_marketprice'] = $update_common['goods_marketprice'];
                    $insert['goods_serial'] = $update_common['goods_serial'];
                    $insert['goods_spec'] = serialize(null);
                    $insert['goods_storage'] = intval($_POST['g_storage']);
                    $insert['goods_image'] = $update_common['goods_image'];
                    $insert['goods_state'] = $update_common['goods_state'];
                    $insert['goods_verify'] = $update_common['goods_verify'];
                    $insert['goods_addtime'] = $time;
                    $insert['goods_edittime'] = $time;
                    $insert['areaid_1'] = $update_common['areaid_1'];
                    $insert['areaid_2'] = $update_common['areaid_2'];
                    $insert['color_id'] = 0;
                    $insert['transport_id'] = $update_common['transport_id'];
                    $insert['goods_freight'] = $update_common['goods_freight'];
                    $insert['goods_vat'] = $update_common['goods_vat'];
                    $insert['goods_commend'] = $update_common['goods_commend'];
                    $insert['goods_stcids'] = $update_common['goods_stcids'];


                    $insert['goods_volume'] = $_POST['volume'];//体积
                    $insert['goods_weight'] = $_POST['weight'];//重量
                    $insert['customization'] = $_POST['iscustom'];//是否可定制

                    $goods_id = $model_goods->addGoods($insert);

                    // 生成商品二维码
//                    $PhpQRCode->set('date', urlShop('goods', 'index', array('goods_id' => $goods_id)));
//                    $PhpQRCode->set('pngTempName', $goods_id . '.png');
//                    $PhpQRCode->init();
                }
                $goodsid_array[] = intval($goods_id);
                $colorid_array[] = 0;
                $model_type->addGoodsType($goods_id, $common_id, array('cate_id' => $_POST['cate_id'], 'type_id' => $_POST['type_id'], 'attr' => $_POST['attr']));
            }
            // 清理商品数据
            $model_goods->delGoods(array('goods_id' => array('not in', $goodsid_array), 'goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']));
            // 清理商品图片表
            $colorid_array = array_unique($colorid_array);
            //$model_goods->delGoodsImages(array('goods_commonid' => $common_id, 'color_id' => array('not in', $colorid_array)));//修改bug 添加多规格时图片不显示
            // 更新商品默认主图
            $default_image_list = $model_goods->getGoodsImageList(array('goods_commonid' => $common_id, 'is_default' => 1), 'color_id,goods_image');
            if (!empty($default_image_list)) {
                foreach ($default_image_list as $val) {
                    $model_goods->editGoods(array('goods_image' => $val['goods_image']), array('goods_commonid' => $common_id, 'color_id' => $val['color_id']));
                }
            }
            // 商品加入上架队列
//            if (isset($_POST['starttime'])) {
//                $selltime = strtotime($_POST['starttime']) + intval($_POST['starttime_H']) * 3600 + intval($_POST['starttime_i']) * 60;
//                if ($selltime > $time) {
//                    $this->addcron(array('exetime' => $selltime, 'exeid' => $common_id, 'type' => 1), true);
//                }
//            }
            //生成体验店相关信息
//            $this->_createBrickGoods($common_id, $update_common['brick_store'],$goodsid_arr);
            // 添加操作日志
            $this->recordSellerLog('编辑商品，平台货号：' . $common_id);
            $this->jsonSucc('编辑成功!');
        } else {
            $this->jsonFail('编辑失败!');
        }

    }
    
    /**发布商品参数校验*/
    private function _checkPublishForm()
    {
        // 三方店铺验证是否绑定了该分类

        $rs = false;
        //$goods_class = H('goods_class') ? H('goods_class') : H('goods_class', true);
        $goods_class = H('goods_class');
        if (!empty($goods_class)){
            foreach ($goods_class as $k => $v){
                if (intval($_POST['gc_id']) == $k){
                    $rs = true;
                    break;
                }
            }
        }
        if (!$rs) {
            $this->jsonFail('分类不存在！');
        }
 

        //检验post参数
        if (empty($_POST['g_name'])) {
            $this->jsonFail('请填写商品名称!');
        }//商品名称不能为空,且长度不能超过30个字符
        if (mb_strlen($_POST['g_name'], 'utf-8') > 30) {
            $this->jsonFail('商品名称不能超过30个字符!');
        }
        //宣传语不能超过50个字符
        if (mb_strlen($_POST['g_jingle'], 'utf-8') > 40) {
            $this->jsonFail('广告语不能超过40个字符!');
        }
        //分类id
        if (!intval($_POST['gc_id'])) {
            $this->jsonFail('分类id不存在!');
        }
        //分类名
        if (!$_POST['gc_name']) {
            $this->jsonFail('分类名不存在!');
        }
        $_POST['b_id'];//品牌id
        $_POST['b_name'];//品牌名字
        intval($_POST['type_id']);//类型id

        //图片
        if (!$_POST['goods_images']) {
            $this->jsonFail('请上传图片!');
        }
        //商品价格
        if (!floatval($_POST['g_price']) || floatval($_POST['g_price']) <= 0) {
            $this->jsonFail('商品价格不正确!');
        }
        //市场价 是工厂发布就跳过此验证
        if ((!floatval($_POST['g_marketprice']) || floatval($_POST['g_marketprice']) <= 0)&&$_SESSION['com_type']!=3) {
            $this->jsonFail('市场价格不正确!');
        }
        //成本价
        if (!floatval($_POST['g_costprice']) || floatval($_POST['g_costprice']) <= 0) {
            $this->jsonFail('成本价格不正确!');
        }
        //体积
        if (floatval($_POST['volume']) < 0) {
            $this->jsonFail('体积不正确！');
        }
        //重量
        if (floatval($_POST['weight']) < 0) {
            $this->jsonFail('重量不正确!');
        }
        //是否可定制
        if ($_POST['iscustom'] != 0 && $_POST['iscustom'] != 1) {
            $this->jsonFail('请选择是否可定制!');
        }
        //是否推荐
        if ($_POST['iscommend'] != 0 && $_POST['iscommend'] != 1) {
            $this->jsonFail('请选择是否推荐!');
        }
        //运费模版
        if (intval($_POST['freight']) < 0) {
            $this->jsonFail('请选择运费模版!');
        }
    }
    
    /****
     * 通过commonid和brickstore_id插入体验店相关商品
     */
    private function _createBrickGoods($commonid, $brickstore_id,$delgoodsid=array())
    {   
        //如果存在体验店信息,则生成体验店相关
        $brickstore_model = D("BrickStore");
        $b_condition['brickstore_id'] = $brickstore_id;
        $b_condition['store_id'] = $_SESSION['store_id'];
        $list=$brickstore_model->getAllBrickStore($b_condition);    
        if ($list) {//判断体验店是否存在
            $goods_model = D('Goods');
            $condition['goods_commonid'] = $commonid; 
            $condition['cache'] = false;//不能缓存数据
            $goods_id = $goods_model->getGoodsList($condition, 'goods_id');
            $brick_store_goodsModel=Model("brick_store_goods");//查询体验店和商品关联的记录
            $brick_store=$brick_store_goodsModel->where(array("brick_store_id"=>$brickstore_id))->find();//查找体验店
            $brick_store1=$brick_store_goodsModel->query("select * from allwood_brick_store_goods g where g.brick_store_id!='{$brickstore_id}' and goods_id like '%{$goods_id}%'");//查找体验店
            if($brick_store){//如果存在体验店则更新
                $info=explode(",",$brick_store["goods_id"]);//将字符串的数组切割转化为数组
               
                $flag=false;//不更新
                 foreach ($goods_id as $k => $v) {
                    if(in_array( $v['goods_id'],$info)){//如果在原有的体验店的商品列表里面有则不作处理
                        continue;
                    }else{
                        array_push($info,$v['goods_id']);
                        $flag=true;//更新
                    } 
                }
                if(count($delgoodsid)){
                    $flag=true;
                }
                if($flag){//如果有更新
                    $data=array();
                    if(count($delgoodsid)){
                        foreach ($delgoodsid as $va){
                            $key=array_search($va, $info);
                            if($key){//如果在原有的体验店的商品列表里面有则不作处理
                                unset($info[$key]);
                            } 
                        }
                    }
                    
                    $data["goods_id"]=implode(",",$info);
                    $brick_store_goodsModel->where(array("brick_store_id"=>$brickstore_id))->update($data);//只是更新商品id
                }
            }else{//插入
                $goodsidinfo=implode(",",$goods_id);
                $data=array();
                $data["goods_id"]=$goodsidinfo;
                $data["brick_store_id"]=$list[0]["brick_store_id"];
                $data["brick_store_name"]=$list[0]["brick_store_name"];
                $data["brick_store_phone"]=$list[0]["brickstore_phone"];
                $data["brick_store_address"]=$list[0]["brickstore_address"];
                $brick_store_goodsModel->insert($data);
            }
            if(is_array($brick_store1)&& count($brick_store1)){//如果存在商品以前在另一个体验店，则需要修改删除以前的信息
                $goodsinfo=$brick_store1[0]["goods_id"];
                $goodsinfo_array=explode(",",$goodsinfo);//将字符串的数组切割转化为数组
                foreach ($goods_id as $k =>$v) {
                    $key=array_search($v['goods_id'], $goodsinfo_array);
                    if($key){//如果在原有的体验店的商品列表里面有则不作处理
                        unset($goodsinfo_array[$key]);
                    }
                }
                $data=array();
                $data["goods_id"]=implode(",",$goodsinfo_array);
                $brick_store_goodsModel->where(array("brick_store_id"=>$brick_store1[0]["brick_store_id"]))->update($data);//只是更新商品id
            }
        }
        
    }

    
    /**商品上传和编辑的图片处理**/
    private function _handlerGoodsImg()
    {
        $img_path_oris = $_POST['goods_images'];//post数组
        $img_path_ori = [];//处理后的数组
        //判断图片路径对图片文件名称进行替换
        if (!is_array($img_path_oris)) return false;
        foreach ($img_path_oris as $k => $v) {
            //对每张图片的名字进行处理
            $img_path = explode('/', $v['m_pic']);
            if (is_array($img_path)) {
                $n_path = $img_path[count($img_path) - 1];//图片名
                if ($v['fengmian'] == 1) {
                    array_unshift($img_path_ori, $n_path);//返回默认图片的数据
                } else {
                    $img_path_ori[$k] = $n_path;
                }
            } else {
                return false;
            }
        }
        return $img_path_ori;
    }

    /***商品图片存储*/
    private function _saveGoodsImg($img_path_ori, $commonid, $store_id, $del_before = false)
    {
        if (is_array($img_path_ori)) {
            //切割成功
            //存入店铺相关图片信息
            $model_images = D('GoodsImages');
            $data = [];
            foreach ($img_path_ori as $k => $v) {
                $data[$k]['goods_commonid'] = $commonid;
                $data[$k]['store_id'] = $store_id;
                $data[$k]['color_id'] = 0;
                $data[$k]['goods_image'] = $v;
                $data[$k]['goods_image_sort'] = 0;
                $data[$k]['is_default'] = 0;
            }
            if ($del_before !== false) {
                //需要先删除之前的所有图片
                $d_condition['store_id'] = $store_id;
                $d_condition['goods_commonid'] = $commonid;
                $det = $model_images->delImages($d_condition);
                if (!$det) {
                    return false;
                }
            }

            $res = $model_images->insertImage($data);
            if ($res) {
                return $img_path_ori[0];//返回第一个数据
            }
        }
        {
            return false;
        }
    }
    
    /**商品上传成功,增加用户常用分类**/
    private function _addStapleClass($gc_id)
    {
        $model_goodsclass = D('GoodsClass');
        // 验证商品分类是否存在且商品分类是否为最后一级
        //$data = H('goods_class') ? H('goods_class') : H('goods_class', true);
        $data = H('goods_class');
        if (!isset($data[$gc_id]) || isset($data[$gc_id]['child']) || isset($data[$gc_id]['childchild'])) {
            return false;
        }
        // 更新常用分类信息
        $goods_class = $model_goodsclass->getGoodsClassLineForTag($gc_id);
        $res = D('GoodsClassStaple')->autoIncrementStaple($goods_class, $_SESSION['member_id']);
        if ($res) {
            return true;
        } else {
            return false;
        }

    }
    
    /**
     * AJAX添加商品规格值
     */
    public function ajaxAddSpec()
    {
        $name = trim($_REQUEST['name']);
        $gc_id = intval($_REQUEST['gc_id']);
        $sp_id = intval($_REQUEST['sp_id']);
        if ($name == '' || $gc_id <= 0 || $sp_id <= 0) {
            $this->jsonFail('添加失败,参数不合法!');
        }
        $insert = array(
            'sp_value_name' => $name,
            'sp_id' => $sp_id,
            'gc_id' => $gc_id,
            'store_id' => $_SESSION['store_id'],
            'sp_value_color' => null,
            'sp_value_sort' => 0,
        );
        $value_id = D('Spec')->addSpecValue($insert);
        if ($value_id) {
            $this->jsonArr(array("value_id" => $value_id));
        } else {
            $this->jsonFail('添加失败!');
        }
    }

    /**
     * 编辑商品
     * @return [type] [description]
     */
    public function editGoods()
    {
        $common_id = $_GET['commonid'];
        if ($common_id <= 0) {
            $this->error('参数错误');
        }
        $model_goods = D('Goods');
        $where = array('goods_commonid' => $common_id, 'store_id' => $_SESSION['store_id']);
        $goodscommon_info = $model_goods->getGoodeCommonInfo($where);

        if (empty($goodscommon_info)) {
            $this->error('参数错误');
        }
        $edit = true;

        $goodscommon_info['g_storage'] = $model_goods->getGoodsSum($where, 'goods_storage');
        $goodscommon_info['spec_name'] = unserialize($goodscommon_info['spec_name']);
        $this->assign('goods', $goodscommon_info);
        if (intval($_GET['class_id']) > 0) {
            $goodscommon_info['gc_id'] = intval($_GET['class_id']);
        }
        $goods_class = D('GoodsClass')->getGoodsClassLineForTag($goodscommon_info['gc_id']);
        $this->assign('goods_class', $goods_class);

        $model_type = D('Type');
        // 获取类型相关数据
        $goods_class1 = D('GoodsClass')->getOneGoodsClass($goodscommon_info['gc_id']);
        $typeinfo = $this->getAttributeList($goods_class1,$goodscommon_info['gc_id']);
        list($spec_list, $attr_list, $brand_list) = $typeinfo;
        $this->assign('spec_json', $spec_json);
        $this->assign('sign_i', count($spec_list));
        $this->assign('spec_list', $spec_list);
        $this->assign('attr_list', $attr_list);
        $this->assign('brand_list', $brand_list);

        //获取商家运费模版
        $transport_model = D('Transport');
        $conditon_transport['store_id'] = $_SESSION['store_id'];
        $transport = $transport_model->getAllTransportInfo($conditon_transport);
        $this->assign('transport', $transport);
        //获取商家体验店信息
        $brick_model = D('BrickStore');
        $conditon_brickstore['store_id'] = $_SESSION['store_id'];
        $brick_store = $brick_model->getAllBrickStore($conditon_brickstore);
        $this->assign('brickstore', $brick_store);

        // 取得商品规格的输入值
        $goods_array = $model_goods->getGoodsList($where, 'goods_id, goods_price,goods_storage,goods_serial,goods_spec');


        $sp_value = array();
        if (is_array($goods_array) && !empty($goods_array)) {
            // 取得已选择了哪些商品的属性
            $attr_checked_l = $model_type->typeRelatedList('goods_attr_index', array(
                'goods_id' => intval($goods_array[0]['goods_id'])
            ), 'attr_value_id');
            if (is_array($attr_checked_l) && !empty ($attr_checked_l)) {
                $attr_checked = array();
                foreach ($attr_checked_l as $val) {
                    $attr_checked [] = $val ['attr_value_id'];
                }
            }
            $this->assign('attr_checked', $attr_checked);

            $spec_checked = array();
            $sp_values = [];
            foreach ($goods_array as $k => $v) {
                $a = unserialize($v['goods_spec']);

                if (!empty($a)) {
                    $n = [];
                    $sp_id = isset($spec_list['1']['value']) && !empty($spec_list['1']['value']) ? $spec_list['1']['value'] : [];
                    $sp_ids = [];
                    if (is_array($sp_id) && !empty($sp_id)) {
                        foreach ($sp_id as $kid => $vid) {
                            $sp_ids[$v['sp_value_id']] = $v['sp_value_id'];
                        }
                    }
                    foreach ($a as $key => $val) {
                        $spec_checked[$key]['id'] = $key;
                        $spec_checked[$key]['name'] = $val;
                        $n[$key]['id'] = $key;
                        $n[$key]['name'] = $val;
                        if (array_key_exists($key, $sp_ids)) {
                            $n[$key]['sp_id'] = 1;
                        } else {
                            $n[$key]['sp_id'] = 0;
                        }
                    }
                    $matchs = array_keys($a);
                    sort($matchs);
                    $id = str_replace(',', '', implode(',', $matchs));
                    $sp_value ['i_' . $id . '|price'] = $v['goods_price'];
                    $sp_value ['i_' . $id . '|id'] = $v['goods_id'];
                    $sp_value ['i_' . $id . '|stock'] = $v['goods_storage'];
                    $sp_value ['i_' . $id . '|sku'] = $v['goods_storage'];

                    $sp_values ['i_' . $id]['price'] = $v['goods_price'];
                    $sp_values ['i_' . $id]['id'] = $v['goods_id'];
                    $sp_values ['i_' . $id]['stock'] = $v['goods_storage'];
                    $sp_values ['i_' . $id]['vl'] = $n;
                }
            }
            $this->assign('spec_checked', $spec_checked);//已经选中的规格
        }
        $this->assign('sp_value', json_encode($sp_value));//选中的商品参数
        $this->assign('sp_values', $sp_values);//选中的商品参数


        // 实例化店铺商品分类模型
        $store_goods_class = Model('my_goods_class')->getClassTree(array(
            'store_id' => $_SESSION ['store_id'],
            'stc_state' => '1'
        ));
        $this->assign('store_goods_class', $store_goods_class);
        $goodscommon_info['goods_stcids'] = trim($goodscommon_info['goods_stcids'], ',');
        $this->assign('store_class_goods', explode(',', $goodscommon_info['goods_stcids']));

        //根据commonid获取所有图片
        $model_image = D('GoodsImages');
        $condition_image['goods_commonid'] = $goodscommon_info['goods_commonid'];
        $commonid_img = $model_image->getImages($condition_image);
        foreach ($commonid_img as $k => $v) {
            $commonid_img[$k]['goods_image_origin'] = cthumb_nohost($v['goods_image'],'',$_SESSION ['store_id']);
            $commonid_img[$k]['goods_image_thumb'] = cthumb_nohost($v['goods_image'], 240,$_SESSION ['store_id']);
            if ($v['goods_image'] == $goodscommon_info['goods_image']) {
                $commonid_img[$k]['checked'] = 'checked';
            } else {
                $commonid_img[$k]['checked'] = 'no';
            }
        }
        $this->assign('goods_imgs', $commonid_img);
        // 是否能使用编辑器
//        if (checkPlatformStore()) { // 平台店铺可以使用编辑器
//            $editor_multimedia = true;
//        } else {    // 三方店铺需要
            $editor_multimedia = false;
            if ($this->store_grade['sg_function'] == 'editor_multimedia') {
                $editor_multimedia = true;
            }
//        }
        $this->assign('editor_multimedia', $editor_multimedia);

        // 小时分钟显示
        $hour_array = array('00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23');
        $this->assign('hour_array', $hour_array);
        $minute_array = array('05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55');
        $this->assign('minute_array', $minute_array);

        // 关联版式
        $plate_list = D('StorePlate')->getPlateList(array('store_id' => $_SESSION['store_id']), 'plate_id,plate_name,plate_position');
        $plate_list = array_under_reset($plate_list, 'plate_position', 2);
        $this->assign('plate_list', $plate_list);

        $this->assign('edit_goods_sign', true);
        $this->assign('edit', $edit);

        $this->display();
    }
    
    public function goodsManage(){
        $this->display();
    }

    /*
     * 物流工具
     */
    public function transportManage(){
        //根据store_id读取卖家的相关物流模版
        $transport_model = D('Transport');
        $condition['store_id'] = $_SESSION['store_id'];
        $allTransport = $transport_model->getAllTransportInfo($condition);
        $ids = [];
        foreach ($allTransport as $k => $v) {
            $ids[] = $v['id'];
        }
        $condition_extend['transport_id'] = array('in', implode(',', $ids));
        $allTransportExtend = $transport_model->getAllExtendInfo($condition_extend);
        //将模版和模版拓展信息组合
        foreach ($allTransport as $k => $v) {
            $allTransport[$k]['extend'] = [];
            foreach ($allTransportExtend as $k2 => $v2) {
                if ($v2['transport_id'] == $v['id']) {
                    array_push($allTransport[$k]['extend'], $allTransportExtend[$k2]);
                }
            }
        }
        $this->assign('transport', $allTransport);
        $this->display();
    }
    /*
     * 添加/编辑运费模板
     */
    public function editTemplate()
    {
        $store_id = $_SESSION['store_id'];
        $transport_id = $_REQUEST['transport_id'];
        //获取模版相关的数据
        $transport_model = D('Transport');
        $condition['store_id'] = $store_id;
        $condition['id'] = $transport_id;
        $transport = $transport_model->getTransportInfo($condition);
        //获取模版拓展信息
        $condition_extend['transport_id'] = $transport_id;
        $transport_extend = $transport_model->getExtendInfo($condition_extend);
        $express = false;
        $logistics = false;
        foreach ($transport_extend as $k => $v) {
            //检查transport_type是否包含物流和快递
            if ($v['transport_type'] == '2') {
                $express = true;
            }
            if ($v['transport_type'] == '1') {
                $logistics = true;
            }
        }
        $this->assign('express', $express);
        $this->assign('logistics', $logistics);
        $this->assign('transport', $transport);
        $this->assign('transport_extend', $transport_extend);
        $this->display();
    }
    
    /***
     * 通过transport_id删除模版
     */
    public function delTransport()
    {
        $transport_id = $_POST['id'];
        $store_id = $_SESSION['store_id'];
        $transport_model = D('Transport');
        $condition['id'] = $transport_id;
        $condition['store_id'] = $store_id;
        $del = $transport_model->delTansport($condition);
        if ($del) {
            $this->jsonSucc('删除成功!');
        } else {
            $this->jsonFail('删除失败,请重试!');
        }
    }
    
    /**
     * 新建物流模版接口
     * @return [type] [description]
     */
    public function createTransportAjax()
    {
        //定义参数
        $data = $_POST;
        $datas['title'] = $data['title'];//模版名称
        $datas['store_id'] = $_SESSION['store_id'];//店铺id
        $datas['freightage_type'] = $data['freightage_type'];//运费类型 0自定义, 1卖家承担
        $datas['update_time'] = time();//最后操作时间
        $datas['tesu_created'] = time();//创建时间
        $datas['province_id'] = $data['province_id'];
        $datas['area_id'] = $data['area_id'];
        $datas['city_id'] = $data['city_id'];
        $datas['cash_type'] = $data['cash_type'];//计价方式  体积 重量
        $transport_id = intval($data['transport_id']);
        //对参数进行校验
        //判断运费类型
        $transport_model = D('Transport');
        if ($transport_id > 0) {
            //校验模板是否属于该用户
            $t_conditon['store_id'] = $_SESSION['store_id'];
            $t_conditon['transport_id'] = $transport_id;
            $t_check = $transport_model->getTransportInfo($transport_id);
            if (!$t_check) {
                $this->jsonFail('模板不存在!');
            }
        }
        //判断是否存在运送方式
        if ($data['transport_type'] == '1' || $data['transport_type'] == '2' || $data['transport_type'] == '3' || $data['freightage_type'] == '1') {
        } else {
            $this->jsonFail('请选择运送方式!');
        }

        if ($datas['freightage_type'] == 1) {
            if (empty($transport_id)) {
                //此时卖家承担运费,不需要继续添加模版
                unset($datas['cash_type']);
                foreach ($datas as $k => $v) {
                    if ($v == '') {
                        //循环参数,如果存在空值返回
                        $this->jsonFail('参数异常!');
                    }
                }
                //将获取的数据存入数据库
                $res = $transport_model->addTransport($datas);
                if ($res) {
                    $data_add['id'] = $res;
                    $data_add['title'] = $data['title'];
                    $this->jsonArr('添加成功!', $data_add);
                } else {
                    $this->jsonFail('添加失败,请重试!');
                }
            } else {
                //编辑时更新主模板信息
                $trans_info['id'] = $transport_id;
                $trans_info['title'] = $datas['title'];
                $trans_info['update_time'] = $datas['update_time'];
                $trans_info['province_id'] = $datas['province_id'];
                $trans_info['city_id'] = $datas['city_id'];
                $trans_info['area_id '] = $datas['area_id'];
                $trans_info['cash_type'] = $datas['cash_type'];
                $trans_info['freightage_type'] = $datas['freightage_type'];
                //更新模板,并删除所有拓展模板
                $transport_model->transUpdate($trans_info) && $transport_model->delExtend($transport_id);
                 $this->jsonSucc('更新成功!');
                
            }
        } else {
            //此时按照自定义模版进行设置运费
            foreach ($datas as $k => $v) {
                if ($v == '') {
                    $this->jsonFail('参数异常!');
                }
            }
            if (empty($transport_id)) {
                //开启事务,如果不存在模板,则新生成一个模板
                try {
                    $transport_model->startTrans();
                    $transport_model->addTransport($datas);
                    $transport_id = $transport_model->getLastInsID();//获取最近一次插入的id
                    if ($transport_id) {
                        if ($this->_create_extend_tpl($transport_id)) {
                            //添加成功
                            $transport_model->commit();
                            $data_add['id'] = $transport_id;
                            $data_add['title'] = $data['title'];
                            $this->jsonArr('添加成功!', $data_add);
                        } else {
                            $transport_model->rollback();
                            $this->jsonFail('添加失败,请重试!');
                        }
                    } else {
                        $transport_model->rollback();
                        $this->jsonFail('新增失败,请重试!');
                    }
                } catch (\Exception $e) {
                    $transport_model->rollback();
                    $this->jsonFail('新增异常,请重试!');
                }
            } else {
                try {
                    $transport_model->startTrans();
                    //编辑时更新主模板信息
                    $trans_info['id'] = $transport_id;
                    $trans_info['title'] = $datas['title'];
                    $trans_info['update_time'] = $datas['update_time'];
                    $trans_info['province_id'] = $datas['province_id'];
                    $trans_info['city_id'] = $datas['city_id'];
                    $trans_info['area_id '] = $datas['area_id'];
                    $trans_info['cash_type'] = $datas['cash_type'];
                    $trans_info['freightage_type'] = $datas['freightage_type'];
                    if (!$transport_model->transUpdate($trans_info)) {
                        $transport_model->rollback();
                        $this->jsonFail('更新失败,请重试!');
                    }
                    if (!$transport_model->delExtend($transport_id)) {
                        $transport_model->rollback();
                        $this->jsonFail('更新失败,请重试!');

                    }
                    //修改模板id数据
                    if ($this->_create_extend_tpl($transport_id)) {
                        //添加成功
                        $transport_model->commit();
                        $this->jsonSucc('更新成功!');
                    } else {
                        $transport_model->rollback();
                        $this->jsonFail('更新失败,请重试!');
                    }
                } catch (\Exception $e) {
                    $transport_model->rollback();
                    $this->jsonFail('更新失败,请重试!');
                }

            }
        }
    }
    
    /****
     * 运费extend模板生成方法
     */
    private function _create_extend_tpl($transport_id)
    {
        //检验数据是否有效
        $transport_model = D('Transport');
        //检查是否存在transport_id,并且属于store_id
        $store_id = $_SESSION['store_id'];
        $condition['store_id'] = $store_id;
        $condition['id'] = $transport_id;
        $verify = $transport_model->getTransportInfo($condition);
        if (empty($verify)) {
            $this->jsonFail('数据异常,操作失效,请重试!');
        }
        //校验通过,通过post数据进行生成拓展信息
        //保存默认运费
        if (is_array($_POST['default']['kd'])) {
            $a = $_POST['default']['kd'];
            $trans_list[0]['area_id'] = '';
            $trans_list[0]['area_name'] = '全国';
            $trans_list[0]['snum'] = $a['start'];
            $trans_list[0]['sprice'] = $a['postage'];
            $trans_list[0]['xnum'] = $a['plus'];
            $trans_list[0]['xprice'] = $a['postageplus'];
            $trans_list[0]['is_default'] = 1;
            $trans_list[0]['transport_id'] = $transport_id;
            $trans_list[0]['transport_title'] = $_POST['title'];
            $trans_list[0]['top_area_id'] = '';
            $trans_list[0]['transport_type'] = '2';

        }
        //保存自定义地区的运费设置
        $areas = $_POST['areas']['kd'];
        $special = $_POST['special']['kd'];
        if (is_array($areas) && is_array($special)) {
            //$key需要加1，因为快递默认运费占了第一个下标
            foreach ($special as $key => $value) {
                if (empty($areas[$key])) continue;
                $areas[$key] = explode('|||', $areas[$key]);
                $trans_list[$key + 1]['area_id'] = ',' . $areas[$key][0] . ',';
                $trans_list[$key + 1]['area_name'] = $areas[$key][1];
                $trans_list[$key + 1]['snum'] = $value['start'];
                $trans_list[$key + 1]['sprice'] = $value['postage'];
                $trans_list[$key + 1]['xnum'] = $value['plus'];
                $trans_list[$key + 1]['xprice'] = $value['postageplus'];
                $trans_list[$key + 1]['is_default'] = 2;
                $trans_list[$key + 1]['transport_id'] = $transport_id;
                $trans_list[$key + 1]['transport_title'] = $_POST['title'];
                //计算省份ID
                $province = array();
                $tmp = explode(',', $areas[$key][0]);
                if (!empty($tmp) && is_array($tmp)) {
                    $city = $this->_getCity();
                    foreach ($tmp as $t) {
                        $pid = $city[$t];
                        if (!in_array($pid, $province) && !empty($pid)) $province[] = $pid;
                    }
                }
                if (count($province) > 0) {
                    $trans_list[$key + 1]['top_area_id'] = ',' . implode(',', $province) . ',';
                } else {
                    $trans_list[$key + 1]['top_area_id'] = '';
                }
                $trans_list[$key + 1]['transport_type'] = '2';
            }
        }

        //保存默认运费
        if (is_array($_POST['default']['wl'])) {
            $a = $_POST['default']['wl'];
            $trans_list_wl[0]['area_id'] = '';
            $trans_list_wl[0]['area_name'] = '全国';
            $trans_list_wl[0]['snum'] = $a['start'];
            $trans_list_wl[0]['sprice'] = $a['postage'];
            $trans_list_wl[0]['xnum'] = $a['plus'];
            $trans_list_wl[0]['xprice'] = $a['postageplus'];
            $trans_list_wl[0]['is_default'] = 1;
            $trans_list_wl[0]['transport_id'] = $transport_id;
            $trans_list_wl[0]['transport_title'] = $_POST['title'];
            $trans_list_wl[0]['top_area_id'] = '';
            $trans_list_wl[0]['transport_type'] = '1';

        }
        //保存自定义地区的运费设置
        $areas = $_POST['areas']['wl'];
        $special = $_POST['special']['wl'];
        if (is_array($areas) && is_array($special)) {
            //$key需要加1，因为快递默认运费占了第一个下标
            foreach ($special as $key => $value) {
                if (empty($areas[$key])) continue;
                $areas[$key] = explode('|||', $areas[$key]);
                $trans_list_wl[$key + 1]['area_id'] = ',' . $areas[$key][0] . ',';
                $trans_list_wl[$key + 1]['area_name'] = $areas[$key][1];
                $trans_list_wl[$key + 1]['snum'] = $value['start'];
                $trans_list_wl[$key + 1]['sprice'] = $value['postage'];
                $trans_list_wl[$key + 1]['xnum'] = $value['plus'];
                $trans_list_wl[$key + 1]['xprice'] = $value['postageplus'];
                $trans_list_wl[$key + 1]['is_default'] = 2;
                $trans_list_wl[$key + 1]['transport_id'] = $transport_id;
                $trans_list_wl[$key + 1]['transport_title'] = $_POST['title'];
                //计算省份ID
                $province = array();
                $tmp = explode(',', $areas[$key][0]);
                if (!empty($tmp) && is_array($tmp)) {
                    $city = $this->_getCity();
                    foreach ($tmp as $t) {
                        $pid = $city[$t];
                        if (!in_array($pid, $province) && !empty($pid)) $province[] = $pid;
                    }
                }
                if (count($province) > 0) {
                    $trans_list_wl[$key + 1]['top_area_id'] = ',' . implode(',', $province) . ',';
                } else {
                    $trans_list_wl[$key + 1]['top_area_id'] = '';
                }
                $trans_list_wl[$key + 1]['transport_type'] = '1';
            }
        }

        if ($_POST['transport_type'] == 1) {
            return $transport_model->addExtend($trans_list_wl);
        }
        if ($_POST['transport_type'] == 2) {
            return $transport_model->addExtend($trans_list);
        }
        if ($_POST['transport_type'] == 3) {
            $r1 = $transport_model->addExtend($trans_list);
            $r2 = $transport_model->addExtend($trans_list_wl);
            return ($r1 && $r2);
        }
        return false;


    }
    
    /**
     * 返回 市ID => 省ID 对应关系数组
     *
     * @return array
     */
    private function _getCity()
    {
        return array(36 => 1, 39 => 9, 40 => 2, 62 => 22, 73 => 3, 74 => 3, 75 => 3, 76 => 3, 77 => 3, 78 => 3, 79 => 3, 80 => 3, 81 => 3, 82 => 3, 83 => 3, 84 => 4, 85 => 4, 86 => 4, 87 => 4, 88 => 4, 89 => 4, 90 => 4, 91 => 4, 92 => 4, 93 => 4, 94 => 4, 95 => 5, 96 => 5, 97 => 5, 98 => 5, 99 => 5, 100 => 5, 101 => 5, 102 => 5, 103 => 5, 104 => 5, 105 => 5, 106 => 5, 107 => 6, 108 => 6, 109 => 6, 110 => 6, 111 => 6, 112 => 6, 113 => 6, 114 => 6, 115 => 6, 116 => 6, 117 => 6, 118 => 6, 119 => 6, 120 => 6, 121 => 7, 122 => 7, 123 => 7, 124 => 7, 125 => 7, 126 => 7, 127 => 7, 128 => 7, 129 => 7, 130 => 8, 131 => 8, 132 => 8, 133 => 8, 134 => 8, 135 => 8, 136 => 8, 137 => 8, 138 => 8, 139 => 8, 140 => 8, 141 => 8, 142 => 8, 162 => 10, 163 => 10, 164 => 10, 165 => 10, 166 => 10, 167 => 10, 168 => 10, 169 => 10, 170 => 10, 171 => 10, 172 => 10, 173 => 10, 174 => 10, 175 => 11, 176 => 11, 177 => 11, 178 => 11, 179 => 11, 180 => 11, 181 => 11, 182 => 11, 183 => 11, 184 => 11, 185 => 11, 186 => 12, 187 => 12, 188 => 12, 189 => 12, 190 => 12, 191 => 12, 192 => 12, 193 => 12, 194 => 12, 195 => 12, 196 => 12, 197 => 12, 198 => 12, 199 => 12, 200 => 12, 201 => 12, 202 => 12, 203 => 13, 204 => 13, 205 => 13, 206 => 13, 207 => 13, 208 => 13, 209 => 13, 210 => 13, 211 => 13, 212 => 14, 213 => 14, 214 => 14, 215 => 14, 216 => 14, 217 => 14, 218 => 14, 219 => 14, 220 => 14, 221 => 14, 222 => 14, 223 => 15, 224 => 15, 225 => 15, 226 => 15, 227 => 15, 228 => 15, 229 => 15, 230 => 15, 231 => 15, 232 => 15, 233 => 15, 234 => 15, 235 => 15, 236 => 15, 237 => 15, 238 => 15, 239 => 15, 240 => 16, 241 => 16, 242 => 16, 243 => 16, 244 => 16, 245 => 16, 246 => 16, 247 => 16, 248 => 16, 249 => 16, 250 => 16, 251 => 16, 252 => 16, 253 => 16, 254 => 16, 255 => 16, 256 => 16, 257 => 16, 258 => 17, 259 => 17, 260 => 17, 261 => 17, 262 => 17, 263 => 17, 264 => 17, 265 => 17, 266 => 17, 267 => 17, 268 => 17, 269 => 17, 270 => 17, 271 => 17, 272 => 17, 273 => 17, 274 => 17, 275 => 18, 276 => 18, 277 => 18, 278 => 18, 279 => 18, 280 => 18, 281 => 18, 282 => 18, 283 => 18, 284 => 18, 285 => 18, 286 => 18, 287 => 18, 288 => 18, 289 => 19, 290 => 19, 291 => 19, 292 => 19, 293 => 19, 294 => 19, 295 => 19, 296 => 19, 297 => 19, 298 => 19, 299 => 19, 300 => 19, 301 => 19, 302 => 19, 303 => 19, 304 => 19, 305 => 19, 306 => 19, 307 => 19, 308 => 19, 309 => 19, 310 => 20, 311 => 20, 312 => 20, 313 => 20, 314 => 20, 315 => 20, 316 => 20, 317 => 20, 318 => 20, 319 => 20, 320 => 20, 321 => 20, 322 => 20, 323 => 20, 324 => 21, 325 => 21, 326 => 21, 327 => 21, 328 => 21, 329 => 21, 330 => 21, 331 => 21, 332 => 21, 333 => 21, 334 => 21, 335 => 21, 336 => 21, 337 => 21, 338 => 21, 339 => 21, 340 => 21, 341 => 21, 342 => 21, 343 => 21, 344 => 21, 385 => 23, 386 => 23, 387 => 23, 388 => 23, 389 => 23, 390 => 23, 391 => 23, 392 => 23, 393 => 23, 394 => 23, 395 => 23, 396 => 23, 397 => 23, 398 => 23, 399 => 23, 400 => 23, 401 => 23, 402 => 23, 403 => 23, 404 => 23, 405 => 23, 406 => 24, 407 => 24, 408 => 24, 409 => 24, 410 => 24, 411 => 24, 412 => 24, 413 => 24, 414 => 24, 415 => 25, 416 => 25, 417 => 25, 418 => 25, 419 => 25, 420 => 25, 421 => 25, 422 => 25, 423 => 25, 424 => 25, 425 => 25, 426 => 25, 427 => 25, 428 => 25, 429 => 25, 430 => 25, 431 => 26, 432 => 26, 433 => 26, 434 => 26, 435 => 26, 436 => 26, 437 => 26, 438 => 27, 439 => 27, 440 => 27, 441 => 27, 442 => 27, 443 => 27, 444 => 27, 445 => 27, 446 => 27, 447 => 27, 448 => 28, 449 => 28, 450 => 28, 451 => 28, 452 => 28, 453 => 28, 454 => 28, 455 => 28, 456 => 28, 457 => 28, 458 => 28, 459 => 28, 460 => 28, 461 => 28, 462 => 29, 463 => 29, 464 => 29, 465 => 29, 466 => 29, 467 => 29, 468 => 29, 469 => 29, 470 => 30, 471 => 30, 472 => 30, 473 => 30, 474 => 30, 475 => 31, 476 => 31, 477 => 31, 478 => 31, 479 => 31, 480 => 31, 481 => 31, 482 => 31, 483 => 31, 484 => 31, 485 => 31, 486 => 31, 487 => 31, 488 => 31, 489 => 31, 490 => 31, 491 => 31, 492 => 31, 493 => 32, 494 => 32, 495 => 32, 496 => 32, 497 => 32, 498 => 32, 499 => 32, 500 => 32, 501 => 32, 502 => 32, 503 => 32, 504 => 32, 505 => 32, 506 => 32, 507 => 32, 508 => 32, 509 => 32, 510 => 32, 511 => 32, 512 => 32, 513 => 32, 514 => 32, 515 => 32, 516 => 33, 517 => 33, 518 => 33, 519 => 33, 520 => 33, 521 => 33, 522 => 33, 523 => 33, 524 => 33, 525 => 33, 526 => 33, 527 => 33, 528 => 33, 529 => 33, 530 => 33, 531 => 33, 532 => 33, 533 => 33, 534 => 34, 45055 => 35);
    }
    
    /**
     * 在售/仓库中的商品
     * @return [type] [description]
     */
    public function getGoodsList(){
        $model_goods = D('Goods');
        // 商品分类
        $store_goods_class = D('MyGoodsClass')->getClassTree(array('store_id' => session('store_id'), 'stc_state' => '1'));
        $this->assign('store_goods_class', $store_goods_class);

        if ($_POST) {
            $type = I('post.type/s');
            $pageSize = I('post.pagesize');
            $page = I('post.curpage/d');//当前页
            $startPage = ($page - 1) * $pageSize;

            $where['goods_common.store_id'] = session('store_id');
            if (I('post.stc_id/d') > 0) {
                $where['goods_common.goods_stcids'] = array('like', '%' . I('post.stc_id/d') . '%');
            }
            if (I('post.goods_name/s') != '') {
                $where['goods_common.goods_name'] = array('like', '%' . I('post.goods_name/s') . '%');
            }
            if (I('post.goods_serial/s') != '') {
                $where['goods_common.goods_serial'] = array('like', '%' . I('post.goods_serial/s') . '%');
            }
            if (I('post.f_price/s') != '' || I('post.l_price/s') != '') {
                $f_price = I('post.f_price/s') != '' ? I('post.f_price/s') : 0;
                $l_price = I('post.l_price/s') != '' ? I('post.l_price/s') : 0;
                $where['goods_common.goods_price'] = array('between', array($f_price, $l_price));
            }
            if (I('post.s_salenum/s') != '' || I('post.e_salenum/s') != '') {
                $where['goods.goods_salenum'] = array('between', array(I('post.s_salenum/s'), I('post.e_salenum/s')));
            }
            if (I('post.is_offline/s')) {
                $where['goods_common.is_offline'] = 1;
            }
            $field = 'goods_common.goods_verify,goods_common.goods_verifyremark,goods_common.spec_value,goods_common.is_offline,goods_common.store_id,goods_common.goods_state,goods_common.goods_stateremark,goods.goods_id,goods_common.goods_commonid,goods_common.goods_name,goods_common.gc_name,goods_common.goods_price,goods_common.goods_serial,goods_common.goods_image,sum(goods.goods_storage) as total_goods_storage,sum(goods.goods_salenum) as total_goods_salenum';
            if ($type == 'sale' || $type == 'verify') {
                $field .= ',goods_common.goods_selltime as goods_time';
            } elseif ($type == 'inventory') {
                $field .= ',goods_common.tesu_offline_time as goods_time';
            }
            $goods_info = $model_goods->getGoodsJoinCommonList($type, $where, $field, $startPage . "," . $pageSize, I('post.sortby'), I('post.is_offline/s'));
            $goods_list = $goods_info['goods_list'];
            foreach ($goods_list as $key => $item) {
                if (!$item['goods_commonid']) {
                    unset($goods_list[$key]);
                } else {
                    if ($type == 'sale') {
                        $goods_list[$key]['goods_selltime'] = $item['goods_selltime'] ? date("Y-m-d H:i:s", $item['goods_selltime']) : 0;
                    }
                }
                $goods_list[$key]['goods_image'] = thumb($item, 60);
                if($_SESSION['com_type'] == 1){
                    $goods_list[$key]['url'] = '/goods/index?goods_id='.$item['goods_id'];
                }else if($_SESSION['com_type'] == 3){
                    $goods_list[$key]['url'] = U('ShowFactory/goodsDetial',array('goods_id'=>$item['goods_id'], 'store_id'=>$item['store_id']));
                }
                $spec = unserialize($item['spec_value']) ?: [];
                $spec_str = '';
                if (is_array($spec) && !empty($spec)) {
                    foreach ($spec as $k => $v) {
                        if (is_array($v) && !empty($v)) {
                            foreach ($v as $k2 => $v2) {
                                $spec_str .= $v2 . ',';
                            }
                        }
                    }
                }
                if ($spec_str) {
                    $spec_str = substr($spec_str, 0, -1);
                } else {
                    $spec_str = '无';
                }
                $goods_list[$key]['spec_value'] = $spec_str;
            }

            if (!empty($goods_list)) {
                $result = array('resultText' =>
                    array('message' => '成功',
                        'goods_list' => array_values($goods_list),
                        'total' => $goods_info['count'],
                        'pageSize' => $pageSize,
                        'curpage' => $page,
                        'totalPage' => ceil(count($goods_list) / 10)
                    ),
                    'code' => 1);
            } else {
                $result = array('resultText' => array('message' => '没有商品'), 'code' => 401);
            }
            $this->ajaxReturn($result);
        }

        $type = I('get.type/s');
        $con = array();
        $con['goods_verify'] = 1;
        if ($type == 'sale'){
            $con['goods_state'] = 1;
        }elseif ($type == 'inventory'){
            $con['goods_state'] = 0;
        }elseif ($type == 'verify'){
            $con['goods_verify'] = 10;
        }
        $con['store_id'] = session('store_id');
        $this->assign('count', $model_goods->getGoodsCommonCount($con));

        if ($type == 'sale'){
            $showpage = 'goodsOnSale';
        }elseif ($type == 'inventory'){
            $showpage = 'goodsAtInventory';
        }elseif ($type == 'verify'){
            $showpage = 'goodsOnVerify';
        }
        $this->display($showpage);
    }
    
    private function filterCondition(){
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        if ($_GET['order_num'] != '') {
            $condition['order_sn'] = array("like", '%' . $_GET['order_num'] . '%');
        }
        //添加买家名称
        if ($_GET['buyer_name'] != '') {
            $condition['buyer_name'] = array('like', '%' . $_GET['buyer_name'] . '%');
        }
        //添加订单状态
        if (isset($_GET['order_state'])) {
            if ($_GET['order_state'] == 1000) {
                showMessage('功能正在开发中!');
            }
            $condition['order_state'] = $_GET['order_state'];
        }
        //不是工厂订单
        $condition['order_type'] = array('NEQ', 4);
        //添加支付方式
        if (isset($_GET['pay_way'])) {
            switch ($_GET['pay_way']) {
                case '1':
                    $condition['payment_code'] = 'alipay';
                    break;
                case '2':
                    $condition['payment_code'] = 'wxpay';
                    break;
                case '3':
                    $condition['payment_code'] = 'predeposit';
                    break;
                case '4':
                    $condition['payment_code'] = 'chinapay';
                    break;
                default:
            }
        }
        //追加时间条件
        if (isset($_GET['order_time1']) && isset($_GET['apply_time2'])) {
            $stime = strtotime($_GET['order_time1']);
            $etime = strtotime($_GET['apply_time2']);
            $condition['add_time'] = array('between', $stime . ',' . $etime);
        }
        //是否是投资购
        $condition['is_investpay'] = '0';
        return $condition;
    }

    /**
    * 订单管理
    */
    public function orderManage(){
        $condition = array();
        $condition['o.store_id'] = session('store_id');
        if (I('get.order_num') != '') {
            $condition['o.order_sn'] = array("like", '%' . I('get.order_num') . '%');
        }
        //添加买家名称
        if (I('get.buyer_name') != '') {
            $condition['o.buyer_name'] = array('like', '%' . I('get.buyer_name') . '%');
        }
        //商品名称
        if (I('get.goods_name') != ''){
            $condition['og.goods_name'] = array('like', '%' . I('get.goods_name') . '%');
        }
        //添加订单状态
        if (I('get.order_state')) {
            $condition['o.order_state'] = I('get.order_state');
        }
        //不是工厂订单
        $condition['o.order_type'] = array('NEQ', 4);
        //添加支付方式
        if (I('get.pay_way')) {
            switch (I('get.pay_way')) {
                case '1':
                    $condition['o.payment_code'] = 'alipay';
                    break;
                case '2':
                    $condition['o.payment_code'] = 'wxpay';
                    break;
                case '3':
                    $condition['o.payment_code'] = 'predeposit';
                    break;
                case '4':
                    $condition['o.payment_code'] = 'chinapay';
                    break;
                default:
            }
        }
        //追加时间条件
        if (I('get.start_add_time') != '' || I('get.end_add_time') != ''){
            $stime = I('get.start_add_time') != '' ? strtotime(I('get.start_add_time')) : 0;
            $etime = I('get.end_add_time') != '' ? strtotime(I('get.end_add_time')) : time();
            $condition['o.add_time'] = array('between', $stime . ',' . $etime);
        }

        //是否是投资购
        $condition['is_investpay'] = '0';

        $_SESSION['orders_search'] = $condition;
        $_SESSION['orders_search_join'] = false;

        $orderby = I('get.sortby') ? 'o.add_time '.I('get.sortby') : 'o.order_id desc';

        $model = D("Order");
        $count = $model->alias("o")->join(C('DB_PREFIX').'order_goods og ON o.order_id = og.order_id','LEFT')->where($condition)->count();

        $Page = new \Think\Page($count, 10);
        $field = array('og.goods_id,og.store_id,og.goods_name,og.goods_price,og.goods_num,og.goods_image,o.add_time,o.order_state,o.order_amount,o.order_id,o.order_sn,o.payment_code,o.buyer_name,o.tesu_seller_remark');
        $order_list = $model->alias("o")->join(C('DB_PREFIX').'order_goods og ON o.order_id = og.order_id','LEFT')->where($condition)
            ->field($field)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();

        $show = $Page->show();// 分页显示输出

        $tmp_order_list = array();
        if (!empty($order_list)){
            foreach ($order_list as $order){
                $tmp_order_list[$order['order_id']] = $order;
            }

            //取商品列表
            $order_goods_list = $model->getOrderGoodsList(array('order_id' => array('in', array_keys($tmp_order_list))));
            foreach ($order_goods_list as $value) {
                $value['goods_image_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $tmp_order_list[$value['order_id']]['extend_order_goods'][] = $value;
            }
        }

        $model_goods = D('goods');
        $up_goods_record_model = M('up_goods_record');
        foreach ($tmp_order_list as $k => $v) {
            $tmp_order_list[$k]['payment_name'] = orderPaymentName($v['payment_code']);
            foreach ($v['extend_order_goods'] as $k2 => $v2) {
                //获取规格列表
                $goods_condition['goods_id'] = $v2['goods_id'];
                $goods = $model_goods->getGoodsInfo($goods_condition, 'goods_spec,is_offline,goods_commonid');
                //查找云端商品
                $up_goods = $up_goods_record_model->where(array('dealer_store_id' => session('store_id'), 'dealer_goods_commonid' => $goods['goods_commonid']))->find();
                $spec = unserialize($goods['goods_spec']) ?: [];
                $spec_str = '无';
                if (is_array($spec) && !empty($spec)) {
                    $spec_str = '';
                    foreach ($spec as $k3 => $v3) {
                        $spec_str .= $v3;
                    }
                    if (count($spec) > 1) {
                        $spec_str = substr($spec_str, 0, -1);
                    }
                }
                $tmp_order_list[$k]['extend_order_goods'][$k2]['goods_spec'] = $spec_str;
                $tmp_order_list[$k]['extend_order_goods'][$k2]['is_offline'] = !empty($up_goods) ? 2 : $goods['is_offline'];
                $tmp_order_list[$k]['extend_order_goods'][$k2]['factory_goods_id'] = !empty($up_goods) ? $up_goods['factory_goods_id'] : '';
            }
        }
        $this->assign('order_list', $tmp_order_list);
        $this->assign('sortby', I('get.sortby'));
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * 乐装订单管理
     */
    public function deOrderManage(){
        $condition = array();
        $condition['o.store_id'] = session('store_id');
        if (I('get.order_num') != '') {
            $condition['o.order_sn'] = array("like", '%' . I('get.order_num') . '%');
        }
        //添加买家名称
        if (I('get.buyer_name') != '') {
            $condition['o.buyer_name'] = array('like', '%' . I('get.buyer_name') . '%');
        }
        //商品名称
        if (I('get.title') != ''){
            $condition['dp.title'] = array('like', '%' . I('get.title') . '%');
        }
        //添加订单状态
        if (I('get.order_state')) {
            $condition['o.order_state'] = I('get.order_state');
        }
        //不是工厂订单
        $condition['o.order_type'] = ORDER_TYPE_DECORATE;
        $condition['o.payment_code'] = 'easypay';

        //追加时间条件
        if (I('get.start_add_time') != '' || I('get.end_add_time') != ''){
            $stime = I('get.start_add_time') != '' ? strtotime(I('get.start_add_time')) : 0;
            $etime = I('get.end_add_time') != '' ? strtotime(I('get.end_add_time')) : time();
            $condition['o.add_time'] = array('between', $stime . ',' . $etime);
        }

        //是否是投资购
        $condition['o.is_investpay'] = '0';

        $orderby = I('get.sortby') ? 'o.add_time '.I('get.sortby') : 'o.order_id desc';

        $model = D("Order");
        $count = $model->alias("o")->join(C('DB_PREFIX').'decorate_plan dp ON o.plan_id = dp.de_plan_id')->where($condition)->count();

        $Page = new \Think\Page($count, 10);
        $field = array('dp.*,o.add_time,o.order_state,o.order_amount,o.order_id,o.order_sn,o.payment_code,o.buyer_name');
        $order_list = $model->alias("o")->join(C('DB_PREFIX').'decorate_plan dp ON o.plan_id = dp.de_plan_id')->where($condition)
            ->field($field)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();

        $show = $Page->show();// 分页显示输出

        $this->assign('order_list', $order_list);
        $this->assign('sortby', I('get.sortby'));
        $this->assign('page', $show);
        $this->display();
    }

    /*
     * 定价页面
     */
    public function tplMakePrice(){
	    $order_id = intval($_GET['order_id']);
		
	    if ($order_id <= 0) {
		    $this->error('订单不存在！');
	    }

	    $order_info = $this->get_order_info($order_id);
		//print_r($order_info);exit;
	    if (empty($order_info)) {
		    $this->error('订单不存在！');
	    }
		
	    $this->assign('order_info', $order_info);

        $this->display();
    }

	//获取订单信息
    public function get_order_info($order_id, $extend = array('order_goods', 'order_common', 'store'), $fields = '*'){
        $model_order = D('Order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $condition['store_id'] = session('store_id');
		//$condition['store_id'] = 240;
        $order_info = $model_order->getOrderInfo($condition, $extend, $fields);

        return $order_info;
    }
    
    /*
     * 我的收入
     */
    public function income(){
        $model_money = D('MoneyRecord');
        $model_store = D('Store');
        //7天收入
        $in_money = $model_money->getWeekMoney($_SESSION['store_id']);
        $store_info =$model_store->getStoreInfo(array('store_id' => session('store_id')), 'deposit_avaiable');
        $yu_e = $store_info['deposit_avaiable'];
        $money_record = $model_money->getByStore(array('store_id' => session('store_id')), '1,10');
        $this->assign('in_money',$in_money);
        $this->assign('yu_e',$yu_e);
        $this->assign('money_record',$money_record);
        $this->display();
    }    

    /*
     * 收支明细
     */
    public function incomeDetail(){
        //查询实例        
        $condition = array();
        if (isset($_GET['start_time']) && intval($_GET['start_time']) && isset($_GET['end_time']) && intval($_GET['end_time'])) {
            $time = strtotime($_GET['start_time']);
            $etime = strtotime($_GET['end_time']);
            $condition['created_at']=array('between',array($time,$etime));
        }
        if(isset($_GET['order_num']) && !empty($_GET['order_num'])){
            $condition['order_sn']=$_GET['order_num'];
        }
        if(isset($_GET['serial_number']) && !empty($_GET['serial_number'])){
            $condition['id']=$_GET['serial_number'];
        }
        if(isset($_GET['type'])){
            if(I('get.type') == 0){
                $condition['m_type'] = 8;
                $condition['is_pay'] = 1;
            }else if(I('get.type') == 1){
                $condition['m_type'] = 8;
                $condition['is_pay'] = 0;
            }else{
                $condition['m_type']=  intval($_GET['type']);
            }
        }
        /*
         * 收支分为3部分
         * 1.消费者和卖家的现金交易，记录在money_record 中
         * 2.消费者和卖家的分期交易，募资完成之后，写入到collect_money 中
         * 3.消费者和卖家的转账操作，记录在money_record 中
         * 
         */
        $model_money = D('MoneyRecord');
        $condition['store_id'] = $_SESSION['store_id'];
        
        
        $p = empty($_GET['p'])?1:intval($_GET['p']);
        $money_record = $model_money->getByStore($condition,$p.',10');
        //获得所有链金所收入
//        $collect_money_model = D('collect_money_record');
//        $money_record1 = $collect_money_model->where($condition)->select();
//        foreach ($money_record1 as $key => $value) {
//            if(is_null($value['is_pay'])){
//                //is_pay =0 入账 is_pay =1 出账
//                $value['is_pay'] = 0;    
//            }
//            if(is_null($value['created_at'])){
//                $value['created_at'] = $value['add_time']; 
//            }
//            
//
//            $money_record1[$key] = $value;
//        }
//
//        $money_record = $money_record + $money_record1;
//        $count = $model_money->getByStoreCount($condition);
//        $count = 100000;
        
        $this->assign('page', getPage($count));
        $this->assign('money_record',$money_record);
        $this->display();
    }

    /*
     * 提现记录
     */
    public function withdrawHistory(){

        //查询实例
        $time = 0;
        $etime = time();
        $condition = array();
        if (isset($_GET['time'])) {
            switch ($_GET['time']) {
                case '3month':
                    $time = strtotime('-3 month');
                    $condition['pdc_add_time'] = array('between',"$time,$etime");
                    break;
                case '1year':
                    $time = strtotime('-1 year');
                    $condition['pdc_add_time'] = array('between',"$time,$etime");
                    break;
                default:
            }
        }else{
            if (isset($_GET['s_time']) && intval($_GET['s_time'])) {
                $time = strtotime($_GET['s_time']);
            }
            if (isset($_GET['e_time']) && intval($_GET['e_time'])) {
                $etime = strtotime($_GET['e_time']);
            }
            $condition['pdc_add_time'] = array('between',"$time,$etime");
        }
        if (isset($_GET['payment_state'])&&$_GET['payment_state']!=3){
            $condition['pdc_payment_state'] = $_GET['payment_state'];
        }
                
        $condition['pdc_store_id'] = $_SESSION['store_id'];
        $model_pd = D('Predeposit');
        $p = empty($_GET['p'])?1:$_GET['p'];
        $list = $model_pd->getPdCashList($condition,$p.',10','*','pdc_id desc');
        $count = $model_pd->getPdCashListCount($condition);
        $this->assign('page', getPage($count));
        $this->assign('list', $list);        
        $this->display();
    }



    public function debug(){
        $this->display();
    }

    //商品上下架
    public function turnToOnOff(){
        $commonid = $_POST['id'];
        if (!$commonid){
            $this->jsonFail('请选择');
        }

        $type = trim($_GET['type']);
        $common_id = $this->checkRequestCommonId($commonid);
        $commonid_array = explode(',', $common_id);

        $model_goods = D('Goods');
        $where = array();
        $where['goods_commonid'] = array('in', $commonid_array);
        $where['store_id'] = $_SESSION['store_id'];
        if($_GET['is_offline']==1){
            $where['is_offline'] =1; 
        }

        if ($type == 'on'){
            if ($this->store_info['store_state'] != 1) {
                $this->jsonFail('店铺正在审核中或已经关闭');
            }
            $return = $model_goods->editProducesOnline($where);
        }elseif($type == 'off'){
            $return = $model_goods->editProducesOffline($where);
        }

        if ($return) {
            if ($type == 'on'){
                $this->recordSellerLog('商品上架，平台货号：'.$commonid);
            }else{
                // 更新优惠套餐状态关闭
//                $goods_list = $model_goods->getGoodsList($where, 'goods_id');
//                if (!empty($goods_list)) {
//                    $goodsid_array = array();
//                    foreach ($goods_list as $val) {
//                        $goodsid_array[] = $val['goods_id'];
//                    }
//                    Model('p_bundling')->editBundlingCloseByGoodsIds(array('goods_id' => array('in', $goodsid_array)));
//                }
//                // 添加操作日志
                $this->recordSellerLog('商品下架，平台货号：'.$common_id);
            }
            $this->jsonSucc('操作成功');
        } else {
            $this->jsonFail('操作失败');
        }
    }
    
    /**
     * 验证commonid
     */
    private function checkRequestCommonId($common_ids) {
        if (!preg_match('/^[\d,]+$/i', $common_ids)) {
            $this->jsonFail('error');
        }
        return $common_ids;
    }
    
    /******
     * 发布商品相关异步请求
     * Begin
     */
    //增加,删除常用类目
    public function changeStapleAjax()
    {
        // 实例化商品分类模型
        $model_goodsclass = D('GoodsClass');
        $op = isset($_REQUEST['operation']) ? $_REQUEST['operation'] : '';
        $gc_id = isset($_REQUEST['gc_id']) ? $_REQUEST['gc_id'] : '';
        $staple_id = isset($_REQUEST['staple_id']) ? $_REQUEST['staple_id'] : '';
        switch ($op) {
            case 'add':
                // 验证商品分类是否存在且商品分类是否为最后一级
                //$data = H('goods_class') ? H('goods_class') : H('goods_class', true);
                $data = H('goods_class');
                if (!isset($data[$gc_id]) || isset($data[$gc_id]['child']) || isset($data[$gc_id]['childchild'])) {
                    $this->jsonFail('请选择最后一级商品进行发布!');
                }
                // 更新常用分类信息
                $goods_class = $model_goodsclass->getGoodsClassLineForTag($gc_id);
                $res = D('GoodsClassStaple')->autoIncrementStaple($goods_class, $_SESSION['member_id']);
                if ($res) {
                    $this->jsonSucc('增加成功');
                } else {
                    $this->jsonFail('新增失败');
                }
                break;
            case 'del':
                if ($staple_id < 1) {
                    $this->jsonFail('id异常');
                }
                /**
                 * 实例化模型
                 */
                $model_staple = D('GoodsClassStaple');
                $result = $model_staple->delStaple(array('staple_id' => $staple_id, 'member_id' => $_SESSION['member_id']));
                if ($result) {
                    $this->jsonSucc('删除成功');
                } else {
                    $this->jsonFail('删除失败');
                }
                break;
            default:
                $this->jsonFail('指令不正确');
        }
    }
    
    /****
     *
     * 检查gc_id是否是最后一级
     *
     */
    public function checkStapleAjax()
    {
        $gc_id = isset($_GET['gc_id']) ? $_GET['gc_id'] : '';
        //$data = H('goods_class') ? H('goods_class') : H('goods_class', true);
        $data = H('goods_class');
        if (!isset($data[$gc_id]) || isset($data[$gc_id]['child']) || isset($data[$gc_id]['childchild'])) {
            $this->jsonFail('fail');//不是最后一级
        } else {
            $this->jsonSucc('ok');//是最后一级
        }
    }

    /**
     * ajax获取商品分类的子级数据
     */
    public function ajaGoodsClass() {
        $gc_id = intval($_GET['gc_id']);
        $deep = intval($_GET['deep']);
        if ($gc_id <= 0 || $deep <= 0 || $deep >= 4) {
            exit();
        }
        $model_goodsclass = D('GoodsClass');
        $list = $model_goodsclass->getGoodsClass($_SESSION['store_id'], $gc_id, $deep, 1);
        if (empty($list)) {
            exit();
        }
        /**
         * 转码
         */
//        if (strtoupper ( CHARSET ) == 'GBK') {
//            $list = Language::getUTF8 ( $list );
//        }
        echo json_encode($list);
    }
    
    /**
     * 记录卖家日志
     *
     * @param $content 日志内容
     * @param $state 1成功 0失败
     */
    protected function recordSellerLog($content = '', $state = 1)
    {
        $seller_info = array();
        $seller_info['log_content'] = $content;
        $seller_info['log_time'] = time();
        $seller_info['log_seller_id'] = $_SESSION['seller_id'];
        $seller_info['log_seller_name'] = $_SESSION['seller_name'];
        $seller_info['log_store_id'] = $_SESSION['store_id'];
        $seller_info['log_seller_ip'] = getIp();
        $seller_info['log_url'] = $_GET['act'] . '&' . $_GET['op'];
        $seller_info['log_state'] = $state;
        $model_seller_log = D('SellerLog');
        $model_seller_log->addSellerLog($seller_info);
    }
    
    /*
    * 输出json 成功信息
     */
    function jsonSucc($msg = '成功！'){
        $result['code'] = 1;
        $result['resultText']['message'] = $msg;
        $this->ajaxReturn($result);
    }
    
    /*
    * 输出json 数组信息
     */
    function jsonArr($msg_arr=array()){
        $result['code'] = 1;
        $result['resultText']['data'] = $msg_arr;
        $result['resultText']['message'] = '成功';
        $this->ajaxReturn($result);
    }
    
    /*
    * 输出json 错误信息
     */
    function jsonFail($msg = '失败'){
        $result['code'] = 0;
        $result['resultText']['message'] = $msg;
        $this->ajaxReturn($result);
    }

	/**
	 * 商家定价
	 */
	public function modifyState(){
//		$_POST['state_type']='modify_price';
//		$_POST['order_id']=1510;
//		$_POST['shipping_fee']=0.02;
//		$_POST['order_amount'][1666]=1000.00;
//		$_POST['goods_amount']=1000.02;
		$state_type	= $_POST['state_type'];//modify_price
		$order_id = intval($_POST['order_id']);

		if (!$state_type || $order_id <= 0){
			$this->ajaxReturn(array('resultText' => array('message' => '参数错误'), 'code' => 0));
		}
		
		$model_order = D('Order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['store_id'] = $_SESSION['store_id'];
		$order_info	= $model_order->getOrderInfo($condition);
		
		try {
			$model_order->startTrans();

			if ($state_type == 'order_cancel') {
				$result = $this->_change_state_order_cancel($order_info);
				$message = '成功取消了订单';
			} elseif ($state_type == 'modify_price') {
				$order_id = $order_info['order_id'];

				$if_allow = ($order_info['order_state'] == ORDER_STATE_MAKEPRICE) ||
					($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);

				if (!$if_allow) {
					throw new \Exception('非法请求！');
				}
				$data = array();
				$data['shipping_fee'] = abs(floatval($_POST['shipping_fee']));
                /*订单总价*/
				$total_amount = abs(floatval($_POST['goods_amount']));//商品总价格
                /*商品单价数组*/
				$order_amount_arr = $_POST['order_amount'];


                /*后台数据验证 总价=单价*数量+运费*/
				$price_num = 0;
                $test;
				foreach ($order_amount_arr as $rec_id => $price) {
                    //获得商品数量
                    $tmp = M('order_goods')->where(array('rec_id'=>$rec_id))->find();
                    $tmp_num = $tmp['goods_num'];
                    if(!is_numeric($tmp_num)){
                        throw new \Exception("商品数量不合法", 1);
                    }
					$price_num += $price*$tmp_num;
                    $test[$rec_id] = $tmp_num;
				}

                
				//print_r($total_amount);
				$price_num = abs(floatval($price_num));
				//print_r($price_num);exit;
				if ($total_amount != $data['shipping_fee'] + $price_num){
					throw new \Exception('价格不正确');
				}
				
				foreach ($order_amount_arr as $rec_id => $price) {
					$saveData['goods_pay_price'] = $price;
					$con['rec_id'] = $rec_id;
					$upd_order_goods = $model_order->editOrderGoods($saveData, $con);
					if ($upd_order_goods === false) {
						throw new \Exception('订单商品表修改失败');
					}
				}

				$data['goods_amount'] = $price_num;
				$data['order_amount'] = $total_amount;
				$data['order_state'] = ORDER_STATE_NEW;
				$update = $model_order->editOrder($data, array('order_id' => $order_id));
				if (!$update) {
					throw new \Exception('保存失败！');
				}
				//记录订单日志
				$data = array();
				$data['order_id'] = $order_id;
				$data['log_role'] = 'seller';
				$data['log_user'] = $_SESSION['member_name'];
				$data['log_msg'] = '定价成功';
				$model_order->addOrderLog($data);
				$message = '定价成功';
			}

			$model_order->commit();
			$this->ajaxReturn(array('resultText' => array('message' => $message), 'code' => 1));

		} catch (\Exception $e) {
			$model_order->rollback();
			$this->ajaxReturn(array('resultText' => array('message' => $e->getMessage()), 'code' => 0));
		}
	}

	/**
	 * 取消订单
	 * @param unknown $order_info
	 */
	private function _change_state_order_cancel($order_info, $status_flag) {
        $order_id = $order_info['order_id'];
        $model_order = D('order');
        $if_allow = $model_order->getOrderOperateState('store_cancel', $order_info);
        if (!$if_allow) {
            throw new \Exception(L('invalid_request'));
        }

        $goods_list = $model_order->getOrderGoodsList(array('order_id' => $order_id));
        $model_goods = D('goods');
        if(is_array($goods_list) and !empty($goods_list)) {
            foreach ($goods_list as $goods) {
                $data = array();
                $data['goods_storage'] = array('exp','goods_storage+'.$goods['goods_num']);
                $data['goods_salenum'] = "IF($goods[goods_num]-goods_salenum<0,$goods[goods_num]-goods_salenum,0)";
                $update = $model_goods->editGoods($data, array('goods_id' => $goods['goods_id']));
                if (!$update) {
                    throw new \Exception(L('nc_common_save_fail'));
                }
            }
        }

        //工厂订单
        if ($status_flag != 3){
            if ($order_info['order_state'] == ORDER_STATE_PAY){
                $amount = floatval($order_info['order_amount']);
                if ($amount > 0){
                    $data['deposit_avaiable'] = array('exp','deposit_avaiable+'.$amount);
                    $update = M('store')->where(array('store_id' => $order_info['buyer_id']))->save($data);
                    if (!$update){
                        throw new \Exception(L('nc_common_save_fail'));
                    }
                }
            }
        }

        //更新订单信息
        $data = array('order_state' => ORDER_STATE_CANCEL);
        $update = $model_order->editOrder($data, array('order_id' => $order_id));
        if (!$update) {
            throw new \Exception(L('nc_common_save_fail'));
        }

        //记录订单日志
        $data = array();
        $data['order_id'] = $order_id;
        $data['log_role'] = $status_flag == 2 ? 'factory' : 'seller';
        $data['log_user'] = session('store_name');
        //$data['log_msg'] = L('order_log_cancel');
        $extend_msg = $_POST['state_info1'] != '' ? $_POST['state_info1'] : $_POST['state_info'];
        if ($extend_msg) {
            //$data['log_msg'] .= ' ( '.$extend_msg.' )';
            $data['log_msg'] = $extend_msg;
        }
        $data['log_orderstate'] = ORDER_STATE_CANCEL;
        $model_order->addOrderLog($data);
	}

    public function ajax_order_cancel(){
        if (IS_POST) {
            $order_id = I('order_id', 0, 'int');
            $status_flag = I('status_flag');

            if ($order_id <= 0) {
                $this->jsonFail('订单出错！');
            }

            $model_order = D('order');
            $condition = array();
            $condition['order_id'] = $order_id;
            $condition['order_state'] = array('in', array(ORDER_STATE_NEW, ORDER_STATE_MAKEPRICE, ORDER_STATE_PAY));
            if ($status_flag == 3){//普通商品订单
                $condition['store_id'] = session('store_id');
            }else{
                $condition['order_type'] = ORDER_TYPE_FACTORY;
                if ($status_flag == 1) {//卖家中心--工厂订单
                    $condition['buyer_id'] = session('store_id');
                } elseif($status_flag == 2) {//工厂后台--订单
                    $condition['store_id'] = session('store_id');
                }
            }

            $order_info = $model_order->getOrderInfo($condition);
            if (empty($order_info)) {
                $this->jsonFail('没有订单！');
            }
            $this->assign('order_info', $order_info);

            try {
                $model_order->startTrans();
                $this->_change_state_order_cancel($order_info, $status_flag);
                $model_order->commit();
                $this->jsonSucc();
            } catch (\Exception $e) {
                $model_order->rollback();
                $this->jsonFail($e->getMessage());
            }
        }else{
            $this->jsonFail('非法');
        }
    }

    //工厂订单去支付
    public function go_to_pay(){
        $order_id = I('order_id', 0, 'int');
        if ($order_id <= 0){
            $this->error('订单错误');
        }

        //查询支付单信息
        $model_order= D('order');
        $order_info = $model_order->getOrderInfo(array('order_id' => $order_id, 'order_state' => ORDER_STATE_NEW, 'buyer_id' => session('store_id')));
        if (empty($order_info)){
            $this->error('未支付订单不存在');
        }

        //重新计算在线支付金额
        $pay_amount_online = ncPriceFormat(floatval($order_info['order_amount']) - floatval($order_info['pd_amount']) - floatval($order_info['order_amount_delta']));
        if ($pay_amount_online > 0) {

            /*测试用途，在测试模式在，在线支付永远为1分钱，不管商品的价格如何*/
            // if(APP_DEBUG||(C('MODE') == DEBUG)||(C('MODE') == TEST)){
            //     $pay_amount_online = 0.01;
            // }

            if($order_info['payment_code'] == 'chinabank' OR $order_info['payment_code'] == 'chinapay') {
                $gateway = CommonPay::getInstance('UnionPay_Express', 1);

                $order = [
                    'orderId'   => $order_info['pay_sn'], //Your order ID
                    'txnTime'   => date('YmdHis'), //Should be format 'YmdHis'
                    'orderDesc' => '全木行'.$order_info['subject'], //Order Title
                    //'txnAmt'    => $order_info['pay_amount']*100, //Order Total Fee
                    'txnAmt'    => 1, //测试1分钱
                ];

                $response = $gateway->send($order);
                $response->redirect();
            }
            elseif ($order_info['payment_code'] == 'wxpay'){
                $gateway = CommonPay::getInstance('WechatPay', 1);

                //重新生成微信支付订单号
                $buy_model = D('Buy');
                $order_model = D('Order');
                $wx_pay_sn = $buy_model->makePaySn($_SESSION['member_id']);
                $order_model->editOrder(array('wx_pay_sn' =>$wx_pay_sn), array('pay_sn' => $order_info['pay_sn']));  //更新订单

                $order = array (
                    'body'             => '全木行'.$order_info['subject'], //Your order ID
                    'out_trade_no'     => $wx_pay_sn, //Should be format 'YmdHis'
                    'total_fee'        => $order_info['pay_amount'] * 100, //Order Title
                    //'total_fee'        => 1, //测试1分钱
                    'attach'           => $order_info['pay_sn'],
                    'spbill_create_ip' => '114.119.110.120', //Order Total Fee
                );

                $response = $gateway->send($order);
                $_SESSION['product_buy_wxpay_url'][$order_info['pay_sn']] = $response->getCodeUrl();

                $param['pay_sn'] = $order_info['pay_sn'];
                $param['pay_amount'] = $pay_amount_online;
                $param['order_type'] = $order_info['order_type'];
                $tmp_order = base64_encode(serialize($param));
                $tmp_order = urlencode($tmp_order);
                @header("Location: ".U('Buy/wxpay', array('param' => $tmp_order)));
            }
            else if($order_info['payment_code'] == 'alipay') {
                $gateway = CommonPay::getInstance('Alipay_Express', 1);

                $order = [
                    'out_trade_no' => $order_info['pay_sn'], //your site trade no, unique
                    'subject'      => '全木行'.$order_info['subject'], //order title
                    'total_fee'    => $order_info['pay_amount'], //order total fee
                    //'total_fee'    => '0.01', //测试1分钱
                ];

                $response = $gateway->send($order);
                $response->redirect();
            }
        }
    }

    public function order_detail(){
        $order_id = I('get.order_id', 0, 'int');
        if ($order_id <= 0) {
            $this->error('订单错误');
        }

        $model_order = D('Order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getOrderInfo($condition, array('order_common', 'order_goods', 'member'));
        if (empty($order_info)) {
            $this->error('订单不存在');
        }

        if (!empty($order_info['extend_order_goods'])){
            $model_goods = D('goods');
            foreach ($order_info['extend_order_goods'] as $k => $v) {
                //获取规格列表
                $goods_condition['goods_id'] = $v['goods_id'];
                $goods = $model_goods->getGoodsInfo($goods_condition, 'goods_spec,is_offline');
                $spec = unserialize($goods['goods_spec']) ?: [];
                $spec_str = '无';
                if (is_array($spec) && !empty($spec)) {
                    $spec_str = '';
                    foreach ($spec as $k3 => $v3) {
                        $spec_str .= $v3;
                    }
                    if (count($spec) > 1) {
                        $spec_str = substr($spec_str, 0, -1);
                    }
                }
                $order_info['extend_order_goods'][$k]['goods_spec'] = $spec_str;
                $order_info['extend_order_goods'][$k]['is_offline'] = $goods['is_offline'];
            }
        }

        $this->assign('order_info', $order_info);

        switch ($order_info['order_state']) {
            //标准订单状态
            case ORDER_STATE_MAKEPRICE:
                break;
            case ORDER_STATE_CANCEL:
                $order_log = $model_order->getOrderLogList(array('order_id' => $order_info['order_id'], 'log_orderstate' => '0'));
                $this->assign('order_log_info', end($order_log));
                break;
            case ORDER_STATE_NEW:
                break;
            case ORDER_STATE_PAY:
                break;
            case ORDER_STATE_SUCCESS:
                break;
            case ORDER_STATE_HANDLING:
                break;
            default:
                $this->error('404');
                break;
        }
        $this->display();
    }
}