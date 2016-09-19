<?php
namespace Shop\Controller;
use Shop\Controller\MemberController;
class EasypayController extends MemberController {

	/*
	 * 乐装乐购介绍页面
	 */
	public function index(){
		$application_model = D('Easypayapplication');
		$shouxinapi = API('easypay');
		$result = $shouxinapi->get_credit_status(array("usrid"=>$_SESSION['mid']));
		$result = json_decode($result,true);
		if($result['code'] == 0){ //成功
			$data = $result['return_param'];
			$updata_data = $this->data_construct($data);
			$application_model->insert_update($_SESSION['member_id'], $updata_data);
			//写入session
            $this->_update_session($data['check_flag'], $data['loan_limit'], $data['loan_useble']);
		}
		$this->_check_state();
        //如果在申请中，跳转到申请中页面
        $pro_array = array(APPLYSTATUS_PROCESSING_1);
        if(in_array($_SESSION['easypay_credit_status'], $pro_array)){
                //跳转到审核中页面
                redirect(U('easypay/submitSuccess'));
        }
        $_SESSION['credit_type'] = $_GET['credit_type']==null?0:$_GET["credit_type"];
		$this->submitData();
	}

	/*
	 * 乐购资料提交页面
	 */
	public function submitData(){
		$this->_check_state();
        //用户的填写信息①保存在数据库中  ②条用java接口
        //初始化从数据库中读取数据初始化
        
        //初始化数据
        $application_model = D('easypay_application');
        $record = $application_model->where(array('member_id' => $_SESSION['member_id']))->find();
        $member_record = M('member')->where(array('member_id' => $_SESSION['member_id']))->find();

        //实例数据，在实现查询后注释掉
        $application = array();
        if(!is_null($member_record)){
            $application['usrid'] = $member_record['mid'];
        }
        
        /*初始化页面数据*/            
        $application = $this->_data_init($application, $record);

        $this->assign('app_info', $application);
		$this->display('submitData');
	}

	/**
	 * 保存用户授信提交信息
	 * @return [type] [description]
	 */
	public function save_application(){
		//写入数据库 
        //根据housetype 写入housepay
        //同时unset mortage 和 rent   
        if($_POST['house_type'] == 3) {
            $_POST['house_pay'] = $_POST['rent'];
        }else if ($_POST['house_type'] == 2){
            $_POST['house_pay'] = $_POST['mortage'];
            
        }
        unset($_POST['mortage']);
        unset($_POST['rent']);
        if(is_null($_POST['is_face_id_pass'])){
            $_POST['is_face_id_pass'] = 1;
        }
        $member_record  = M('member')->where(array('member_id' => $_SESSION['member_id']))->find();
        $_POST['usrid'] = is_null($member_record)?-1:$member_record['mid'];

        $app_model = D('Easypayapplication');
        $service = $_POST['service'];
        unset($_POST['service']);
        $result = $app_model->insert_update($_SESSION['member_id'], $_POST);
        if(!is_numeric($result) && $result !== 0){
            $stan_return['code'] = 0;
            $stan_return['resultText']['message'] = "存储失败";
            $this->ajaxReturn($stan_return);
            exit;
        }

        //聚信力
        if(!is_null($_POST["bondsmaninf_list"])){
            $tmp = json_decode(htmlspecialchars_decode($_POST['bondsmaninf_list']), true);
            $tmp2 = array();
            $relation = array(0,1,3);
            $relation_count = 0;
            $tmp3 = array();
            foreach ($tmp as $key => $value) {
                $tmp2[$key]['rel_usrname']      = $value['rel_usrname'];
                $tmp2[$key]['relation']         = $value['relation'];
                $tmp2[$key]['rel_mobile_phone'] = $value['rel_mobile_phone'];
                if(in_array($value['relation_id'], $relation)){
                    $relation_count++;
                }
                if(in_array($value['relation_id'], $tmp3)){
                    $stan_return['code'] = 0;
                    $stan_return['resultText']['message'] = "必须包含不同的两位直系亲属";
                    $this->ajaxReturn($stan_return);
                    exit;
                }else{
                    array_push($tmp3, $value['relation_id']);
                }
            }
            if($relation_count < 2){
                $stan_return['code'] = 0;
                $stan_return['resultText']['message'] = "必须包含不同的两位直系亲属";
                $this->ajaxReturn($stan_return);
                exit;
            }
            //聚信立联系人列表
            foreach ($tmp as $key => $value) {
                $bondsmaninf_list[$key]['contact_name']      = $value['rel_usrname'];
                $bondsmaninf_list[$key]['contact_type']      = $value['relation_id'];
                $bondsmaninf_list[$key]['contact_tel']       = $value['rel_mobile_phone'];
            }
            $_POST['bondsmaninf_list'] = json_encode($tmp2);
        }

        //聚信立参数重组
        $judata['usr_name']         = $_POST['usrname'];
        $judata['id_card']          = $_POST['id_card'];
        $judata['cell_phone']       = $_POST['mobile_phone'];
        $judata['home_addr']        = $_POST['add_areainfo'].$_POST['addr_street'];
        $judata['work_tel']         = $_POST['work_tel'];
        $judata['work_addr']        = $_POST['com_street'];
        $judata['home_tel']         = $_POST['home_phone'];
        $judata['bondsmaninf_list'] = json_encode($bondsmaninf_list,JSON_UNESCAPED_UNICODE);
        unset($_POST['usr_native_province']);
        unset($_POST['usr_native_city']);
        $_POST['addr_street'] = $_POST['add_areainfo'].$_POST['addr_street'];
        unset($_POST['add_areainfo']);
        //乐购：0 乐装：1
        $_POST['credit_type'] = $_SESSION['credit_type'];
        //调用java接口写入授.信数据
        $easypay_api = API('easypay');
        
        $result = json_decode($easypay_api->set_credit_info($_POST), true);
        if($result['code'] < 0){
            $stan_return['code'] = $result['code'];
            $stan_return['resultText']['message'] = $result['resultText']['message']?$result['resultText']['message']:$result['msg'];
            $this->ajaxReturn($stan_return);
            exit;
        }

        //java数据存储在session在第四步调用java设置新浪密码
        if($_SESSION['credit_type'] == 1){
            $condition['usrid']        = $_POST['usrid'];
            $condition['usrname']      = $_POST['usrname'];
            $condition['mobile_phone'] = $_POST['mobile_phone'];
            $condition['id_card']      = $_POST['id_card'];
            $condition['allwood_url']  = "http://".$_SERVER['SERVER_NAME']."/easypay/activateSucc";
            $condition['type']         = 1;
            $_SESSION["javadata"] = $condition;
        }
        
        
        //调用聚信立接口
        $jxl_api = API('juxinli');
        $jxl_result = json_decode($jxl_api->post_jxl_userdata($judata),true);
        if($jxl_result['code'] < 0){
            //\Think\Log::ext_log('juxinli response  = '.encode_json($jxl_result), 'api');
            $stan_return['code'] = $jxl_result['code'];
            //系统报错统一使用['resultText']['message']
            $stan_return['resultText']['message'] = $jxl_result['resultText']['message']?$jxl_result['resultText']['message']:$jxl_result['msg'];
            $this->ajaxReturn($stan_return);
            exit;
        }else{
            $jxl_return['phone'] = $_POST['mobile_phone'];
            $jxl_return['token'] = $jxl_result['resultText']['token'];
            $jxl_return['website'] = $jxl_result['resultText']['website'];
            $jxl_return['name'] = $jxl_result['resultText']['name'];
            $_SESSION['juxinli'] = $jxl_return;
            $stan_return['code'] = 1;
            $stan_return['resultText']['url'] = U('easypay/mobileConfirm');
            $this->ajaxReturn($stan_return);
            exit;
        }
	}

	/*
	 * 手机认证，电商认证
	 */
	public function mobileConfirm(){
		$phoneinfo['phone'] = $_SESSION['juxinli']['phone'];
        $phoneinfo['phone_name'] = $_SESSION['juxinli']['name'];
        $this->assign('phoneinfo',$phoneinfo);
		$this->display();
	}
	/**
     * 聚信立数据采集，java设置密码
     */
    public function do_jxl_sina(){
        if( $_SESSION['jxl_phonechekc'] != 1 ){
            $data['password'] = $_POST['phone_pwd'];
            $data['captcha'] = $_POST['captcha'];
            $data["type"] = 'SUBMIT_CAPTCHA';
            $phone_result = $this->do_jxl_phone($data);
            if($phone_result !== true){
                $this->ajaxReturn($phone_result);
                exit();
            }
            if(!empty($_POST['jd_username']) && !empty($_POST['jd_password'])){
                $data['jd_username'] = $_POST['jd_username'];
                $data['jd_password'] = $_POST['jd_password'];
                $jd_result = $this->do_jxl_b2c($data);   
                if($jd_result !== true){
                    $this->ajaxReturn($jd_result);
                    exit();
                }
            }else{
                $this->pass_jxl();
            }
        }
        $stan_return['code'] = 1;
        $stan_return['resultText']['url'] = U('easypay/creditConfirm');
        $this->ajaxReturn($stan_return);
    }
	/**
	 * 聚信立手机采集
	 */
	public function do_jxl_phone($data=null){
		$_SESSION['jxl_phonechekc'] = null;
        $judata['token'] = $_SESSION['juxinli']['token'];
        $judata['account'] = $_SESSION['juxinli']['phone'];
        $judata['website'] = $_SESSION['juxinli']['website'];
        if(!is_null($_POST['password'])){
            $judata['password'] = $_POST['password'];
        }else{
            $judata['password'] = $data['password'];
        }
        if(!is_null($data['captcha'])){
            $judata['captcha'] = $data['captcha'];
        }
        if(!is_null($data['type'])){
            $judata['type'] = $data['type'];
        }elseif($_POST['type'] == 1){
            $judata['type'] = 'RESEND_CAPTCHA';
        }
        //调用聚信立接口
        $jxl_api = API('juxinli');
        $jxl_result = json_decode($jxl_api->post_jxl_otherdata($judata),true);
        if($jxl_result['resultText']['code'] >=JXL_PWD_ERROR && $jxl_result['resultText']['code']<=JXL_ERROR && $jxl_result['resultText']['code'] != JXL_SUCCESS){
            $stan_return['code'] = 0;
            $stan_return['resultText']['message'] = C('JXL_STATE')[$jxl_result['resultText']['code']];
            if($_POST['ajax'] == 'yes'){$this->ajaxReturn($stan_return);exit();}
            return $stan_return;
            exit;
        }elseif($jxl_result['code'] < 0){
            if($jxl_result['code'] == "-404"){
                if($_POST['ajax'] == 'yes'){
                    $stan_return['code'] = -2;
                    $stan_return['url'] = U('easypay/creditConfirm');    
                }else{
                    $_SESSION['jxl_phonechekc'] = 1; 
                    $stan_return['code'] = 1;     
                    return true;
                }
            }else{
                $stan_return['code'] = $jxl_result['code'];
                $stan_return['resultText']['message'] = $jxl_result['resultText']['message']?$jxl_result['resultText']['message']:$jxl_result['msg'];
            }
            if($_POST['ajax'] == 'yes'){$this->ajaxReturn($stan_return);exit;}
            return $stan_return;
            exit;
        }elseif($jxl_result['resultText']['code'] == JXL_SUCCESS){
            $_SESSION['jxl_phonechekc'] = 1;
        }
        $stan_return['code'] = 1;
        if($_POST['ajax'] == 'yes'){$this->ajaxReturn($stan_return);exit;}
        return true;
	}

	/**
     * 分期购（聚信立），电商数据采集
     */
    public function do_jxl_b2c($data){
        $judata['token'] = $_SESSION['juxinli']['token'];
        $judata['account'] = $data['jd_username'];
       // $judata['website'] = 'jingdong';
        $judata['password'] = $data['jd_password'];
        //调用聚信立接口
        $jxl_api = API('juxinli');
        $jxl_result = json_decode($jxl_api->post_jxl_otherdata($judata),true);
        if($jxl_result['resultText']['code'] >=JXL_PWD_ERROR && $jxl_result['resultText']['code']<JXL_ERROR && $jxl_result['resultText']['code'] != JXL_SUCCESS){
            $stan_return['code'] = 0;
            $stan_return['resultText']['message'] = $jxl_result['resultText']['code'];
            return $stan_return;
            exit;
        }elseif($jxl_result['code'] < 0){
            \Think\Log::ext_log('juxinli response  = '.encode_json($jxl_result), 'api');
            $stan_return['code'] = $jxl_result['code'];
            $stan_return['resultText']['message'] = $jxl_result['resultText']['message']?$jxl_result['resultText']['message']:$jxl_result['msg'];
            return $stan_return;
        }
        return true;
    }
    /**
     * 分期购(聚信立)，跳过电商采集
     */
    public function pass_jxl(){
        $judata['token'] = $_SESSION['juxinli']['token'];
        $jxl_api = API('juxinli');
        $jxl_result = json_decode($jxl_api->pass_jxl_step($judata),true);
        if($jxl_result['code'] < 0){
            \Think\Log::ext_log('juxinli response  = '.encode_json($jxl_result), 'api');
            $stan_return['code'] = $jxl_result['code'];
            $stan_return['resultText']['message'] = $jxl_result['resultText']['message']?$jxl_result['resultText']['message']:$jxl_result['msg'];
            return $stan_return;
            exit;
        }
        return true;
    }

    /**
     * 分期购，JAVA设置新浪密码
     */
    public function do_sina_setpwd($data,$red_type=0){
        //调用java接口设置新浪密码
        $condition['usrid']        = $data['usrid'];
        $condition['usrname']      = $data['usrname'];
        $condition['mobile_phone'] = $data['mobile_phone'];
        $condition['id_card']      = $data['id_card'];
        $condition['allwood_url']  = $data['allwood_url'];
        $condition['type']         = $data['type'];
        $easypay_api = API('easypay');
        $result = json_decode($easypay_api->set_sina_pay_pwd($condition), true);
        if($result['code'] < 0 ){
            if($red_type != 0){
                $this->error($result['msg']);
            }
            $stan_return['code'] = $result['code'];
            $stan_return['resultText']['message'] = $result['resultText']['message']?$result['resultText']['message']:$result['msg'];
            \Think\Log::ext_log('sina response  = '.encode_json($result), 'api');
            return $stan_return;
            exit;
        }     
        //默认返回
        if($red_type == 0){
        	$stan_return['code'] = 1;
            $stan_return['resultText']['url'] = $result['return_param']['sina_paypwd_url'];
            \Think\Log::write('sinaurl ====================  '.encode_json($stan_return));
            return $stan_return;  
            exit();
        }else{
            redirect($result['return_param']['sina_paypwd_url']);
        }
        
    }
	/*
	 * 上传征信资料
	 */
	public function creditConfirm(){
		if(IS_AJAX){
			$easypay_api             = API('easypay');
	        $member_record  = M('member')->where(array('member_id' => $_SESSION['member_id']))->find();
	        $data['usrid'] = is_null($member_record)?-1:$member_record['mid'];
	        if($_POST['credit_type'] == 1){
	            $data['credit_report_url'] = $_POST['credit_src'];
	        }elseif($_POST['credit_type'] == 2){
	            $data['credit_report_url'] = null;
	        }
	        $result = json_decode($easypay_api->set_credit_info($data), true);
	        if($result['code'] < 0){
	            $stan_return['code'] = $result['code'];
	            $stan_return['resultText']['message'] = $result['resultText']['message']?$result['resultText']['message']:$result['msg'];
	            $this->ajaxReturn($stan_return);
	            exit;
	        }
	        if($_SESSION['credit_type'] == 0){
	        	$stan_return['code'] = 1;
	            $stan_return['resultText']['url'] = U('easypay/submitSuccess');
	            $this->ajaxReturn($stan_return);
	        }else{
	            $this->ajaxReturn($this->do_sina_setpwd($_SESSION['javadata']));
	        }
		}else{
			$this->display();
		}
	}

	/*
	 * 等待审核
	 */
	public function submitSuccess(){
		$easypay_api             = API('easypay');
        $condition['usrid']      = $_SESSION['mid'];
        $condition['check_flag'] = '1';
        $result = json_decode($easypay_api->set_credit_status($condition), true);
        if($result['code'] == -1){//返回失败
        }    
		$this->display();
	}

	/*
	 * 开始激活页面
	 */
	public function activate(){
        updateEasypayStatus();
        $this->display();
	}

    /**
     *  执行激活操作
     */
    public function do_activate(){
        $member_record  = M('member')->where(array('member_id' => $_SESSION['member_id']))->find();
        $data['usrid'] = is_null($member_record)?-1:$member_record['mid'];
        $app_model = D('easypay_application');
        $result = $app_model->where(array('member_id' => $_SESSION['member_id']))->find();
        $condition['usrid']        = $data['usrid'];
        $condition['usrname']      = $result['usrname'];
        $condition['mobile_phone'] = $result['mobile_phone'];
        $condition['id_card']      = $result['id_card'];
        $condition['allwood_url']  = "http://".$_SERVER['SERVER_NAME']."/easypay/activateSucc";
        $condition['type']         = 0;
        $this->do_sina_setpwd($condition,1);
    }

	/*
	 * 乐购（乐装）激活成功
	 */
	public function activateSucc(){
        updateEasypayStatus();
        $type = $_GET['type']==null?0:$_GET['type'];
        $member_record  = M('member')->where(array('member_id' => $_SESSION['member_id']))->find();
        $data['usrid'] = is_null($member_record)?-1:$member_record['mid'];
        $easypay_api = API('easypay');
        $result = json_decode($easypay_api->set_usr_activate($data), true);
        if($result['code'] == -1){//返回失败
                
        }
        if($type == 0){
            $credit = $_SESSION['easypay_credit_total'];
            $this->assign('credit',$credit);
        }else{
            $condition['usrid']      = $_SESSION['mid'];
            $condition['check_flag'] = '1';
            $result = json_decode($easypay_api->set_credit_status($condition), true);
        }
        $this->assign('type',$type);
		$this->display();
	}

	/*
	 * 补充资料（乐装专用)
     * IS_AJAX 执行保存补充资料
	 */
	public function moreMaterial(){
        if(IS_AJAX){
            $this->jsonFail('test');
            $more_material_model = D('MoreMaterial');
            $_POST['housing_pic_list'] = json_encode($_POST['housing_pic_list']);
            $_POST['fitment_pic_list'] = json_encode($_POST['fitment_pic_list']);
            $_POST['other_pic_list'] = json_encode($_POST['other_pic_list']);
            $id = $more_material_model->add($_POST);
            if($id){
                $re['code'] = 1;
                $re['resultText']['message'] = "保存成功!";
                $re['resultText']['url'] = U('thomedesign/buy', array('plan_id'=>$_POST['plan_id'], 'period'=>$_POST['period'], 'x'=>$id));
                $this->ajaxReturn($re);
            }else{
                $return['code'] = 0;
                $return['resultText']['message'] ="数据库写入错误";
                $this->ajaxReturn($return);
            }
        }else{
            $this->display();
        }
	}

    /*
     * 保存补充资料
     */
    public function saveApplicationMore(){
          $more_material_model = D('MoreMaterial');
            $_POST['housing_pic_list'] = json_encode($_POST['housing_pic_list']);
            $_POST['fitment_pic_list'] = json_encode($_POST['fitment_pic_list']);
            $_POST['other_pic_list'] = json_encode($_POST['other_pic_list']);
            $id = $more_material_model->add($_POST);
            if($id){
                $re['code'] = 1;
                $re['resultText']['message'] = "保存成功!";
                $re['resultText']['url'] = U('THomedesign/check', array('plan_id'=>$_POST['plan_id'], 'period'=>$_POST['period'], 'x'=>$id));
                $this->ajaxReturn($re);
            }else{
                $return['code'] = 0;
                $return['resultText']['message'] ="数据库写入错误";
                $this->ajaxReturn($return);
            }
    
    }

    /*
     * 乐装申请乐购
     */
    public function easypayAfterLezhuang(){
        $this->display();
    }

	//数据构造
	private function data_construct($data){
        if(is_null($data))
            return array();
        //总额度
        $arr['credit_total']     = $data['loan_limit'];
        //可用额度
        $arr['credit_available'] = $data['loan_useble'];
        //授信状态
        $arr['credit_status']    = $data['check_flag'];
        return $arr;
    }

    //更新
    private function _update_session($status, $total, $available){
        $_SESSION['easypay_credit_status'] = $status;
        $_SESSION['easypay_credit_total'] = $total;
        $_SESSION['easypay_credit_available'] = $available;
        $_SESSION['easypay_freeze'] = $_SESSION['easypay_credit_status'] === -1 ? 1:0;;
        $_SESSION['easypay_status_zh'] = str_replace(
            array(0, 1, 3, 2, 5, 4, 6),
            array('未开启', '审批中','审批中', '审批未通过', '已开通', '审批未通过','冻结' ),
            intval($_SESSION['easypay_credit_status']));
    }

    /*
    * 判断用户的授信状态，跳转到不同的页面
     */
    private function _check_state(){
        return true;
        if(is_null($_SESSION['easypay_credit_status'])){
            //没有授信信息
            return ;
        }
        
        $credit_status = $_SESSION['easypay_credit_status'];
        switch ($credit_status){
            //未申请
            case APPLYSTATUS_NONE:
                redirect(U('easypay', 'apply_for_credit_step1'));
                break;
            //审批中（提交申请，未通过审批）
            case APPLYSTATUS_NEW:
                redirect(U('easypay', 'application_in_processing'));
                break;
            //审批失败， 一审未通过
            case APPLYSTATUS_PROCESSING_1_FAIL:
                redirect(U('easypay', 'apply_for_credit_step1'));
                break;
            //审批中，复审中
            case APPLYSTATUS_PROCESSING_1:
                redirect(U('easypay', 'application_in_processing'));
                break;
            //审批失败， 二审未通过
            case APPLYSTATUS_PROCESSING_2_FAIL:
                redirect(U('easypay', 'apply_for_credit_step1'));
                break;
            //审批通过
            case APPLYSTATUS_PROCESSING_2:
                redirect(U('easypay', 'apply_for_credit_step1', array('type' => 1)));
                break;
            //账户冻结
            case APPLYSTATUS_FREEZE:
                redirect(U('member/index'));
                break;                
            
            default:
                # code...
                break;
        }
    }

    /*
     * 初始化授信提交页面
     * @param  [type] $application [description]
     * @return [type]              [description]
     */
    private function _data_init($application, $record){
         $application['usrname']             = $record['usrname']?$record['usrname']:"";
         $application['id_card']             = $record['id_card']?$record['id_card']:"";
         $application['sex']                 = $record['sex']?$record['sex']:"0";
         $application['marital']             = $record['marital']?$record['marital']:"0";
         $application['addr_province']       = $record['addr_province']?$record['addr_province']:0 ;//北京
         $application['addr_city']           = $record['addr_city']?$record['addr_city']:0;
         $application['addr_county']         = $record['addr_county']?$record['addr_county']:0;
         $application['addr_street']         = $record['addr_street']?$record['addr_street']:"";
         $application['usr_native']          = $record['usr_native']?$record['usr_native']:"";
         $application['usr_native_province'] = $record['usr_native_province']?$record['usr_native_province']:0;
         $application['usr_native_city']     = $record['usr_native_city']?$record['usr_native_city']:0;
         $application['diploma']             = $record['diploma']?$record['diploma']:0;
         $application['mobile_phone']        = $record['mobile_phone']?$record['mobile_phone']:"";
         $application['home_phone']          = $record['home_phone']?$record['home_phone']:"";
         $application['profession']          = $record['profession']?$record['profession']:"";
         $application['profession_level']    = $record['profession_level']?$record['profession_level']:"";
         $application['com_name']            = $record['com_name']?$record['com_name']:"";
         $application['com_street']          = $record['com_street']?$record['com_street']:"";
         $application['income']              = $record['income']?$record['income']:"";
         $application['working_long']        = $record['working_long']?$record['working_long']:"";
         $application['house_type']          = $record['house_type']?$record['house_type']:"";
         $application['graduate_school']     = $record['graduate_school']?$record['graduate_school']:"";
         $application['graduate_time']       = $record['graduate_time']?date("Y-m-d",strtotime($record['graduate_time'])):"";
         $application['hiredate']            = $record['hiredate']?date("Y-m-d",strtotime($record['hiredate'])):"";
         //如果房租为租赁，填写租金
         $application['rent']                = $record['house_type'] == 3?$record['house_pay']:0;
         //如果是按揭，填写按揭款
         $application['mortage']             = $record['house_type'] == 2?$record['house_pay']:0;
         $application['house_pay']           = $record['house_pay']?$record['house_pay']:"0";
         $application['estates']             = $record['estates']?$record['estates']:"0";
         $application['car_assets']          = $record['car_assets']?$record['car_assets']:"0";
         $application['securities']          = $record['securities']?$record['securities']:"0";
         $application['other_assets']        = $record['other_assets']?$record['other_assets']:"0";
         $application['id_card_front_pic']   = $record['id_card_front_pic']?json_decode(htmlspecialchars_decode($record['id_card_front_pic']), true):array("s_pic"=>"","m_pic"=>"");
         
         $application['id_card_reverse_pic'] = $record['id_card_reverse_pic']?json_decode(htmlspecialchars_decode($record['id_card_reverse_pic']), true):array("s_pic"=>"","m_pic"=>"");
         $application['is_face_id_pass']     = $record['is_face_id_pass']?$record['is_face_id_pass']:"1";
         $application['with_id_card_pic']    = $record['with_id_card_pic']?json_decode(htmlspecialchars_decode($record['with_id_card_pic']), true):array("s_pic"=>"","m_pic"=>"");
         $application['other_pic_list']      = $record['other_pic_list']?json_decode(htmlspecialchars_decode($record['other_pic_list']), true):array();
         //空联系人列表
         $empty_bondsmaninf_list                            = array(0=>array("rel_usrname"=>"","relation"=>"","rel_mobile_phone"=>""),
                                                                    1=>array("rel_usrname"=>"","relation"=>"","rel_mobile_phone"=>""),
                                                                    2=>array("rel_usrname"=>"","relation"=>"","rel_mobile_phone"=>""));
         $bondsmaninf_list_arr = json_decode(htmlspecialchars_decode($record['bondsmaninf_list']), true);
         $application['bondsmaninf_list']                   = empty($bondsmaninf_list_arr)?$empty_bondsmaninf_list:$bondsmaninf_list_arr;
         return $application;
    }
}