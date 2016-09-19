<?php
/*
 * 基本设置
 */
namespace Admin\Controller;
use Admin\Controller\AdminController;
class SettingController extends AdminController {

	/*
	 *  基本设置模块
     */
	public function index(){
		$set = M('setting');
		IF(IS_POST){
			
			$data = array( 
				'site_name'  => trim(I("post.site_name")),
				'icp_number' => trim(I("post.icp_number")),
				'site_phone' => trim(I("post.site_phone"))
			);
			//处理图片
			if($_FILES['site_logo']['error']==0){
				$data['site_logo'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['site_logo'],'site_logo',array('jpg','png','gif'));
			}
			if($_FILES['member_logo']['error']==0){
				$data['member_logo'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['member_logo'],'member_logo',array('jpg','png','gif'));
			}
			if($_FILES['default_user_portrait']['error']==0){
				$data['default_user_portrait'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['default_user_portrait'],'default_user_portrait',array('png'));
			}
			if($_FILES['seller_center_logo']['error']==0){
				$data['seller_center_logo'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['seller_center_logo'],'seller_center_logo',array('jpg','png','gif'));
			}
			if($_FILES['seller_center_banner']['error']==0){
				$data['seller_center_banner'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['seller_center_banner'],'seller_center_banner',array('jpg','png','gif'));
			}
			// if($_FILES['seller_center_slide1']['error']==0){
			// 	$data['seller_center_slide1'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['seller_center_slide1'],'seller_center_slide1',array('jpg','png','gif'));
			// }
			// if($_FILES['seller_center_slide2']['error']==0){
			// 	$data['seller_center_slide2'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['seller_center_slide2'],'seller_center_slide2',array('jpg','png','gif'));
			// }
			// if($_FILES['seller_center_slide3']['error']==0){
			// 	$data['seller_center_slide3'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['seller_center_slide3'],'seller_center_slide3',array('jpg','png','gif'));
			// }
			// if($_FILES['seller_center_slide4']['error']==0){
			// 	$data['seller_center_slide4'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['seller_center_slide4'],'seller_center_slide4',array('jpg','png','gif'));
			// }
			if($_FILES['default_goods_image']['error']==0){
				$data['default_goods_image'] = $this->upload($this->root.'/data/upload/shop/common/',$_FILES['default_goods_image'],'default_goods_image',array('jpg','png','gif'));
			}
			
			$fields = '';
			foreach($data as $key=>$d){
				$fields .=",'".$key."'";
			}
			$fields = substr($fields,1);
			$sql = "UPDATE ".$this->pre."setting SET value = CASE name ";
			foreach($data as $k=>$d){
				$sql.= sprintf("WHEN %s THEN %s ",'"'.$k.'"','"'.$d.'"');
			}
			$sql .="END WHERE name IN($fields)";
			$res = $this->db->execute($sql);
			if($res!==false){
				$this->success('保存成功',U('Admin/Setting/index'),0);
				exit;
			}
		}
		
		$setting = $set->where("
								name='site_name' OR 
								name='site_logo' OR 
								name='seller_center_logo' OR 
								name='member_logo' OR 
								name='default_user_portrait' OR 
								name='seller_center_banner' OR 
								name='seller_center_slide1' OR 
								name='seller_center_slide2' OR 
								name='seller_center_slide3' OR 
								name='seller_center_slide4' OR 
								name='default_goods_image' OR
								name='icp_number' OR 
								name='site_phone' 
							   ")->select();
		foreach($setting as $s){
			$setInfo[$s['name']]  = $s['value'];
		}
		//$this->e($setInfo);
		$this->assign('setInfo',$setInfo);
        $this->display('Setting/baseSetting');
	}
	

    /*
     * 银行列表，转账用
     */
    public function bankList(){
		$b = M('bank_type');
		
		if(IS_POST){
			$data['state']    = $_POST['state'];
			$data['bankname'] = trim($_POST['bankname']);
			if($this->check_duplicate2('bank_type','bankname',$data['bankname']) && trim(I('post.bankname'))!=I('post.bankname2') ){
				$this->error('该银行已存在',U('Admin/Setting/banklist'),0);
				exit;
			}
			if($_GET['act']=='add'){
				$res = $b->add($data);
				if($res!==false){
					$this->success('添加成功',U('Admin/Setting/banklist'));
					exit;
				}
			}elseif($_GET['act']=='edit'){
				$res = $b->where(['bank_id'=>(int)$_GET['id']])->save($data);
				if($res!==false){
					$this->success('修改成功',U('Admin/Setting/banklist'));
					exit;
				}
			}
		}
		if(isset($_GET['act'])){
			if($_GET['act']=='edit'){
				$bank = $b->where("bank_id = $_GET[id]")->find();
				$this->assign('bank',$bank);
			}elseif($_GET['act']=='del'){
				$del  = $b->where(['bank_id'=>(int)$_GET[id]])->delete();
				if($del){
					$this->success('删除成功',U('Admin/Setting/banklist'),0);
					exit;
				}
			}
			$this->display('Setting/banklist_edit');
			exit;
		}
			
		$bank = $b->select();
		$this->assign('bank',$bank);
        $this->display();
    }

    /*
     * 快递公司列表
     */
    public function expressList(){
		$e = M('express');
		if(IS_POST){
			$data['e_state'] = $_POST['e_state'];
			$data['e_order'] = $_POST['e_order'];
			$res = $e->where(['id'=>(int)$_GET['id']])->save($data);
			if($res!==false){
				$this->success('修改成功',U('Admin/Setting/expressList'));
				exit;
			}
		}
		if(isset($_GET['act'])){
			$express = $e->where(['id'=>(int)$_GET['id']])->find();
			$this->assign('express',$express);
			$this->display('Setting/express_edit');
			exit;
		}
		
		$express = $e->where(['tesu_deleted'=>0])->order('e_state desc,e_order')->select();
		$this->assign('express',$express);
        $this->display();
    }

    /*
     * 支付方式修改
     */
    public function paymentList(){
		$p = M('payment');
		if(IS_POST){
			$payment_state = $_POST['status'];
			$res = $p->where(['payment_id'=>(int)$_GET['id']])->save(['payment_state'=>$payment_state]);
			if($res!==false){
				$this->success('修改成功',U('Admin/Setting/paymentlist'));
				exit;
			}
		}
		if(isset($_GET['act'])){
			$paytype = $p->where(['payment_id'=>(int)$_GET['id']])->find();
			$this->assign('payment',$paytype);
			$this->display('Setting/paymentlist_edit');
			exit;
		}
		
		$paytype = $p->where(['tesu_deleted'=>0])->select();
		$this->assign('paytype',$paytype);
        $this->display();
    }
	
	
	/*检测重复*/
	public function check_duplicate(){  
		if($_GET['type']=="bankname"){  
			$table = 'bank_type';
			$field = 'bankname';
		}
		$field_value = I('get.value');
		
		$res = M("$table")->where("$field='".$field_value."'")->find();
		if($res){ 
			echo 1; exit;
		}else{
			echo 0; exit;
		}
	}
	
	/*第二道重复检测防护*/
	public function check_duplicate2($table,$field,$field_value){  
		$res = M("$table")->where("$field='".$field_value."'")->find();
		if($res){ 
			return true;
		}else{
			return false;
		}
	}
	

}

 





