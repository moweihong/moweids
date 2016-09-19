<?php
namespace Shop\Model;
use Think\Model;

class MemberModel extends Model 
{
	
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
     * 注册
     */
    public function register($register_info) {
      
		// 注册验证
        /*
		$obj_validate = new Validate();
		$obj_validate->validateparam = array(
		array("input"=>$register_info["username"],		"require"=>"true",		"message"=>'用户名不能为空'),
		array("input"=>$register_info["password"],		"require"=>"true",		"message"=>'密码不能为空'),
		array("input"=>$register_info["password_confirm"],"require"=>"true",	"validator"=>"Compare","operator"=>"==","to"=>$register_info["password"],"message"=>'密码与确认密码不相同'),
		array("input"=>$register_info["email"],			"require"=>"true",		"validator"=>"email", "message"=>'电子邮件格式不正确'),
		);
		$error = $obj_validate->validate();
		if ($error != ''){
            return array('error' => $error);
		}
         * </howard>
         */

        // 验证用户名是否重复
		//$check_member_name	= $this->infoMember(array('member_name'=>trim($register_info['username'])));
    
        //用户名验证
        $check_member_name = $this->where(array('member_name'=>trim($register_info['username'])))->select();
		if(is_array($check_member_name) and count($check_member_name) > 0) {
            return array('error' => '用户名'.$register_info['username'].'已存在');
		}

        //手机验证
        $check_mobile = $this->where(array('member_name'=>trim($register_info['mobile'])))->select();
        if(is_array($check_member_name) and count($check_member_name) > 0) {
            return array('error' => '手机'.$register_info['mobile'].'已注册');
        }

        //mid验证
        $check_mobile = $this->where(array('member_name'=>trim($register_info['mid'])))->select();
        if(is_array($check_member_name) and count($check_member_name) > 0) {
            return array('error' => 'java编号 '.$register_info['mid'].'已存在');
        }
        

        // 验证邮箱是否重复
//		$check_member_email	= $this->infoMember(array('member_email'=>trim($register_info['email'])));
//		if(is_array($check_member_email) and count($check_member_email)>0) {
//            return array('error' => '邮箱已存在');
//		}

		// 会员添加
		$member_info	= array();
		$member_info['member_name']   = $register_info['username'];
		$member_info['member_passwd'] = $register_info['password'];
        $member_info['member_paypwd'] = $register_info['password'];
        
		$member_info['member_email']  = $register_info['email'];
		$member_info['mobile']        = $register_info['mobile'];//<howard>
		$member_info['mid'] 		  = $register_info['mid'];
        $member_info['member_avatar'] = '/data/upload/shop/common/'.C('member_logo');
		$insert_id	= $this->addMember($member_info);
		if($insert_id) {
			//添加会员积分
			if ($GLOBALS['setting_config']['points_isuse'] == 1){
				$points_model = Model('points');
				$points_model->savePointsLog('regist',array('pl_memberid'=>$insert_id,'pl_membername'=>$register_info['username']),false);
			}

            // 添加默认相册
            // $insert['ac_name']      = '买家秀';
            // $insert['member_id']    = $insert_id;
            // $insert['ac_des']       = '买家秀默认相册';
            // $insert['ac_sort']      = 1;
            // $insert['is_default']   = 1;
            // $insert['upload_time']  = TIMESTAMP;
            // \Think\Model::table('sns_albumclass')->add($insert);

            $member_info['member_id'] = $insert_id;
            $member_info['is_buy'] = 1;

            return $member_info;
		} else {
            return array('error' => '注册失败');
		}

    }
/**
	 * 注册商城会员
	 *
	 * @param	array $param 会员信息
	 * @return	array 数组格式的返回结果
	 */
	public function addMember($param) {
		if(empty($param)) {
			return false;
		}
		$member_info	= array();
		$member_info['member_id']             = $param['member_id'];
		$member_info['member_name']           = $param['member_name'];
		$member_info['member_passwd']         = md5(trim($param['member_passwd']));
        $member_info['member_paypwd']         = md5(trim($param['member_passwd']));
		$member_info['member_email']          = $param['member_email'];
		$member_info['member_time']           = time();
		$member_info['member_login_time']     = $member_info['member_time'];
		$member_info['member_old_login_time'] = $member_info['member_time'];
		$member_info['member_login_ip']       = getIp();
		$member_info['member_old_login_ip']   = $member_info['member_login_ip'];
		
		$member_info['member_truename']       = $param['member_truename'];
		$member_info['member_qq']             = $param['member_qq'];
		$member_info['member_sex']            = $param['member_sex'];
		$member_info['member_avatar']         = $param['member_avatar'];
		$member_info['member_qqopenid']       = $param['member_qqopenid'];
		$member_info['member_qqinfo']         = $param['member_qqinfo'];
		$member_info['member_sinaopenid']     = $param['member_sinaopenid'];
		$member_info['member_sinainfo']       = $param['member_sinainfo'];
		$member_info['mid']                   = $param['mid'];
		$member_info['mobile']	= $param['mobile'];
		//log::record(" member info is ".json_encode($member_info));
		$result	=$this->add($member_info);
		if($result) {
			return $result;
		} else {
			return false;
		}
	}
	
	/**
     * 登录时创建会话SESSION
     *
     * @param array $member_info 会员信息
     */
    public function createSession($member_info = array()) {
        if (empty($member_info) || !is_array($member_info)) return ;

		$_SESSION['is_login']	= '1';
		$_SESSION['member_id']	= $member_info['member_id'];
		$_SESSION['member_name']= $member_info['mobile'];
		$_SESSION['member_email']= $member_info['member_email'];
		$_SESSION['is_buy']		= $member_info['is_buy'];
		$_SESSION['avatar'] 	= $member_info['member_avatar'];
        $_SESSION['mid']        = $member_info['mid'];
        $_SESSION['mobile']     = $member_info['mobile'];
        //登录商家
        $this->sellerLogin();
	 	//写入分期购额度
	 	$options['usrid'] = $member_info['mid'];
        $api = API('user');
        $register_result = json_decode($api->getLimit($options),true);
       	if(empty($register_result) || !isset($register_result)){
                $_SESSION['easypaycredit_total'] = -1;
                $_SESSION['easypaycredita_available'] = -1;
        }else if($register_result['code'] == 0){
                $result = json_decode($register_result['resultText'], true);
                $_SESSION['easypaycredit_total'] = $result['loan_limit'];
                $_SESSION['easypaycredita_available'] = $result['loan_useble'];
                $_SESSION['is_firstquery'] = $result['is_firstquery'];
        }else{
                $_SESSION['easypaycredit_total'] = -1;
                $_SESSION['easypaycredita_available'] = -1;
        }

        _setcookie("uid",$member_info['member_id'],60*60*24*7);			
        _setcookie("ushell",md5($member_info['member_id'].$member_info['member_passwd'].$member_info['mobile'].$member_info['member_email']),60*60*24*7);
		$seller_info = D('seller')->getSellerInfo(array('member_id'=>$_SESSION['member_id']));
		$_SESSION['store_id'] = $seller_info['store_id'];
        $store_info = D('store')->getStoreInfoByID($seller_info['store_id']);
        $_SESSION['com_type']      = $store_info['com_type'];
        $_SESSION['business_type'] = $store_info['business_type'];
		if (trim($member_info['member_qqopenid'])){
			$_SESSION['openid']		= $member_info['member_qqopenid'];
		}
		if (trim($member_info['member_sinaopenid'])){
			$_SESSION['slast_key']['uid'] = $member_info['member_sinaopenid'];
		}
		if(!empty($member_info['member_login_time'])) {//登录时间更新
    		$update_info	= array(
    		'member_login_num'=> ($member_info['member_login_num']+1),
    		'member_login_time'=> time(),
    		'member_old_login_time'=> $member_info['member_login_time'],
    		'member_login_ip'=> getIp(),
    		'member_old_login_ip'=> $member_info['member_login_ip']);
			$this->where('member_id='.$member_info['member_id'])->data($update_info)->save();
		}
    }
		
 /**
     * 创建商家登录session
     * @return [type] [description]
     */
    private function sellerLogin(){
        //如果用户没有登录，返回
        if(!isset($_SESSION['member_id']) || empty($_SESSION['member_id']))
            return;

        //如果客户没有开店，返回
        $model_seller = D('seller');
        $seller_info = $model_seller->getSellerInfo(array('member_id' => $_SESSION['member_id']));
        if(!$seller_info)
            return;

        // 更新卖家登陆时间
        $model_seller->editSeller(array('last_login_time' => TIMESTAMP), array('seller_id' => $seller_info['seller_id']));

       

        $model_store = D('store');
        $store_info = $model_store->getStoreInfoByID($seller_info['store_id']);

        $_SESSION['is_login'] = '1';

        $_SESSION['grade_id'] = $store_info['grade_id'];
        $_SESSION['seller_id'] = $seller_info['seller_id'];
        $_SESSION['seller_name'] = $seller_info['seller_name'];
        $_SESSION['store_id'] = intval($seller_info['store_id']);
        $_SESSION['store_name'] = $store_info['store_name'];
        if(!$seller_info['last_login_time']) {
            $seller_info['last_login_time'] = TIMESTAMP;
        }
        $_SESSION['seller_last_login_time'] = date('Y-m-d H:i', $seller_info['last_login_time']);
        if(!empty($seller_info['seller_quicklink'])) {
            $quicklink_array = explode(',', $seller_info['seller_quicklink']);
            foreach ($quicklink_array as $value) {
                $_SESSION['seller_quicklink'][$value] = $value ;
            }
        }

  
     
        return true;
    }

	/**
	 * 会员详细信息
	 * @param array $condition
	 * @param string $field
	 * @return array
	 */
	public function getMemberInfo($condition, $field = '*') {
		return $this->field($field)->where($condition)->find();
	}
	
	/**
	 * 更新会员信息
	 *
	 * @param	array $param 更改信息
	 * @param	int $member_id 会员条件 id
	 * @return	array 数组格式的返回结果
	 */
	public function updateMember($param,$member_id) {
		if(empty($param)) {
			return false;
		}
		$update		= false;
		//得到条件语句
		$condition_str	= " member_id=".$member_id;
		$update		= M('member')->where($condition_str)->save($param);
		return $update;
	}

	/**
	 * 获取会员信息
	 *
	 * @param	array $param 会员条件
	 * @param	string $field 显示字段
	 * @return	array 数组格式的返回结果
	 */
	public function infoMember($param, $field='*') {
		if (empty($param)) return false;

		//得到条件语句
		$condition_str	= $this->getCondition($param);
		if (strtoupper(substr(trim($condition_str),0,3)) == 'AND'){
			$condition_str = substr(trim($condition_str),3);
		}
		$where	= $condition_str;
		$field	= $field;
		$member_list	= $this->where($where)->field($field)->limit(1)->select();
		$member_info	= $member_list[0];
		if (intval($member_info['store_id']) > 0){
	      $field	= 'store_id';
	      $where[$field]	= $member_info['store_id'];
	      $field	= 'store_id,store_name,grade_id';
	      $store_info	= $this->where($where)->field($field)->find();
	      if (!empty($store_info) && is_array($store_info)){
		      $member_info['store_name']	= $store_info['store_name'];
		      $member_info['grade_id']	= $store_info['grade_id'];
	      }
		}
		return $member_info;
	}

	/**
	 * 将条件数组组合为SQL语句的条件部分
	 *
	 * @param	array $conditon_array
	 * @return	string
	 */
	private function getCondition($conditon_array){
		$condition_sql = '';
		if($conditon_array['member_id'] != '') {
			$condition_sql	.= " and member_id= '" .intval($conditon_array['member_id']). "'";
		}
		if($conditon_array['mobile'] != '') {
			$condition_sql	.= " and mobile= '" .$conditon_array['mobile']. "'";
		}
		if($conditon_array['member_name'] != '') {
			//$condition_sql	.= " and member_name='".$conditon_array['member_name']."'";
                    $condition_sql.= " and (member_name='".$conditon_array['member_name']."'"."or mobile='".$conditon_array['member_name']."')";
		}
		if($conditon_array['member_passwd'] != '') {
			$condition_sql	.= " and member_passwd='".$conditon_array['member_passwd']."'";
		}
		//是否允许举报
		if($conditon_array['inform_allow'] != '') {
			$condition_sql	.= " and inform_allow='{$conditon_array['inform_allow']}'";
		}
		//是否允许购买
		if($conditon_array['is_buy'] != '') {
			$condition_sql	.= " and is_buy='{$conditon_array['is_buy']}'";
		}
		//是否允许发言
		if($conditon_array['is_allowtalk'] != '') {
			$condition_sql	.= " and is_allowtalk='{$conditon_array['is_allowtalk']}'";
		}
		//是否允许登录
		if($conditon_array['member_state'] != '') {
			$condition_sql	.= " and member_state='{$conditon_array['member_state']}'";
		}
		if($conditon_array['friend_list'] != '') {
			$condition_sql	.= " and member_name IN (".$conditon_array['friend_list'].")";
		}
		if($conditon_array['member_email'] != '') {
			$condition_sql	.= " and member_email='".$conditon_array['member_email']."'";
		}
		if($conditon_array['no_member_id'] != '') {
			$condition_sql	.= " and member_id != '".$conditon_array['no_member_id']."'";
		}
		if($conditon_array['like_member_name'] != '') {
			$condition_sql	.= " and member_name like '%".$conditon_array['like_member_name']."%'";
		}
		if($conditon_array['like_member_email'] != '') {
			$condition_sql	.= " and member_email like '%".$conditon_array['like_member_email']."%'";
		}
		if($conditon_array['like_member_truename'] != '') {
			$condition_sql	.= " and member_truename like '%".$conditon_array['like_member_truename']."%'";
		}
		if($conditon_array['in_member_id'] != '') {
			$condition_sql	.= " and member_id IN (".$conditon_array['in_member_id'].")";
		}
		if($conditon_array['in_member_name'] != '') {
			$condition_sql	.= " and member_name IN (".$conditon_array['in_member_name'].")";
		}
		if($conditon_array['member_qqopenid'] != '') {
			$condition_sql	.= " and member_qqopenid = '{$conditon_array['member_qqopenid']}'";
		}
		if($conditon_array['member_sinaopenid'] != '') {
			$condition_sql	.= " and member_sinaopenid = '{$conditon_array['member_sinaopenid']}'";
		}
		
		return $condition_sql;
	}
}
?>