<?php
namespace Admin\Controller;
use Admin\Controller\AdminController;
class StoreController extends AdminController {
	//店铺管理
    public function shopManager(){
        $page_num = I('get.p')==null?0:I('get.p');
    	if(trim(I('get.owner_and_name')) != ''){
			$condition['member_name']	= array('like', '%'.trim($_GET['owner_and_name']).'%');
			$this->assign('owner_and_name',trim($_GET['owner_and_name']));
		}
		if(trim($_GET['store_name']) != ''){
			$condition['store_name']	= array('like', '%'.trim($_GET['store_name']).'%');
			$this->assign('store_name',trim($_GET['store_name']));
		}
		if(intval($_GET['grade_id']) > 0){
			$condition['grade_id']		= intval($_GET['grade_id']);
			$this->assign('grade_id',intval($_GET['grade_id']));
		}


        if(intval($_GET['com_type']) > 0){
            $condition['com_type']      = intval($_GET['com_type']);
            $this->assign('com_type',intval($_GET['com_type']));
        }
        if(intval($_GET['business_type']) > 0){
            $condition['business_type']      = intval($_GET['business_type']);
            $this->assign('business_type',intval($_GET['business_type']));
        }
        $has_terminal='';
        if($_GET['has_terminal']){
            $has_terminal=$_GET['has_terminal'];
        }                

        switch (I('get.store_type')) {
            case 'close':
                $condition['store_state'] = 0;
                break;
            case 'open':
                $condition['store_state'] = 1;
                break;
            case 'expired':
                $condition['store_end_time'] = array('between', array(1, time()));
                $condition['store_state'] = 1;
                break;
            case 'expire':
                $condition['store_end_time'] = array('between', array(time(), time() + 864000));
                $condition['store_state'] = 1;
                break;
        }


		//店铺列表
		$model_store = D('Shop/Store');
		$store_list = $model_store->getStoreList2($condition, $page_num.',10', 'store_time desc','*','',$has_terminal);
		//店铺等级
		$model_grade = D('Storegrade');
		$grade_list = $model_grade->getGradeList($condition);
		if (!empty($grade_list)){
			$search_grade_list = array();
			foreach ($grade_list as $k => $v){
				$search_grade_list[$v['sg_id']] = $v['sg_name'];
			}
		}
        $store_count = $model_store->getStoreCount($condition);
        $Page = new \Think\Page($store_count,10);
        $show = $Page->show();
        $this->assign('page',$show);
		$this->assign('search_grade_list', $search_grade_list);
		$this->assign('grade_list',$grade_list);
		$this->assign('store_list',$store_list);
        $this->assign('store_type', C('STORE_TYPE'));
        $this->display();
    }

    //开店申请
    public function shopJoinin(){
        $page_num = I('get.p')==null?0:I('get.p');
    	if(!empty($_GET['owner_and_name'])) {
			$condition['member_name'] = array('like','%'.trim($_GET['owner_and_name']).'%');
		}
		if(!empty($_GET['store_name'])) {
			$condition['store_name'] = array('like','%'.trim($_GET['store_name']).'%');
		}
		if(!empty($_GET['grade_id']) && intval($_GET['grade_id']) > 0) {
			$condition['sg_id'] = $_GET['grade_id'];
		}
		if(!empty($_GET['joinin_state']) && intval($_GET['joinin_state']) > 0) {
            $condition['joinin_state'] = $_GET['joinin_state'] ;
        } else {
            $condition['joinin_state'] = array('gt',0);
        }
        if(intval($_GET['com_type']) > 0){
            $condition['com_type']      = intval($_GET['com_type']);
            $this->assign('com_type',intval($_GET['com_type']));
        }
        if(intval($_GET['business_type']) > 0){
            $condition['business_type']      = intval($_GET['business_type']);
            $this->assign('business_type',intval($_GET['business_type']));
        }
		$model_store_joinin = D('Shop/StoreJoinin');
		$store_list = $model_store_joinin->getList($condition, $page_num.',10', 'joinin_state asc ,tesu_created desc');
		$this->assign('store_list', $store_list);
        $this->assign('joinin_state_array', C('STORE_JOIN_STATE'));

		//店铺等级
		$model_grade = D('Storegrade');
		$grade_list = $model_grade->getGradeList();

        $store_count = $model_store_joinin->getStoreJoininCount($condition);
        $Page = new \Think\Page($store_count,10);
        $show = $Page->show();
        $this->assign('page',$show);
		$this->assign('grade_list', $grade_list);
        $this->display();
    }

    //开店审核
    public function storeJoinInDetail(){
    	$model_store_joinin = D('Shop/StoreJoinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id'=>$_GET['member_id']));
        $model_store_grade = D('Storegrade');
        $grade = $model_store_grade->getOneGrade($joinin_detail['sg_id']);
        $joinin_detail_title = '查看';
        if(in_array(intval($joinin_detail['joinin_state']), array(STORE_JOIN_STATE_NEW, STORE_JOIN_STATE_PAY, STORE_JOIN_STATE_VERIFYING))) {
            $joinin_detail_title = '审核';
        }
        $model_store_fund = D('StoreFund');
        $condition['store_fund_member_id'] = $_SESSION['member_id'];
        $condition['store_fund_payment_state'] = 1;
        $list = $model_store_fund->getStoreFundRechargeList($condition);

        //如果入驻不用缴费，则另取入驻时间
        if (isset($_GET['store_id'])){
            $store_model = D('Shop/Store');
            $store_info = $store_model->getStoreInfoByID($_GET['store_id']);
            $joinin_detail['baozhengjin']=$store_info['baozhengjin'];
            $this->assign('ruzhu_time', $store_info['store_time']);
        }

        $this->assign('list', $list);
        $this->assign('joinin_detail_title', $joinin_detail_title);
		$this->assign('joinin_detail', $joinin_detail);
        $this->assign('grade_detail', $grade);
        $this->display();
    }

    //审核操作
    public function storeJoinInVerify(){
    	$model_store_joinin = D('Shop/StoreJoinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id'=>$_POST['member_id']));

        switch (intval($joinin_detail['joinin_state'])) {
            //开店初审
            case STORE_JOIN_STATE_NEW:
                $this->store_joinin_verify_pass($joinin_detail);
                break;
            
            case STORE_JOIN_STATE_VERIFYING:
                $this->store_joinin_verify_open_02($joinin_detail);
            break;
            //新版开店流程，去掉了初审和支付 支付状态码
            //new 10 -> 80 审核中  -> 审核完成 40
            default:
                $this->error('参数错误','');
                break;
        }
    }

    /**
     * 店铺编辑
     */
    public function storeEdit(){

        $model_store = D('Shop/Store');
        //保存
        if (IS_POST){
            //取店铺等级的审核
            $model_grade = D('Storegrade');
            // //结束时间
            // $time   = '';
            // if(trim($_POST['end_time']) != ''){
            //     $time = strtotime($_POST['end_time']);
            // }
            $update_array = array();
            $update_array['store_name'] = trim($_POST['store_name']);
            $update_array['sc_id'] = intval($_POST['sc_id']);
            // $update_array['grade_id'] = intval($_POST['grade_id']);
            // $update_array['store_end_time'] = $time;
            $update_array['store_state'] = intval($_POST['store_state']);
            $update_array['is_discount'] = $_POST['is_discount'];
            $update_array['ser_charge'] = $_POST['ser_charge'];
            // $update_array['downpayment'] = $_POST['downpayment'];
            if ($_POST['store_state'] == '0'){
                //根据店铺状态修改该店铺所有商品状态
                $model_goods = D('Shop/Goods');
                $model_goods->editProducesOffline(array('store_id' => $update_array['store_id']));
                $update_array['store_close_info'] = trim($_POST['store_close_info']);
                $update_array['store_recommend'] = 0;
            }else {
                //店铺开启后商品不在自动上架，需要手动操作
                $update_array['store_close_info'] = '';
                $update_array['store_recommend'] = intval($_POST['store_recommend']);
            }
            $result = $model_store->editStore($update_array, array('store_id' => $_POST['store_id']));
            if ($result){
                $store_info = $model_store->getStoreInfoByID($_POST['store_id']);
                $model_store_joinin = D('Shop/StoreJoinin');
                $update_array = array();
                $update_array['is_discount'] = $_POST['is_discount'];
                $update_array['ser_charge'] = $_POST['ser_charge'];
                $update_array['downpayment'] = $_POST['downpayment'];
                $model_store_joinin->modify($update_array, array('member_id'=>$store_info['member_id']));
                //$this->log(L('nc_edit,store').'['.$_POST['store_name'].']',1);
                $this->success('返回店铺列表',__CONTROLLER__.'/shopmanager');
            }else {
               // $this->log(L('nc_edit,store').'['.$_POST['store_name'].']',1);
                $this->error('保存失败');
            }
        }
        //取店铺信息
        $store_array = $model_store->getStoreInfoByID($_GET['store_id']);
        if (empty($store_array)){
            $this->error('该店铺不存在');
        }
        //整理店铺内容
        $store_array['store_end_time'] = $store_array['store_end_time']?date('Y-m-d',$store_array['store_end_time']):'';
        //店铺分类
        $model_store_class = D('Shop/StoreClass');
        $parent_list = $model_store_class->getTreeClassList(2);
        if (is_array($parent_list)){
            foreach ($parent_list as $k => $v){
                $parent_list[$k]['sc_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['sc_name'];
            }
        }
        //店铺等级
        $model_grade = D('Storegrade');
        $grade_list = $model_grade->getGradeList();
        $this->assign('grade_list',$grade_list);
        $this->assign('class_list',$parent_list);
        $this->assign('store_array',$store_array);
        $this->display();
    }

    /**
     * 装修公司效果图风格
     * @return [type] [description]
     */
    public function effectdrawType(){
        $conditon = array();
        if ($_GET['type_name'] != ''){
            $conditon['type_name'] = array('like','%'.$_GET['type_name'].'%');
            $this->assign('type_name',$_GET['type_name']);
        }

        $model = D('Shop/EffectdrawType');
        $type_list = $model->selectEffectdrawType($conditon);
        $this->assign('type_list',$type_list);
        $this->display();
    }

    /**
     * 装修公司效果图风格--add/edit
     * @return [type] [description]
     */
    public function effectdrawTypeOperate(){
        $model = D('Shop/EffectdrawType');
        if (IS_POST){
            $type_name = trim($_POST['type_name']);
            if ($type_name == ''){
                $this->error('名称不能为空');
            }
            $data = array();
            $data['type_name'] = $type_name;
            $data['status'] = intval($_POST['status']);
            if (isset($_POST['id']) && $_POST['id'] > 0){
                $condition['id'] = $_POST['id'];
                $res = $model->editEffectdrawType($data, $condition);
            }else{
                $condition['type_name'] = $data['type_name'];
                $rs = $model->findEffectdrawType($condition);
                if($rs){
                    $this->error('该风格名称已存在',__CONTROLLER__.'/effectdrawType');
                    exit();
                }else{
                    $data['add_time'] = $_SERVER['REQUEST_TIME'];
                    $res = $model->addEffectdrawType($data);    
                }
            }

            if ($res){
                $this->success('操作成功',__CONTROLLER__.'/effectdrawType');
                exit();
            }else{
                $this->error('操作失败',__CONTROLLER__.'/effectdrawType');
                exit();
            }
        }

        if (isset($_GET['id']) && $_GET['id'] > 0){
            $type_info = $model->findEffectdrawType(array('id' => $_GET['id']));
            $this->assign('type_info',$type_info);
        }
        $this->display();
    }

    /**
     * 装修公司效果图风格--change
     * @return [type] [description]
     */
    public function changeEffectdrawType(){
        $id = intval($_GET['id']);
        if ($id <= 0){
            $this->error('ID错误');
        }else{
            $model = D('Shop/EffectdrawType');
            $condition['id'] = $id;
            $update['status'] = intval($_GET['status']);
            $res = $model->editEffectdrawType($update, $condition);
            if ($res){
                $this->success('操作成功');
            }else{
                $this->error('操作失败');
            }
        }
    }

    /**
     * 分期购配置
     * @return [type] [description]
     */
    public function mortageBuy(){
        $model = D('Shop/Setting');
        //分期信息
        $pay_by_period = $model->getRowSetting('pay_by_period');

        //是否开启分期购
        $easypay_status = $model->getRowSetting('easybuy_status');
        $easypay_status = $easypay_status['value'];

        $rate_arr = unserialize(strval($pay_by_period['value']));
        if(is_null($easypay_status) || empty($easypay_status)){
            $easypay_status = 0;
        }

        $this->assign('rate_arr', $rate_arr);
        $this->assign('status', $easypay_status);

        if (IS_POST){
            $easypay_arr = $_POST['easypay'];
            $easypay_status = $_POST['easypay_status'];

            $insert_arr = array();
            if (!empty($easypay_arr)){
                $duration = $easypay_arr['duration'];
                $rate = $easypay_arr['rate'];
                if (!empty($duration)){
                    foreach ($duration as $k => $num) {
                        if ($this->num_judge($num) && isset($rate[$k]) && $this->num_judge($rate[$k])){
                            $insert_arr[$num] = $rate[$k];
                        }
                    }
                }
            }
            if (!empty($insert_arr)){
                $param['pay_by_period'] = serialize($insert_arr);
                
                $param['easybuy_status'] = $easypay_status;
                $result = $model->updateSetting($param);

                if ($result > 0){
                    $this->success('平台服务费设置成功');
                    exit();
                }else {
                    $this->error('平台服务费设置失败');
                    exit();
                }
            }else{
                $this->error('平台服务费设置失败');
                exit();
            }
        }

        $this->display();
    }

    private function num_judge($num){
        if (is_numeric($num) && $num > 0 && $num < 100){
            return true;
        }
        return false;
    }

    private function store_joinin_verify_pass($joinin_detail) {
    	print_r($_POST);exit;
        $param = array();
        $param['joinin_state'] = $_POST['verify_type'] === 'pass' ? STORE_JOIN_STATE_PAY : STORE_JOIN_STATE_VERIFY_FAIL;
        $param['joinin_message'] = $_POST['joinin_message'];
        $param['store_class_commis_rates'] = implode(',', $_POST['commis_rate']);
        $param['is_discount'] = $_POST['is_discount'];        
        $param['ser_charge'] = $_POST['ser_charge'];
        $param['downpayment'] = $_POST['downpayment'];
        $model_store_joinin = Model('store_joinin');
        $model_store_joinin->modify($param, array('member_id'=>$_POST['member_id']));

        
        showMessage('店铺入驻申请审核完成','index.php?act=store&op=store_joinin');
    }


    private function store_joinin_verify_open_02($joinin_detail) {
        $model_store_joinin = D('Shop/StoreJoinin');
        $model_store    = D('Shop/Store');
        $model_seller = D('Shop/Seller');

        //验证卖家用户名是否已经存在
        if($model_seller->isSellerExist(array('seller_name' => $joinin_detail['seller_name']))) {
            $this->error('卖家用户名已存在','');
        }
        $store_info = $model_store_joinin->where(array('member_id'=>$_POST['member_id']))->find();

        if(!is_null($store_info)){
            $mobile = $store_info['company_phone'];
        }
        $param = array();
        $param['joinin_state'] = $_POST['verify_type'] === 'pass' ? STORE_JOIN_STATE_FINAL : STORE_JOIN_STATE_VERIFY_FAIL;
        $param['joinin_message'] = $_POST['joinin_message'];
        $param['is_discount'] = $_POST['is_discount'];
        $param['ser_charge'] = $_POST['ser_charge'];
        $param['downpayment'] = $_POST['downpayment'];
        $model_store_joinin->modify($param, array('member_id'=>$_POST['member_id']));
        //审核通过
        if($_POST['verify_type'] === 'pass') {
            //开店
            $shop_array     = array();
            $shop_array['member_id']    = $joinin_detail['member_id'];
            $shop_array['member_name']  = $joinin_detail['member_name'];
            $shop_array['seller_name'] = $joinin_detail['seller_name'];
            $shop_array['grade_id']     = $joinin_detail['sg_id'];
            $shop_array['store_owner_card']= '';
            $shop_array['store_name']   = $joinin_detail['store_name'];
            $shop_array['sc_id']        = $joinin_detail['sc_id']?$joinin_detail['sc_id']:"123";
            $shop_array['store_company_name'] = $joinin_detail['company_name'];
            $shop_array['area_id']      = 0;
            $shop_array['area_info']    = $joinin_detail['company_address'];
            $shop_array['store_address']= $joinin_detail['company_address_detail'];
            $shop_array['store_zip']    = '';
            $shop_array['store_tel']    = $joinin_detail['company_phone'];
            $shop_array['store_zy']     = '';
            $shop_array['store_state']  = 1;
            $shop_array['store_time']   = time();
            $shop_array['is_discount'] = $_POST['is_discount'];
            $shop_array['ser_charge'] = $_POST['ser_charge'];
            $shop_array['downpayment'] = $_POST['downpayment'];
            $shop_array['baozhengjin'] = $_POST['baozhengjin'];
            $shop_array['com_type'] = $joinin_detail['com_type'];
            $shop_array['business_type'] = $joinin_detail['business_type'];
            //$shop_array['store_label'] = C('default_store_label');
            $shop_array['store_label'] = '/data/upload/shop/common/'.C('seller_center_logo');
            
            //$shop_array['store_banner'] = C('default_store_banner');
            $shop_array['store_banner'] = '/data/upload/shop/common/'.C('seller_center_banner');
            $shop_array['store_slide'] = C('default_slide');
            $shop_array['province_id'] = $joinin_detail['province_id'];
            $shop_array['city_id'] = $joinin_detail['city_id'];
            
            $store_id = $model_store->addStore($shop_array);

            if($store_id) {
                //写入卖家帐号
                $seller_array = array();
                $seller_array['seller_name'] = $joinin_detail['seller_name'];
                $seller_array['member_id'] = $joinin_detail['member_id'];
                $seller_array['seller_group_id'] = 0;
                $seller_array['store_id'] = $store_id;
                $seller_array['is_admin'] = 1;
                $state = $model_seller->addSeller($seller_array);

                //$this->bindAllClass($store_id);
            }

            if($state) {
                // 添加相册默认
                $album_model = D('Shop/Album');
                $album_arr = array();
                $album_arr['aclass_name'] = '默认相册';
                $album_arr['store_id'] = $store_id;
                $album_arr['aclass_des'] = '';
                $album_arr['aclass_sort'] = '255';
                $album_arr['aclass_cover'] = '';
                $album_arr['upload_time'] = time();
                $album_arr['is_default'] = '1';
                $result1 = $album_model->addClass($album_arr);

                $model = M('store_extend');
                //插入店铺扩展表
                $model->add(array('store_id'=>$store_id));

                $msg = '恭喜您，您的店铺创建成功。'.($store_grade['sg_confirm'] == 1 ? '等待管理员审核。' : '');

                //插入店铺绑定分类表
                $store_bind_class_array = array();
                $store_bind_class = unserialize($joinin_detail['store_class_ids']);
                $store_bind_commis_rates = explode(',', $joinin_detail['store_class_commis_rates']);
                for($i=0, $length=count($store_bind_class); $i<$length; $i++) {
                    list($class1, $class2, $class3) = explode(',', $store_bind_class[$i]);
                    $store_bind_class_array[] = array(
                        'store_id' => $store_id,
                        'commis_rate' => $store_bind_commis_rates[$i],
                        'class_1' => $class1,
                        'class_2' => $class2,
                        'class_3' => $class3,
                    );
                }
                $model_store_bind_class = D('Shop/StoreBindClass');
                $result2 = $model_store_bind_class->addStoreBindClassAll($store_bind_class_array);

                //发送短信
                if(!APP_DEBUG&&(C('MODE') != DEBUG)&&(C('MODE') != TEST)){
                    sendOpenStoreSucc($mobile);
                }
                $this->success('店铺开店成功',__CONTROLLER__.'/shopmanager');
            } else {
                if(!APP_DEBUG&&(C('MODE') != DEBUG)&&(C('MODE') != TEST)){
                    sendOpenStoreFail($mobile);
                }
                $this->error('店铺开店失败',__CONTROLLER__.'/shopjoinin');
            }
        } else {
            if(!APP_DEBUG&&(C('MODE') != DEBUG)&&(C('MODE') != TEST)){
                sendOpenStoreFail($mobile);
            }
            $this->success('店铺开店拒绝',__CONTROLLER__.'/shopjoinin');
        }
    }
}