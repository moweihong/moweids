<?php
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;

/*
 * 家装设计
 */
class THomedesignController extends ShopCommonController {

	/*
	 * 装修设计首页
     */
	public function index()
	{
		$this->decoration_company=D('adv')->getAdvList(array('ap_id'=>486));
        $this->decoration_pic=D('adv')->getAdvList(array('ap_id'=>487));
		$this->display();
	}

	/*
	 * 装修设计搜索页
	 */
	public function search(){
		$province_id = I('get.province_id');
		$city_id = I('get.city_id');
		//$area_id = I('get.area_id');
		if((intval($province_id) === false)||(intval($province_id) === false)){
			$this->error('请选择区域');
		}
        

		//根据区域搜索家装公司
		$city_record = D('area')->where(array('area_id' => $city_id))->find();
        $this->assign('city_name', $city_record['area_name']);

		$com_model = Model('store');
		$condition['province_id']   = $province_id;
		$condition['city_id']       = $city_id;
		$condition['com_type']      = 2;
		$condition['business_type'] = 1;
		$record_list = $com_model->where($condition)->select();
        $count = $com_model->field('count(*) as c')->where($condition)->select();
        $this->assign('count', $count[0]['c']);
		$this->assign('com_list', $record_list);
		$this->display();
	}	

	/*
	 * 装修设计公司详情页
	 */
	public function storeDetail(){
		$id = I('get.id');
        $type = I('get.type');
		//获取装修公司信息
	    $store_model = Model('store');
        $type = intval(I('get.type'));
        if ($id > 0 && $type > 0){
            $store_info = $store_model->getStoreInfoByID($id);
            if ($store_info['store_state'] != 1){
                showMessage('该店铺已关闭或正在审核中','','html','error');
            }
            $this->assign('decorate_info', $store_info);

            $condition = array();
            $condition['store_id'] = $id;
            $condition['tesu_deleted'] = 0;
            $decorate_model = D('decorate');
            if ($type == 1){
                $condition['is_cover'] = 1;
                $field = 'draw_list_id,title,pic';
                $effectdraw_list = $decorate_model->getDecorateList('decorate_effectdraw', $condition, $field, 'is_cover desc,draw_id desc', "1,4", '');
                if (!empty($effectdraw_list)){
                    $this->assign('effectdraw_list', $effectdraw_list);
                }
            }else{
                $decorate_list = $decorate_model->getDecorateList('decorate_plan', $condition, '*', 'de_plan_id desc', "1,4");
                $this->assign('decorate_list', $decorate_list);
            }
        }

        $this->assign('store_id', $id);
        $this->assign('type_id', $type);
		$this->display();
	}

    /*
     * 验证访问密码
     */
	public function inputpwd(){
		$id = intval(I('post.id'));

        $pwd = trim(I('post.visit_pwd'));
        if ($pwd == ''){
        	$this->jsonFail('密码错误');
        }else{
            $decorate_model = Model('decorate');
            $conditon = array();
            $conditon['de_plan_id'] = $id;
            $decorate_info = $decorate_model->getDecorateInfo('decorate_plan', $conditon, 'visit_pwd');
            $visit_pwd = $decorate_info['visit_pwd'];
            \Think\Log::lol('  pwd = '.$pwd.'  db pwd = '.$visit_pwd.'  md4 pwd = '.md5($pwd));
            if ($visit_pwd != md5($pwd)){
            	$this->jsonFail('密码错误');
            }else{
                $_SESSION['decorate_input_right_pwd_'.$id] = 1;
                $this->jsonSucc('正确');
            }
        }
        $this->jsonSucc('密码正确');
	}

	/*
	 * 方案详情页
	 */
	public function planDetail(){
		$id = I('get.id');
		if(intval($id) === false){
			$this->error('找不到方案');
		}

		$decorate_model = Model('decorate');
        $conditon = array();
        $conditon['de_plan_id'] = $id;
        $decorate_info = $decorate_model->getDecorateInfo('decorate_plan', $conditon);
        if (empty($decorate_info)){
            $this->error('方案不存在');
        }
        $access = false;
        if (isset($_SESSION['store_id']) && $_SESSION['store_id'] == $decorate_info['store_id']){//本商家发布的方案有权限查看
            $access = true;
        }
        if ($access == false && $_SESSION['decorate_input_right_pwd_'.$id] != 1){
            $this->error('权限不足，请从装修公司页面输入密码查看！');
        }
        if (!empty($decorate_info)){
            $store_id = $decorate_info['store_id'];
            $decorate_info['coverpage'] = unserialize($decorate_info['coverpage']);
            $decorate_info['contract_pic'] = unserialize($decorate_info['contract_pic']);
            $this->assign('decorate_info', $decorate_info);

            //地址
            // $store_info = Model()->table('allwood_store')->join('allwood_store_joinin on  allwood_store.`member_id`=allwood_store_joinin.`member_id` ')->where(array('allwood_store.store_id' => $decorate_info['store_id']))->field('store.store_name,store_joinin.province_id,store_joinin.city_id,store_joinin.area_id,store.store_tel,store.store_address')->find();

            $store_model = Model('store');
           	$store_info = $store_model->where(array('store_id' => $decorate_info['store_id']))->find();

            if (!empty($store_info)){
                $area_arr = array($store_info['province_id'], $store_info['city_id'], $store_info['area_id']);
                $model = Model('area');
                $condition = array();
                $condition['area_id'] = array('in', $area_arr);
                $areaList = $model->getAreaList($condition, 'group_concat(area_name) as areaname', 'area_name');
                $address = '';
                if (!empty($areaList)){
                    foreach ($areaList as $item) {
                        $address .= $item['areaname'];
                    }
                }
                $store_info['address'] = $address.$store_info['store_address'];
                $this->assign('store_info', $store_info);

                $easypay = $this->_getEasypayInfo($decorate_info['cost']);
                $this->assign('easypay', $easypay);
            }

            /*$easypay_api = API('easypay');
            $result = json_decode($easypay_api->get_credit_status(array('usrid' => $_SESSION['mid'])), true);
            if($result['code'] == 0){
                $easypay_credit_status = $result['return_param']['check_flag'];
                $this->assign('easypay_credit_status', $easypay_credit_status);
            }*/

        }

        $this->assign('cover', $decorate_info['coverpage']['m_pic']);
        $status = $_SESSION['easypay_credit_status'];

        $is_active = $_SESSION['is_activate'];
        $this->assign('credit_status', $status);
        $this->assign('is_active', $is_active);
       
		$this->display();
	}

	/*
	 * 装修方案结算页
	 */
	public function check(){
        $material_id = I('get.id/d');
        $plan_id = I('get.plan_id/d');
        $period = I('get.period/d');
        if($material_id === false){
            $this->error('请先填写补充资料');
        }
        if($plan_id === false){
            $this->error('找不到方案');
        }
        if($period === false){
            $this->error('请选择期数');
        }

        //生成订单
        //提交乐装
        $decorate_model = D('decorate');
        $condition = array();
        $condition['de_plan_id'] = $plan_id;
        $decorate_plan_info = $decorate_model->getDecorateInfo('decorate_plan', $condition, '*');
        if (empty($decorate_plan_info)){
            $this->error('没有此装修方案');
        }
        $this->assign('plan_info', $decorate_plan_info);
        $easypay = $this->_getEasypayInfo($decorate_plan_info['cost']);
        if (!empty($easypay)){
            $selected_easypay_info = array();
            foreach ($easypay as $key => $value) {
                if ($value['period'] == $period){
                    $selected_easypay_info = $value;
                    break;
                }
            }
            if (empty($selected_easypay_info)){
                $this->error('没有此分期期数');
            }
            $this->assign('easypay_info', $selected_easypay_info);
        }

        $selected_easypay_info = getMyEasypayInfo($decorate_plan_info['cost'], $period, 0);

        $this->assign('easy_deco', $selected_easypay_info);
        $this->assign('more_material_id', I('get.x'));
        $this->assign('plan_id', $plan_id);
        $this->assign('period', $period);
        $this->display();
	}

	/*
	 * 结算成功
	 */
	public function checkSuccess(){
		$this->display();
	}

    /*
     * 效果图查看
     */
    public function broadcastPic(){
        //$this->jsonFail('他很懒，什么都没有写！');
        $draw_list_id = intval($_POST['id']);
        if ($draw_list_id <= 0){
            $result = array('code' => 0, 'resultText' => array('message' => 'id错误'));
        }else{
            $decorate_model = Model('decorate');
            $conditon = array();
            $conditon['draw_list_id'] = $draw_list_id;
            $conditon['tesu_deleted'] = 0;
            $pic_list = $decorate_model->getDecorateList('decorate_effectdraw', $conditon, 'pic', 'draw_id desc');
            $pic_arr = array();
            if (!empty($pic_list)){
                foreach ($pic_list as $item) {
                    $pic_arr[] = unserialize($item['pic']);
                }
                $result = array('code' => 1, 'resultText' => array('message' => '图片列表', 'pic_list' => $pic_arr));
            }else{
                $result = array('code' => 0, 'resultText' => array('message' => '没有图片'));
            }
        }
        echo json_encode($result);
    }


	  /**
     *获取分期购信息
     */
    private function _getEasypayInfo($amount){
        //检查是否开启
        if(is_null(C('easybuy_status')) || (C('easybuy_status') == 0)){
            return array();
        }

        $pay_by_period = unserialize(C('pay_by_period'));
        $pay_by_period2 = $pay_by_period;
        $qishu = implode(';', array_keys($pay_by_period));
        $rate = array_values($pay_by_period);
        foreach ($rate as $key => $value) {
            $rate[$key] = number_format($value/100,3,'.','');
        }
        $rate = implode(';', $rate);

        //计算分期费用
        $easypay_api = API('easypay');
        $options['amount']        = $amount;
        //1 tie  0 bu tie
        //$tiexi = $this->_is_tiexi($store_id);
        $options['interest_type'] = 0;
        $options['periods']       = $qishu;
        $options['interest_rate'] = $rate;
        $options['factorage']     = C('easypay_charge_by_ccfax')/ 100;
        $result = json_decode($easypay_api->get_interest_info($options), true);
        if($result['code'] == 0){
            $pay_by_period = $result['return_param']['interests_list'];
        }

        //封装利息参数
        foreach ($pay_by_period as $key => $value) {
            $pay_by_period[$key]['factorage_rate'] = $options['factorage'];
            $pay_by_period[$key]['interest_rate'] = $pay_by_period2[$value['period']];
        }
        return $pay_by_period;
    }

    /*
     * 更新session
     */
    private function _update_session($status, $total, $available){
        $_SESSION['easypay_credit_status'] = $status;
        $_SESSION['easypay_credit_total'] = $total;
        $_SESSION['easypay_credit_available'] = $available;
        $_SESSION['easypay_freeze'] = $_SESSION['easypay_credit_status'] === -1 ? 1:0;;
        $_SESSION['easypay_status_zh'] = str_replace(
            array(0, 1, 3, 2, 5, 4, 6),
            array('未开启', '审批中','审批中', '审批未通过', '已开通', '审批未通过','冻结' ),
            intval($_SESSION['easypay_credit_status']));

        //
    }

    /**
     * 提交乐装订单
     */
    public function createOrders(){
        if(!is_numeric(session('member_id'))){
            $this->jsonFail('请登录');
        }

        \Think\Log::ext_log('post  = '.json_encode($_POST), 'api');
        $period = I('period/d');//期数
        $plan_id = I('plan_id/d');//方案id
        $material_id = I('material_id/d');//方案id
        $decorate_plan_info = D('decorate')->getDecorateInfo('decorate_plan', array('de_plan_id' => $plan_id), 'de_plan_id,store_id,cost');
        if (empty($decorate_plan_info)){
            $this->jsonFail('没有此装修方案');
        }

        $selected_easypay_info = getMyEasypayInfo($decorate_plan_info['cost'], $period, 0);
        if (empty($selected_easypay_info)){
            $this->jsonFail('没有此分期期数');
        }

        try {
            //开始事务
            $model_order = Model('order');
            $model_order->startTrans();
            //创建订单
            $order_id = $this->_createOrder($decorate_plan_info, $selected_easypay_info);
            $order_info = D('order')->getOrderInfo(array('order_id' => $order_id));
            //提交补充资料
            $this->_moreMaterial($order_id, $material_id);
            //发标
            $this->_fabiao($order_info['order_sn'], $selected_easypay_info);
            //提交事务
            $model_order->commit();
        }catch (\Exception $e){
            //回滚事务
            $model_order->rollback();
            $this->jsonFail('请求失败：'.$e->getMessage());

        }

        $this->jsonSucc();
    }

    /*
     * 创建乐装订单
     */
    private function _createOrder($decorate_plan_info, $selected_easypay_info){
        $model_buy = D('buy');
        $model_order = D('order');
        $pay_sn = $model_buy->makePaySn(session('member_id'));
        $order_pay = array();
        $order_pay['pay_sn'] = $pay_sn;
        $order_pay['buyer_id'] = session('member_id');
        $order_pay_id = $model_order->addOrderPay($order_pay);

        if (!$order_pay_id) {
            throw new \Exception('订单创建失败!');
        }

        $order = array();
        $store_model = D('store');
        $member_model = D('member');
        $store_info = $store_model->getStoreInfoByID($decorate_plan_info['store_id']);
        $member_info = $member_model->getMemberInfo(array('member_id' => session('member_id')), 'member_name');

        $order['plan_id']            = $decorate_plan_info['de_plan_id'];
        $order['order_sn']           = $model_buy->makeOrderSn($order_pay_id);
        $order['pay_sn']             = $pay_sn;
        $order['store_id']           = $decorate_plan_info['store_id'];
        $order['store_name']         = $store_info['store_name'];
        $order['buyer_id']           = session('member_id');
        $order['buyer_name']         = $member_info['member_name'];
        $order['buyer_email']        = session('member_email');
        $order['add_time']           = TIMESTAMP;
        $order['payment_code']       = 'easypay';
        $order['order_amount_delta'] = $decorate_plan_info['cost'];
        $order['order_from']         = 1;
        $order['order_state']        = ORDER_STATE_HANDLING;
        $order['order_type']         = ORDER_TYPE_DECORATE;
        $order['period']             = $selected_easypay_info['period'];
        $order['interest_rate']      = $selected_easypay_info['interest_rate'];
        $order['factorage']          = $selected_easypay_info['factorage'];
        $order['interest_total']     = $selected_easypay_info['interest_total'];
        $order['fbxy_code']          = $model_buy->makesn_for_easypay('FBXY');//发标协议编号
        $order['jkxy_code']          = $model_buy->makesn_for_easypay('JKXY');//借款协议编号

        $order_id = $model_order->addOrder($order);

        if (!$order_id) {
            throw new \Exception('订单保存失败'.$order_id.$model_order->getLastSql());
        }
        return $order_id;
    }   

    /**
     * 乐装发标
     */
    private function _fabiao($order_sn, $selected_easypay_info){
        if(!is_numeric(session('mid'))){
            throw new \Exception("找不到java userid ", 1);
        }

        $start = 1;//每一天标的期数开始数字
        $tenderModel = M("tender");
        $today = date("Ymd", time());
        $tender = $tenderModel->where(array("date" => $today, 'type' => 1))->find();
        if($tender){
            $start = $start + $tender["count"];
            $tenderModel->where(array("date" => $today, 'type' => 1))->save(array("count" => $start));
        }else{
            $tenderModel->add(array("date" => $today, "count" => 1, 'type' => 1));
        }

        $easypay_api                     = API('easypay');
        $options['order_id']             = $order_sn;
        $options['borrow_uid']           = session('mid');
        $options['borrow_name']          = "乐装分期".$today.$start."期";
        $options['borrow_money']         = $selected_easypay_info['principal'];
        $options['borrow_interest']      = $selected_easypay_info['interest_total'];
        $options['borrow_interest_rate'] = $selected_easypay_info['interest_rate'];
        $options['borrow_duration']      = $selected_easypay_info['period'];
        $options['fee']                  = $selected_easypay_info['factorage'];
        $options['borrow_info']          = "borrow_info";
        $options['picpath']              = array();
        $options['interest_type']        = 0; //等额本息 不贴息
        $options['borrow_use']           = BORROW_USE_DECORATE;

        $result = json_decode($easypay_api->fabiao($options), true);
        if($result['code'] == -1){
            throw new \Exception($result['msg'], 1);
        }
    }

    /*
     * 提交补充资料
     */
    private function _moreMaterial($order_id, $material_id){
        \Think\Log::ext_log('material id = '.$material_id, 'api');
        if(!is_numeric($material_id)){
            throw new \Exception("找不到补充资料记录", 1);
            
        }
        $order_info = D('order')->getOrderInfo(array('order_id' => $order_id));
        if(is_null($order_info)){
            throw new \Exception("没有找到订单信息", 1);
            
        }
        /*获取补充*/
        $material_record = D('MoreMaterial')->where(array('id' => $material_id))->find();
        if(is_null($material_record)){
            throw new \Exception("补充资料记录不存在", 1);
        }

        $easypay_api = API('easypay');
        $material_record['usrid'] = $_SESSION['mid'];

        $data = $this->dataPrepare($material_record,$order_info['order_sn']);
        $result = json_decode($easypay_api->set_credit_info($data), true);
        if($result['code'] < 0){
            if($result['code'] == "-404"){
                throw new \Exception("JAVA 接口没有响应", 1);    
            }else if($result['code'] == "-500"){
                throw new \Exception("JAVA 接口异常", 1);    
            }else{
                throw new \Exception($result['resultText']['message'], 1);
            }
        }
    }

    private function dataPrepare($material_record, $order_sn){
        if(is_null($material_record)){
            throw new \Exception("找不到补充资料记录", 1);
        }


        $data['usrid']                      = $_SESSION['mid'];
        $data['order_sn']                   = $order_sn;
        $data['decoration_addr_province']   = $material_record['addr_province'];
        $data['decoration_addr_city']       = $material_record['addr_city'];
        $data['decoration_addr_county']     = $material_record['addr_county'];
        $data['decoration_addr_street']     = $material_record['addr_street'];
        $data['house_owner']                = $material_record['property_owner'];
        $data['build_area']                 = $material_record['house_area'];
        $data['house_price']                = $material_record['house_price'];
        $data['house_buy_type']             = $material_record['house_case'];
        $data['decoration_contract_price']  = $material_record['design_price'];
        $data['decoration_pay_type']        = $material_record['pay_way'];
        $data['decoration_contract_period'] = $material_record['period'];
        $data['credit_type']                = 1;

        if($material_record['other_pic_list'] == "null"){
            $data['other_pic_list']             = array(); 
        }else{
            $data['other_pic_list']             = $material_record['other_pic_list'];    
        }

        
        //3个列表
        $tmp_premise_permit_pic_list = json_decode($material_record['housing_pic_list'], true);
        foreach ($tmp_premise_permit_pic_list as $key => $value) {
            $value['order_sn'] = $order_sn;
            $tmp_premise_permit_pic_list[$key] = $value;

        }
        $tmp_decoration_contract_list = json_decode($material_record['fitment_pic_list'], true);
        foreach ($tmp_decoration_contract_list as $key1 => $value1) {
            $value1['order_sn'] = $order_sn;
            $tmp_decoration_contract_list[$key1] = $value1;

        }

        
        $data['premise_permit_pic_list'] = json_encode($tmp_premise_permit_pic_list);
        $data['decoration_contract_list'] = json_encode($tmp_decoration_contract_list);


        return $data;



    }


}