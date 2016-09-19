<?php
namespace Admin\Controller;
use Admin\Controller\AdminController;
class IndexController extends AdminController {
	
	//后台登录页
    public function login(){  
		//登录动作
		if(IS_POST && $_POST){
			$username = trim(I('post.username'));
			$password = trim(I('post.password'));
			if($username=='admin' && $password=='lsx8899'){
				$_SESSION['admin']['username']   = 'admin';
				$_SESSION['admin']['login_time'] = time();
				$_SESSION['admin']['expired']    = 7200;
                redirect(U('Admin/Index/index'));
			}else{
				$this->error('登录失败，帐号或密码错误。');
			}
		}  
		$this->display(); 
    }
	
    public function index(){
        $this->display();  
    }
}