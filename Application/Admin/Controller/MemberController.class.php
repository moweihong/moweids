<?php
namespace Admin\Controller;
use Admin\Controller\AdminController;
class MemberController extends AdminController {
	 //会员管理
    public function memberManager(){
        $page_num = I('get.p')==null?0:I('get.p');
    	
    	//检索条件
   		switch (I('get.search_field_name')) {
   			case 'member_name':
   				$condition['member_name'] =  array('like','%'.trim(I('get.search_field_value')).'%');
   				break;
   			case 'member_email':
   				$condition['member_email'] =  array('like','%'.trim(I('get.search_field_value')).'%');
   				break;
   			case 'member_truename':
   				$condition['member_truename'] =  array('like','%'.trim(I('get.search_field_value')).'%');
   				break;
   			case 'member_mobile':
   				$condition['member_mobile'] =  array('like','%'.trim(I('get.search_field_value')).'%');
   				break;
   			default:
   				break;
    	}
    	switch (I('get.search_state')) {
    		case 'no_informallow':
				$condition['inform_allow'] = '2';
				break;
			case 'no_isbuy':
				$condition['is_buy'] = '0';
				break;
			case 'no_isallowtalk':
				$condition['is_allowtalk'] = '0';
				break;
			case 'no_memberstate':
				$condition['member_state'] = '0';
				break;
			default:
				break;
    	}
    	//排序
		$order = trim(I('get.search_sort'));
		if ($order != 0) {
		    $order = 'member_id desc';
		}
    	$member = D('Member');
    	$member_list = $member->getMemberList($condition,'*',$page_num.',10',$order);
    	/**
    	 * 整理会员信息
    	 */
    	if (is_array($member_list)){
			foreach ($member_list as $k=> $v){
				$member_list[$k]['member_time'] = $v['member_time']?date('Y-m-d H:i:s',$v['member_time']):'';
				$member_list[$k]['member_login_time'] = $v['member_login_time']?date('Y-m-d H:i:s',$v['member_login_time']):'';
			}
		}
		$member_count = $member->getMemberCount($condition);
		$Page = new \Think\Page($member_count,10);
		$show = $Page->show();
		$this->assign('search_sort',trim(I('get.search_sort')));
		$this->assign('search_field_name',trim(I('get.search_field_name')));
		$this->assign('search_field_value',trim(I('get.search_field_value')));
		$this->assign('search_state',trim(I('get.search_state')));
		$this->assign('member_list',$member_list);
		$this->assign('page',$show);
    	$this->display();
    }

    /**
	 * 会员修改
	 */
	public function memberEdit(){
		$model_member = D('Shop/Member');
		/**
		 * 保存
		 */
		if (IS_POST){
			
			/**
			 * 验证
			 */
			// $obj_validate = new Validate();
			// $obj_validate->validateparam = array(
			// array("input"=>$_POST["member_email"], "require"=>"true", 'validator'=>'Email', "message"=>$lang['member_edit_valid_email']),
			// );
			// $error = $obj_validate->validate();
			// if ($error != ''){
			// 	showMessage($error);
			// }else {
				$update_array = array();
				$update_array['member_id']			= intval($_POST['member_id']);
				if (!empty($_POST['member_passwd'])){
					$update_array['member_passwd'] = md5($_POST['member_passwd']);
				}
				// $update_array['member_email']		= trim($_POST['member_email']);
                 $update_array['mobile']		= trim($_POST['mobile']);//<howard>
				// $update_array['member_truename']	= trim($_POST['member_truename']);
				// $update_array['member_sex'] 		= trim($_POST['member_sex']);
				$update_array['member_qq'] 			= trim($_POST['member_qq']);
				$update_array['member_ww']			= trim($_POST['member_ww']);
                                $update_array['qianming']			= trim($_POST['qianming']);//<howard>
                                // $update_array['jingyan']			= trim($_POST['jingyan']);//<howard>
                                // $update_array['member_points']			= trim($_POST['member_points']);//<howard>
                                // $update_array['groupid']			= trim($_POST['membergroup']);//<howard>
                                $update_array['available_predeposit']			= trim($_POST['available_predeposit']);//<howard>
				// $update_array['inform_allow'] 		= trim($_POST['inform_allow']);
				// $update_array['is_buy'] 			= trim($_POST['isbuy']);
				// $update_array['is_allowtalk'] 		= trim($_POST['allowtalk']);
				$update_array['member_state'] 		= trim($_POST['memberstate']);
				if (!empty($_POST['member_avatar'])){
					$update_array['member_avatar'] = $_POST['member_avatar'];
				}
				$result = $model_member->updateMember($update_array,intval($_POST['member_id']));
				if ($result){
					// $this->log(L('nc_edit,member_index_name').'[ID:'.$_POST['member_id'].']',1);
					$this->success('编辑会员成功',__CONTROLLER__.'/memberManager');
				}else {
					$this->error('编辑会员失败',__CONTROLLER__.'/memberManager');
				}
			// }
		}
		$condition['member_id'] = intval($_GET['member_id']);
		$member_array = $model_member->infoMember($condition);

		$this->assign('member_array',$member_array);
		$this->display();
	}
}