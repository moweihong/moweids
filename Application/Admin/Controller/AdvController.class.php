<?php
namespace Admin\Controller;
use Admin\Controller\AdminController;
use Shop\Model\AdvModel;
class AdvController extends AdminController {
	
/**
	 *
	 * 管理广告位
	 */
	public function apManage(){
		
		/**
		 * 删除
		 */
		if(I('get.ap_id')){
			$ap_id=I('get.ap_id');
			if(M('adv_position')->where('ap_id='.$ap_id)->delete()){
				$this->success('删除成功');
				exit;
			}
		}
		/**
		 * 显示广告位管理界面
		 */
		$condition = array();
		$orderby   = 'ap_id desc';
		if($_GET['search_name'] != ''){
			$condition['ap_name']=array('like','%'.trim($_GET['search_name']).'%') ;
		}
		if($_GET['order'] == 'clicknum'){
			$orderby = 'click_num desc';
		}
		$this->pageSize='20';
		$ap_list  = M('adv_position')->where($condition)->page(I('get.p').','.$this->pageSize)->order($orderby)->select();
		$adv_list = M('adv')->select();//$this->e($adv_list);exit;
		$this->ap_count =  M('adv_position')->where($condition)->count();
		$Page = new \Think\Page($this->ap_count,$this->pageSize);	
		$this->assign('adv_list',$adv_list);
		$this->assign('ap_list',$ap_list);
		$this->assign('page',$Page->show());
		$this->display();
	}
	/**
	 *
	 * 广告位添加与编辑
	 */
	public function apManageForm(){
		$adv_position=M('adv_position');
		if(IS_POST){
			if($data=$adv_position->create()){//print_r($_FILES['default_content']);exit;  default_content 改成 foile
				//print_r($_FILES);exit;
//				if($_FILES['file']['error']==0){
//					$file_url=$this->upload($this->root.'/data/upload/shop/adv/',$_FILES['file'],time().rand(),['jpg']);
//					$data['default_content']=$file_url;
//				}
				$data['default_content']=$_POST['adv_content'];
				//print_r($data);exit;
				if($data['ap_id']){
					$result=$adv_position->where('ap_id='.$data['ap_id'])->data($data)->save();
					if($result!==false){
						$this->success('修改成功');
					}else{
						$this->error('修改失败');
					}
				}else{
					$result=$adv_position->add($data);
					if($result!==false){
						$this->success('添加成功');
					}else{
						$this->error('添加失败');
					}
				}
			}
		}else{
			if(I('get.ap_id')){
				$ap_id=I('get.ap_id');
				$this->info=$adv_position->where('ap_id='.$ap_id)->find();//print_r($this->info);exit;
			}
			$this->display();
		}				
		
	}
   

 /**
	 *
	 * 管理广告
	 */
	public function adv(){
		
		/**
		 * 删除
		 */
		if(I('get.adv_id')){
			$adv_id=I('get.adv_id');
			if(M('adv')->where('adv_id='.$adv_id)->delete()){
				$this->success('删除成功');
				exit;
			}
		}
		$condition['ap_id']=$ap_id=I('get.ap_id');
		$this->pageSize='20';
		$adv_list  = M('adv')->where($condition)->page(I('get.p').','.$this->pageSize)->order('adv_id desc')->select();//$this->e($adv_list);exit;
		$this->ap_info=M('adv_position')->where('ap_id='.$ap_id)->find();
		$this->adv_count =  M('adv')->where($condition)->count();
		$Page = new \Think\Page($this->adv_count,$this->pageSize);	
		$this->assign('adv_list',$adv_list);
		$this->assign('page',$Page->show());
		$this->display();
	} 
/**
	 *
	 * 广告添加与编辑
	 */
	public function advForm(){
		$adv=M('adv');
		$ap_id=I('get.ap_id');
		if(IS_POST){
			if($data=$adv->create()){//print_r($_POST);exit;
				$ap_class=I('post.ap_class');
//				if($_FILES['adv_pic']['error']==0){
//					$file_url=$this->upload($this->root.'/data/upload/shop/adv/',$_FILES['adv_pic'],time().rand(),['jpg']);
//				}
				$file_url=$_POST['adv_pic'];
				$installment_thumb_img=$_POST['installment_thumb_img'];
				if($ap_class==0){
					$adv_content = array(
						'adv_pic'     =>$file_url,
						'installment_thumb_img'=>$installment_thumb_img,
						'adv_pic_url' =>trim($_POST['adv_pic_url']),
						'adv_intro'  =>  trim($_POST['adv_intro'])     
					);
				}elseif($ap_class==1){
					$adv_content = array(
						'adv_word'    =>trim($_POST['adv_word']),
						'adv_word_url'=>trim($_POST['adv_word_url']),
						'adv_intro'  =>  trim($_POST['adv_intro'])      
					);						
				}elseif($ap_class==3){
					$adv_content = array(
						'flash_swf'  =>$file_url,
						'installment_thumb_img'=>$installment_thumb_img,
						'flash_url'  =>trim($_POST['flash_url']),
						'adv_intro'  =>  trim($_POST['adv_intro'])       
					);
				}//print_r($file_url);exit;
				$data['adv_start_date']=  strtotime($_POST['adv_start_date']);
				$data['adv_end_date']=  strtotime($_POST['adv_end_date']);//print_r($data);exit;
				$data['adv_content']=serialize($adv_content);
				if($data['adv_id']){					
					$result=$adv->where('adv_id='.$data['adv_id'])->data($data)->save();
					if($result!==false){
						$this->success('修改成功');
					}else{
						$this->error('修改失败');
					}
				}else{
					$result=$adv->add($data);
					if($result!==false){
						$this->success('添加成功');
					}else{
						$this->error('添加失败');
					}
				}
				
			}
		}else{
			if($ap_id==436){
				$size='420*420';
			}elseif($ap_id==464){
				$size='112*112';
			}elseif($ap_id==405||$ap_id==407){
				$size='635*314';
			}elseif($ap_id==409){
				$size='315*638';
			}else{
				$size='314*314';
			}
			if(I('get.adv_id')){
				$adv_id=I('get.adv_id');
				$info=$adv->where('adv_id='.$adv_id)->find();
				$info['adv_content']=  unserialize($info['adv_content']);
				$this->info=$info;
				//$this->e($info);exit;				
			}
			$this->size=$size;
			$this->ap_info=M('adv_position')->where('ap_id='.$ap_id)->find();
			$this->display();
		}				
		
	}	

    
}