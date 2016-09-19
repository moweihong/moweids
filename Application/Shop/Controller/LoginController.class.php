<?php
/*
 * 前端登录
 */
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;
class LoginController extends ShopCommonController {
    /*
     * 登录界面
     */
    public function index(){
		$model_member = D('member');
		$api = API("user");
		if($_POST['form_submit']=='ok'){
			$login_type=I('post.login_type');
			switch ($login_type) {
                case 'password':
                    $get_userinfo = array();
                    $get_userinfo['user_phone'] = trim($_POST['user_name'],'+');
                    $get_userinfo['user_pass'] = md5($_POST['password']);
                    //统一登录，java远程登录作为登录成功与否的唯一凭证
                    $this->loginByJava($get_userinfo, $model_member, $api);
                    break;
                case 'verify_code':
                    $login_code = $_SESSION['login_code'];
                    $phone = trim($_POST['user_name'],'+');
                    $verify_code = $_POST['verify_code'];
					$error['code'] = 0;					                                               
					$vcode=$_SESSION['ts_'.$phone]['verify_code'];
                    if($vcode!=$verify_code){//print_r($vcode);exit;
						$error['resultText']['message'] = "登录失败,验证码错误!";
                        $this->ajaxReturn($error);
                    }
                    if ($vcode==$verify_code) {
						//用户成功获取验证码
						if(time()>$_SESSION['ts_'.$phone]['verify_time']+SMS_EXPIRED_TIME){
							$error['resultText']['message'] ="登录失败,验证码超时,请重新获取验证码!";
							$this->ajaxReturn($error);
						};  
                        //校验通过,手机号和密码都通过验证,访问java获取用户的信息,模拟用户常规登陆
                        //登录成功
                        //第一步,通过用户账号拉取java平台uid
                        $res = json_decode($api->getUserId(array('user_name' => $phone)), true);
                        if ($res['code'] <= 0) {
							$error['resultText']['message'] = "登录失败,用户不存在!";
                            $this->ajaxReturn($error);
                        }
                        $uid = $res['code'];
                        //第二步,通过uid获取用户的详细信息,模拟用户登录
                        $userinfo['usr_id'] = $uid;
                        $get_userinfo = json_decode($api->getUserInfo($userinfo), true);
                        if ($get_userinfo['code'] < 0) {
							$error['resultText']['message'] = "登录失败,获取用户信息失败,请重试!";
                            $this->ajaxReturn($error);
                        }
                        $get_userinfo = json_decode($get_userinfo['resultText'], true);
                        $this->loginByJava($get_userinfo, $model_member, $api);
                    } else {
                        //登录失败
						$error['resultText']['message'] = "登录失败!";
                        $this->ajaxReturn($error);
                    }
                    break;
            }			
		}
        $this->display();
    }

	/**公共登录函数抽取**/
    private function loginByJava($get_userinfo, $model_member, $api)
    {

        //构造登录数组
        $array = array();
        $array['member_name'] = $get_userinfo['user_phone'];
        $array['member_passwd'] = $get_userinfo['user_pass'];
        //统一登录，java远程登录作为登录成功与否的唯一凭证
        $local_login = $model_member->where($array)->find();
        unset($login_options);
        $login_options['user_name']      = $get_userinfo['user_phone'];
        $login_options['user_pass']      = $get_userinfo['user_pass'];
        $login_options["recommend_id"]   =$_SESSION["salesman_usrid"];
        $login_options["recommend_2_id"] =$_SESSION["salesman_2_usrid"];
        $login_options["platform_source"]=2;
        $remote_login = json_decode($api->login($login_options), true);
        $user_id = $remote_login['code'];
        //服务器异常
        //判断网路
		$error['code']=0;
        if (!isset($remote_login)) {
			$error['resultText']['message'] = "登录失败，无法连接服务器!";
			$this->ajaxReturn($error);
        }
        //全木行屏蔽的账户
        if (is_array($local_login) and !empty($local_login)) {
            //账户不能使用
            if (!$local_login['member_state']) {
                $error['resultText']['message'] = "账户不能使用!";
				$this->ajaxReturn($error);
            }
        }
        //登录失败
        if ($user_id == "-1") {
            //登录失败
            //processClass::addprocess('login');
            $_SESSION['sub_time']=$_SESSION['sub_time']?$_SESSION['sub_time']+1:1;
            $_SESSION['expiretime'] = time();  
//            if($_SESSION['sub_time']>5){
//                $this->_failReturn('您的操作过于频繁，请稍候再试！');
//            }
			$error['resultText']['message'] = $remote_login['resultText'];    
			$this->ajaxReturn($error);
        }
		
        //登录成功，比对数据，更新用户数据
        $update_result = $this->checkAndUpdateUserInfo($user_id);
        unset($_SESSION['sub_time']);
        //获取用户信息
        unset($array);
        $array['mid'] = $user_id;
        $array['mobile'] = $get_userinfo['user_phone'];
        $local_login = $model_member->where($array)->find();
        $member_info = $local_login;
        local:
        //设置session
        $model_member->createSession($member_info);
        //写入分期购信息
        updateEasypayStatus();
		$suc['code']=1;
		$error['resultText']['message'] = "登录成功";
        $this->ajaxReturn($suc);
    }
	/**
     * 比对java用户信息和全木行本地用户信息，如果出现不一致
     * 用java端返回更新全木行数据库
     * @return [type] [description]
     */
    private function checkAndUpdateUserInfo($userid)
    {

        if (!isset($userid) || empty($userid))
            return false;

        //获取用户信息
        $api = API("user");
        $options['usr_id'] = $userid;
        $info = json_decode($api->getUserInfo($options), true);//print_r($info);exit;

        if ($info['code'] == -1){
            //log::i(PHP_EOL."member_info_sync_failed reason: JAVA FAIL ".PHP_EOL."date:".date('Y-m-d H:i:s', time()), 'sync');
            return false;
        }

        $info = json_decode($info['resultText'], true);

        $model_member = D('member');
        $array = array();
        //$array['member_name'] = $info['user_name'];
        $array['member_passwd'] = $info['user_pass'];
        $array['mobile'] = $info['user_phone'];
        $array['mid'] = $userid;
        $array['member_email'] = $info['user_email'];
        $local_member_result = $model_member->where($array)->find();

        $arr2 = array();
        $arr2['mobile'] = $info['user_phone'];
        $local_member_exist = $model_member->where($arr2)->find();
		//print_r($info);exit;
        //如果用户不存在，注册用户
        if (!$local_member_exist) {//print_r($info);exit;

            $register_options['username'] = $info['user_phone'];
            $register_options['password'] = $info['user_pass'];
            $register_options['email'] = 'tesu@tesu.com';
            $register_options['mobile'] = $info['user_phone'];
            $register_options['mid'] = $userid;
            $register_result = $model_member->register($register_options);
            if (!is_null($register_result['error'])){
                return false;
            }
        } else {

            if (!$local_member_result) {

                //信息不匹配，更新用户信息
                //$update['member_name']   = $info['user_name'];
                $update['member_passwd'] = $info['user_pass'];
                $update['mobile'] = $info['user_phone'];
                $update['mid'] = $userid;
                //$update['member_email'] = $info['user_email'];
                $update = $model_member->where(array('member_id' => $local_member_exist['member_id']))->data($update)->save();
                if (!$update){
                    //log::i(PHP_EOL."member_info_sync_failed reason: member_update fail  ".json_encode($update).PHP_EOL."date:".date('Y-m-d H:i:s', time()), 'sync');
                    return false;
                }
            }
        }
        return true;
    }
	
	/**
     * 检查手机号码是否在平台注册
     *
     * @param
     * @return
     */
    public function check_and_exist()
    {
        /**
         * 实例化模型
         */
        $api = API('user');
        //1.判断手机号码是否存在
        $userinfo['user_name'] = $_GET['mobile'];
        $is_register_username = json_decode($api->isRegister($userinfo), true);
        if ($is_register_username['code'] == -1) {
            //失败
			$data['code'] = 0;
			$data['resultText']['message'] = $is_register_username['resultText'];
           
        } else if($is_register_username['code'] >-1) {
            //成功
			$data['code'] = 1;
			$data['resultText']['message'] = '验证通过';
        } else {
            //异常
            $data['code'] = $is_register_username['code'];
            $data['resultText']['message'] = $is_register_username['resultText'];
        }
		 $this->ajaxReturn($data);
    }
	/**
     *获取短信验证码
     */
    public function verify_code()
    {

        //查询手机号码是否已经发送过
        //检查login中的参数和重发时间
        $login_code = $_SESSION['login_code'];
        $rec_mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
		$error['code']=0;
        if (preg_match('/^1\d{10}$/', $rec_mobile)) {
            //增加java层面的用户存在性校验,防止登录被刷短信
            $api = API("user");
            //获取用户uid			
            $check_uid = json_decode($api->getUserId(array('user_name' => $rec_mobile)), true);//print_r($check_uid);exit;
            if ($check_uid['code'] > 0) {//登录验证码，找回密码验证码,该手机号码属于注册用户   
				if(time()<($_SESSION['ts_'.$rec_mobile]['verify_time']+SMS_SEND_TIME)){
					$error['resultText']['message'] = '您发送的太频繁，请稍候再试!';
                    $this->ajaxReturn($error);
				}
                //print_r($_POST);exit;				
				if($_POST['tag']=='resetpassword'){
					$result=sendResetPwd($rec_mobile);
				}else{
					$result=sendLogin($rec_mobile);
				}				
                //print_r($result);exit;
                if ($result==1) {//die("aaa");
                    //短信发送成功!
					$suc['code']=1;
					$suc['resultText']['message'] = '短信发送成功!';
                    $this->ajaxReturn($suc);
                } else {
                    //unset($_SESSION[$rec_mobile]['verify_code']);
					$error['resultText']['message'] = '短信发送失败!';
                    $this->ajaxReturn($error);
                }
            } else {
				if($_POST['tag']=='register'){//注册验证码
						if(time()<($_SESSION['ts_'.$rec_mobile]['verify_time']+SMS_SEND_TIME)){
							$error['resultText']['message'] = '您发送的太频繁，请稍候再试!';
							$this->ajaxReturn($error);
						}
						$result=sendRegis($rec_mobile);								
						if ($result==1) {//die("aaa");
							//短信发送成功!
							$suc['code']=1;
							$suc['resultText']['message'] = '短信发送成功!';
							$this->ajaxReturn($suc);
						} else {
							//unset($_SESSION[$rec_mobile]['verify_code']);
							$error['resultText']['message'] = '短信发送失败!';
							$this->ajaxReturn($error);
						}		
				}	
				$error['resultText']['message'] = '短信发送失败!';
                $this->ajaxReturn($error);
            }
        } else {			
			$error['resultText']['message'] = '手机号码错误!';
            $this->ajaxReturn($error);
        }
    }
	
	function check_usersave(){
		$mobile=$_POST['mobile'];//print_r($_POST);exit;
		\Think\Log::lol('post = '.$_POST['mobile'].' SESSION = '.$_SESSION[$mobile]['verify_code']);
		if($_POST['code']!=$_SESSION['ts_'.$mobile]['verify_code']){
			$data['code']=0;
			$data['message'] = '验证码不正确!';		    
		}else{
			$data['code']=1;
		}//print_r($data);exit;
		$this->ajaxReturn($data);
		
	}
 /**
     * 忘记密码页面
     */
    public function forget_password()
    {
        if ($_POST) {
            $mobile = $_POST['mobile'];
            $api = API('user');
            $userinfo['user_name'] = $mobile;
            $find_user_id = json_decode($api->getUserId($userinfo), true);
            $user_id = $find_user_id['code'] > 0 ? $find_user_id['code'] : 0;

            if (!$user_id) {
                echo json_encode(array('message' => '无法获取手机信息', 'code' => '0'));
                exit;
            }
            $member_info = array();
            $userinfo['usr_id'] = $user_id;
            $java_member_info = json_decode($api->getUserInfo($userinfo), true);
            if ($java_member_info['code'] == 0) {
                $member_info = json_decode($java_member_info['resultText'], true);
            }

            if (empty($member_info)) {
                echo json_encode(array('message' => '找不到该手机号码记录', 'code' => '0'));
                exit;
            }

            $userinfo['usr_id'] = $user_id;
            $userinfo['user_name'] = $member_info['user_name'];
            $userinfo['user_pass'] = '';//老密码
            $userinfo['user_pass_new'] = md5($_POST['password']);//新密码
            $userinfo['is_checkoldpass'] = 0;
            $change_pwd = json_decode($api->changePassword($userinfo), true);//print_r($change_pwd);exit;

            if ($change_pwd['code'] == 0) {
                D('member')->where(array("mobile" => $mobile))->data(array("member_passwd" => md5(trim($_POST['password']))))->save();
				$data['code']=1;
				$data['message']='密码重置成功';
				
            } else {
                $data['code']=0;
				$data['message']='密码重置失败';
            }
            $this->ajaxReturn($data);
        } else {
           
            //Tpl::showpage('find_password');
        }


    }	

	/**
     * 退出操作
     *
     * @param int $id 记录ID
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function logout()
    {
        session_unset();
        session_destroy();
        //<howard>一元购退出
        _setcookie("allwood_shop", "", time() - 3600);
        _setcookie("uid", "", time() - 3600);
        _setcookie("ushell", "", time() - 3600);
        if (empty($_GET['ref_url'])) {
            $ref_url = $_SERVER['HTTP_REFERER'];
        } else {
            $ref_url = $_GET['ref_url'];
        }
        header('location:'.$ref_url);
		//showMessage($lang['login_logout_success'], 'index.php?act=login&ref_url=' . urlencode($ref_url), 'html', 'succ', 1, 2000);
    }

    /*
     * 注册页面
     */
    public function register(){
        $this->display();
    }
	
	/**
     * 会员添加操作
     *
     * 统一注册实行之后，所用有效用户都必须在java端存在数据，
     * 所有不在java端的数据都是非法数据
     * java创建不成功，用户创建失败。
     *
     * @param
     * @return
     */
    public function new_usersave()
    {
        if($_POST['code']!=$_SESSION['ts_'.$_POST['mobile']]['verify_code']){//检验验证码是否正确
            $error['message']='验证码错误';
            $this->ajaxReturn($error);
        }
		//用户成功获取验证码
		if(time()>$_SESSION['ts_'.$_POST['mobile']]['verify_time']+SMS_EXPIRED_TIME){
			$error['message'] ="登录失败,验证码超时,请重新获取验证码!";
			$this->ajaxReturn($error);
		};		
        $model_member = D('member');
        $register_info = array();
        $register_info['username'] = $_POST['mobile'];//$_POST['user_name']
        $register_info['password'] = $_POST['member_passwd'];//$_POST['password']
        $register_info['member_paypwd'] = $_POST['member_passwd'];//$_POST['password']
        $register_info['password_confirm'] = '';//$_POST['password_confirm']
        $register_info['email'] = 'tesu@tesu.com';
        $register_info['mobile'] = $_POST['mobile'];//<howard>
		
        //调用JAVA API接口 注册
        $register_options['user_name'] = $register_info['username'];
        $register_options['user_pass'] = md5(trim($register_info['password']));
        //md5(trim($register_info['password']));
        //$register_options['user_email'] = $register_info['email'];
        $register_options['user_phone'] = $register_info['mobile'];

        $api = API('user');
        $register_result = json_decode($api->register($register_options), true);
        //判断网络
        if (!isset($register_result)) {
			$error['message']="注册失败，无法连接服务器!";
			$this->ajaxReturn($error);
        }

        if ($register_result['code'] == "-1") {
			$error['message']=$register_result['resultText'];
            $this->ajaxReturn($error);
        }
        
        $register_info['mid'] = $register_result['resultText'];
        $member_info = $model_member->register($register_info);
        if (!isset($member_info['error'])) {
            _setcookie("uid", $member_info['member_id'], 60 * 60 * 24 * 7);
            _setcookie("ushell", md5($member_info['member_id'] . $member_info['member_passwd'] . $member_info['mobile'] . $member_info['member_email']), 60 * 60 * 24 * 7);
            $model_member->createSession($member_info);

            $_POST['ref_url'] = (strstr($_POST['ref_url'], 'logout') === false && !empty($_POST['ref_url']) ? $_POST['ref_url'] : 'index.php?c=shop&a=index');
            //$this->Success('注册成功');
            $tmp['code'] = 0 ;
            $tmp['resultText']['message'] = "success";
            $tmp['message'] = "success";
            $tmp['resultText']['url'] = U('shop/login/index');
            exit(json_encode($tmp));
        } else {
            $tmp['code'] = 1 ;
            $tmp['resultText']['message'] = $member_info['error'];
            $tmp['message'] = $member_info['error'];
            $tmp['resultText']['url'] = U('shop/login/index');
            exit(json_encode($tmp));
            // $this->error($member_info['error']);

            // $this->error($member_info['error']);
        }
    }
 /**
     * 会员名称检测  
     *
     * @param
     * @return
     */
    public function check_member()
    {
        /**
         * 实例化模型
         */
        //$model_member = Model('member');
        //$check_member_name = $model_member->infoMember(array('mobile' => trim($_GET['mobile'])));
        $is_ajax_request = !is_null($_GET['ajaxrequest'])?$_GET['ajaxrequest']:$_POST['ajaxrequest'];
        $api = API('user');
        $userinfo['user_name'] = $_GET['mobile'];
        $is_register = json_decode($api->isRegister($userinfo), true);//print_r($is_register);exit;
        //兼容ajax请求
        if($is_ajax_request){
            if($is_register['code'] > -1){
                //成功
				$result['code'] = 1;	
	            $result['resultText']['message'] = "该手机号已经注册,请更换手机号！";
                $this->ajaxReturn($result);
            }else if ($is_register['code'] == -1){
                //失败
                exit(json_encode(array('code'=>0,'resultText'=>array('message'=>$is_register['resultText']))));
            }else{
                //异常
//				$result['code'] = $is_register['code'];	
//	            $result['resultText']['message'] =$is_register['message'];
//                $this->ajaxReturn($result);
                exit(json_encode(array('code'=>$is_register['code'],'resultText'=>array('message'=>'java接口异常'))));
            }
        }


        $is_register = $is_register['code'] > 0 ? true : false;
        if (empty($_GET["keys"])) {
            if ($is_register) {
                echo 'false';
            } else {
                echo 'true';
            }
        } else {
            if ($is_register) {
                echo 'true';
            } else {
                echo 'false';
            }
        }
    }	

    /*
     * 找回密码
     */
    public function findPassword(){
        $this->display();
    }

    /*
     * 登录弹窗
     */
    public function tplLogin(){
        $this->display();
    }
}