<?php
/*
 * 商家入驻控制器
 */

namespace Shop\Controller;
use Shop\Controller\MemberController;
class StartBusinessController extends MemberController {
    private $joinin_detail = NULL;
    private $joinin_state = 12;
    const STORE_JOIN_STATE_FINAL = 40;       //开店成功
    const STORE_JOIN_STATE_VERIFY_FAIL = 30;       //初审失败
    const STORE_JOIN_STATE_VERIFYING = 12;       //开店申请审核中

	public function __construct() {
    parent::__construct();

    $model_store_joinin = D('StoreJoinin');
    $joinin_info = $model_store_joinin->getOne(array('member_id' => $_SESSION['member_id']));
    if(empty($joinin_info)) {
        $this->joinin_state =0;
    }else if(!empty($joinin_info) && $joinin_info['joinin_state'] == self::STORE_JOIN_STATE_FINAL){
        //开店完成
        session_unset();
        session_destroy();
        $this->joinin_state = 40;
//        $this->redirect('login/index');
    }else if(!empty($joinin_info) && $joinin_info['joinin_state'] == self::STORE_JOIN_STATE_VERIFY_FAIL){
        //开店初审失败
        $this->joinin_state =self::STORE_JOIN_STATE_VERIFY_FAIL;
        //redirect(U('/shop/StartBusiness/verifying'));
    }else{
        $this->joinin_state = 12;
        //店铺审核中
        //redirect(U('/shop/StartBusiness/index'));
    }
    //$this->behaveMyself($this->joinin_state);
	}

    /*
     * 商家入驻介绍页
     */
    public function index(){
        $this->behaveMyself(array(0, self::STORE_JOIN_STATE_VERIFY_FAIL));
        $this->display();
    }

    /*
    * 协议页面
    */
    public function step1(){
        $this->behaveMyself(array(0,  self::STORE_JOIN_STATE_VERIFY_FAIL));
        $model_document = D('Document');
        $document_info = $model_document->getOneByCode('open_store');
        $this->assign('agreement',$document_info['doc_content']);
        $this->display();
    }

   /*
    * 选择开店类型
    * 家居经销商/装修公司
    */
   public function step2(){
        $this->behaveMyself(array(0,  self::STORE_JOIN_STATE_VERIFY_FAIL));
        $this->display();
   }

   /*
    * 选择个体 企业
    */
   public function enterOrIndiv(){
      $this->behaveMyself(array(0,  self::STORE_JOIN_STATE_VERIFY_FAIL));
      $this->display('enterOrIndiv');
   }

   /*
    * 资料
    */
   public function step3(){
       if($this->joinin_state != self::STORE_JOIN_STATE_VERIFY_FAIL){
           $this->behaveMyself(array(0, self::STORE_JOIN_STATE_VERIFY_FAIL));
       }else{
        //获取企业类型
        //审核失败编辑
        $model_store_joinin = D('StoreJoinin');
        $joinin_info = $model_store_joinin->getOne(array('member_id' => $_SESSION['member_id']));
        //个人资料json
        $com_info['store_name']                         = $joinin_info['store_name'];
        $com_info['company_name']                       = $joinin_info['company_name'];
        $com_info['company_province_id']                = $joinin_info['province_id'];
        $com_info['company_city_id']                    = $joinin_info['city_id'];
        $com_info['company_area_id']                    = $joinin_info['area_id2'];
        $com_info['company_address_detail']             = $joinin_info['company_address_detail'];
        $com_info['company_phone']                      =$joinin_info['company_phone'];
        $com_info['business_licence_number']            = $joinin_info['business_licence_number'];
        $com_info['business_licence_number_electronic'] =$joinin_info['business_licence_number_electronic'];
        $com_info['organization_code_electronic'] =$joinin_info['organization_code_electronic'];
        $com_info['organization_code']                  = $joinin_info['organization_code'];
        $com_info['representive']                       = $joinin_info['representive'];
        $com_info['representive_id']                    = $joinin_info['representive_id'];//"422201198808046452";
        $com_info['representive_id_start']              = $joinin_info['representive_id_start'];
        $com_info['representive_id_end']                = $joinin_info['representive_id_end'];
        $com_info['representive_id_front_electronic']   = $joinin_info['representive_id_front_electronic'];
        $com_info['representive_id_back_electronic']    = $joinin_info['representive_id_back_electronic'];

        $this->assign('com_info', $com_info);
        
       }
        $this->display();
   }

  

    /*
     * 通知页面，正在审核中
     */
    public function verifying(){
        if($this->joinin_state == STORE_JOIN_STATE_VERIFY_FAIL){
            $this->redirect('StartBusiness/verifyFail');
        }
        $this->display();
    }
    
    /*
     * 审核失败页面
     */
    public function verifyFail(){
          $model_store_joinin = D('StoreJoinin');
          $joinin_info = $model_store_joinin->getOne(array('member_id' => $_SESSION['member_id']));
          $this->assign('joinin_info',$joinin_info);
          $this->display();
    }
   
  /*
   * 判断审核状态，跳转页面
   */
  private function check_joinin_state() {
        $model_store_joinin = D('StoreJoinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id'=>$_SESSION['member_id']));
        if(!empty($joinin_detail)) {
            $this->joinin_detail = $joinin_detail;
            $this->joinin_state = $joinin_detail['joinin_state'];
        }else{
            $this->joinin_state = 0;
        }
  }
  
  /*
   * 路径分发
   */
  private function behaveMyself($state){
    if(!is_array($state)&&($this->joinin_state != $state)){
        $this->checkRoute();
    }else if(is_array($state)&&(!array_key_exists($this->joinin_state, $state))){
        $this->checkRoute();
    }
  }

    /*
     * 根据状态重定向到页面
     */
    private function checkRoute(){
      switch ($this->joinin_state) {
        //审核中
        case 0:
            $this->redirect('StartBusiness/index');
          break;
        case self::STORE_JOIN_STATE_VERIFYING:
            $this->redirect('StartBusiness/verifying');
          break;
        case self::STORE_JOIN_STATE_FINAL:
            $this->redirect('StartBusiness/step4');
          break;
        case self::STORE_JOIN_STATE_VERIFY_FAIL:
            $this->redirect('StartBusiness/verifyFail');
          break;
        default:
            $this->redirect('StartBusiness/index');
          break;
      }
    }


      /*
     *  提交数据
     */
    public function saveProfile(){
       //保存成功
        if(!empty($_POST)) {
            $param = array();
            
            $param['store_name']                         = $_POST['store_name'];
            $param['member_name']                        = $_SESSION['member_name'];   
            $param['company_name']                       = $_POST['company_name'];
            $param['province_id']                        = $_POST['company_province_id'];
            $param['city_id']                            = $_POST['company_city_id'];
            $param['area_id2']                           = $_POST['company_area_id'];
            $param['company_address']                    = $_POST['company_address'];
            $param['company_address_detail']             = $_POST['company_address_detail'];            
            $param['company_phone']                      = $_POST['company_phone'];
            $param['business_licence_number']            = $_POST['business_licence_number'];
            $param['organization_code']                  = $_POST['organization_code'];
            $param['business_licence_number_electronic'] = basename($_POST['business_licence_number_electronic']);
            $param['organization_code_electronic']       = basename($_POST['organization_code_electronic']);
            $param['com_type']                           = intval($_POST['com_type']);
            $param['business_type']                      = intval($_POST['business_type']);
            //获取法人信息
            //法人姓名
            $param['representive']                       = $_POST['representive'];
            //法人身份证号
            $param['representive_id']                    = $_POST['representive_id'];
            //法人证件有效期start
            $param['representive_id_start']              = $_POST['representive_id_start'];
            //法人证件有效期start
            $param['representive_id_end']                = $_POST['representive_id_end'];
            //法人证件正面照
            $param['representive_id_front_electronic']   =basename($_POST['representive_id_front_electronic']);
            //法人证反面面照
            $param['representive_id_back_electronic']    =basename($_POST['representive_id_back_electronic']);
            $param['seller_name']                        = $_SESSION['member_name'];
            $param['company_employee_count']             =0;
            $param['company_registered_capital']         =0;
            $param['tesu_created']                       = time();
            $store_class_ids                             = array();
            $store_class_names                           = array();
            $param['store_class_ids']                    = serialize($store_class_ids);
            $param['store_class_names']                  = serialize($store_class_names);
            $param['sg_id']                              = 3;
            $param['member_id']                          = $_SESSION['member_id'];
            
            $param['joinin_state']                       =  self::STORE_JOIN_STATE_VERIFYING;

            $this->step2SaveValid($param);
        }
    }
    
    private function step2SaveValid($param) {
        $is_exist = D('StoreJoinin')->getOne(array('member_id' => $_SESSION['member_id'], 'joinin_state' => 12));
        if(!is_null($is_exist)){
             redirect(U('/shop/StartBusiness/verifying'));
             //$this->jsonFail('资料正在审核中，请不要频繁提交');
        }

        $rules = array(
             array('store_name','require','店铺名称不能为空且必须小于50个字！'),
             array('store_name','1,50','店铺名称不能为空且必须小于50个字！',0,'length'),
             array('company_name','require','公司名称不能为空且必须小于50个字！'),
             array('company_name','1,50','公司名称不能为空且必须小于50个字！',0,'length'),
             array('company_address','require','公司地址不能为空且必须小于50个字！'),
             array('company_address','1,50','公司地址不能为空且必须小于50个字！',0,'length'),
             array('company_address_detail','require','公司详细地址不能为空且必须小于50个字！'),
             array('company_address_detail','1,50','公司详细地址不能为空且必须小于50个字！',0,'length'),
             array('company_phone','require','公司电话不能为空！'),
             array('company_phone','1,20','公司电话不能为空且不能超过20个数字！',0,'length'),
             array('business_licence_number','require','营业执照号不能为空且必须小于20个字！'),
             array('business_licence_number','1,20','营业执照号不能为空且必须小于20个字！',0,'length'),
             array('business_licence_number_electronic','require','营业执照电子版不能为空！'),
             array('organization_code','require','组织机构代码不能为空且必须小于20个字！'),
             array('organization_code','1,20','组织机构代码不能为空且必须小于20个字！',0,'length'),
             array('organization_code_electronic','require','组织机构代码电子版不能为空！',2),
        );
        $model_store_joinin = D('StoreJoinin');
        
        $store_name_isexist = $model_store_joinin->getOne(array('store_name'=>$param['store_name']));
        if(!empty($store_name_isexist) && $store_name_isexist['member_id'] != $_SESSION['member_id']){
            $this->jsonFail('店铺名称,已存在！');
        }
        $store_representive_id_isexist = $model_store_joinin->getOne(array('representive_id'=>$param['representive_id']));
        if(!empty($store_representive_id_isexist) && $store_representive_id_isexist['member_id'] != $_SESSION['member_id']){
            $this->jsonFail('身份证号码已被使用，请核实是否输入正确！');
        }
        $store_company_name_isexist = $model_store_joinin->getOne(array('company_name'=>$param['company_name']));
        if(!empty($store_company_name_isexist) && $store_company_name_isexist['member_id'] != $_SESSION['member_id']){
            $this->jsonFail('公司名称已被使用，请核实是否输入正确！');
        }  
        
        if($model_store_joinin->validate($rules)->create($param)){
            $joinin_info = $model_store_joinin->getOne(array('member_id' => $_SESSION['member_id']));
            
            if(empty($joinin_info)) {
                $model_store_joinin->member_id = $_SESSION['member_id'];
                $return_save = $model_store_joinin->save($param);
                if($return_save){
                    $response['code'] = 1;
                    $response['resultText']['message'] = "保存成功";
                    $response['url'] = U('StartBusiness/verifying');
                    exit(json_encode($response));
                    //$this->jsonSucc(U('StartBusiness/verifying'));
                }else{
                    $this->jsonFail("保存失败!");
                }
            } else {
                $model_store_joinin->modify($param, array('member_id'=>$_SESSION['member_id']));

                $response['code'] = 1;
                $response['resultText']['message'] = "保存成功";
                $response['url'] = U('StartBusiness/verifying');
                exit(json_encode($response));
            }
        }else{
            //返回验证失败的信息
            $this->jsonFail($model_store_joinin->getError());
        }
    }
}