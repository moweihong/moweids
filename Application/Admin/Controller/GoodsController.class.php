<?php
/*
	CREAT BY TangZhe 2016-08-29 商品管理
*/
namespace Admin\Controller;
use Admin\Controller\AdminController;
class GoodsController extends AdminController {
	
	//商品管理
	public function goods(){
		$where = '';
		switch($_GET['type']) {
			case 'wgxj':
				$where .= ' AND gcm.goods_state=10';
				break;
			case 'dsh':
				$where .= " AND gcm.goods_verify=10";
		}
		if(!empty($_GET['brand'])){
			$where .= " AND gcm.brand_id=".(int)$_GET['brand'];
		}
		if(!empty($_GET['cate'])){
			$where .= " AND gcm.gc_id=".(int)$_GET['cate'];
		}
		if(!empty($_GET['state'])){
			$goods_state = $_GET['state']=='x0'?0:(int)$_GET['state'];
			$where .= " AND gcm.goods_state=$goods_state";
		}
		if(!empty($_GET['verify'])){
			$goods_verify = $_GET['verify']=='v0'?0:(int)$_GET['verify'];
			$where .= " AND gcm.goods_verify=$goods_verify";
		}
		if(!empty($_GET['goods_name'])){
			$goods_name = trim(I('get.goods_name'));
			$where .= " AND gcm.goods_name like '%".$goods_name."%'";
		}
		if(!empty($_GET['store'])){
			$store = trim(I('get.store'));
			$where .= " AND st.store_name like '%".$store."%'";
		}
		if(!empty($_GET['common'])){
			$common = trim(I('get.common'));
			$where .= " AND gcm.goods_commonid like '%".$common."%'";
		}
		
		$sql		= "SELECT count(*) count FROM `$this->pre"."goods_common` gcm 
					   LEFT JOIN `$this->pre"."store` st ON gcm.store_id=st.store_id 
					   LEFT JOIN `$this->pre"."goods` g  ON gcm.goods_commonid=g.goods_commonid
					   LEFT JOIN `$this->pre"."goods_class` c ON gcm.gc_id=c.gc_id 
					   WHERE st.store_state=1 AND gcm.is_offline!=1 AND g.tesu_deleted=0 AND c.tesu_deleted=0 $where";
		$count 		= $this->db->query($sql);
		$Page       = new \Think\Page($count[0]['count'],15);
		$show       = $Page->show(); 
		$sql		= "SELECT gcm.*,gcm.goods_state gs,g.goods_id,st.store_name,gcm.goods_addtime gtime,gcm.goods_verify gv,g.goods_storage,c.gc_name gname FROM `$this->pre"."goods_common` gcm 
					   LEFT JOIN `$this->pre"."store` st ON gcm.store_id=st.store_id 
					   LEFT JOIN `$this->pre"."goods` g  ON gcm.goods_commonid=g.goods_commonid
					   LEFT JOIN `$this->pre"."goods_class` c ON gcm.gc_id=c.gc_id 
					   WHERE st.store_state=1 AND gcm.is_offline!=1 AND g.tesu_deleted=0 AND c.tesu_deleted=0 $where  
					   ORDER BY gcm.goods_addtime desc LIMIT $Page->firstRow,$Page->listRows";
		$list		= $this->db->query($sql);
		 
		$this->assign('list',$list);
		$this->assign('brand',$this->get_brand_list());
		$this->assign('cate',$this->getGoodsCate(0,0,3));
		$this->assign('count',$count[0]['count']);
		$this->assign('page',$show);
		$this->display('Goods/goods');
	}
	//商品设置
	public function goods_set(){
		$setting  = M('setting');
		IF(IS_POST){ 
			$data['value'] = I('post.goods_verify');
			$rest = $setting->where(['name'=>'goods_verify'])->save($data);
			if($rest!==false){
				$this->success('设置成功',U('Admin/Goods/goods_set'),0);
				exit;
			}
		}
		$this->assign('goods_verify',$setting->where(['name'=>'goods_verify'])->find());
		$this->display('Goods/goods_set');
	}
	//下架商品
	public function goods_down(){
		$gm  = M('goods_common');
		IF(IS_POST){ 
			$data['goods_state']       = $data['goods_verify'] 	 = 10;
			$rest  = M('goods')->where(['goods_commonid'=>(int)$_GET['id']])->save($data);
			
			$data['goods_stateremark'] = I('post.goods_stateremark');
			$rest2 = $gm->where(['goods_commonid'=>(int)$_GET['id']])->save($data);
			if($rest!==false && $rest2!==false){
				$this->success('下架成功',U('Admin/Goods/goods'),0);
				exit;
			}
		}
		$this->assign('goods_stateremark',$gm->where(['goods_commonid'=>(int)$_GET['id']])->find());
		$this->display('Goods/goods_down');
	}
	//审核商品
	public function goods_verify(){
		$gm  = M('goods_common');
		$cm  = M('goods');
		IF(isset($_GET['type'])){ 
			if($_GET['type']=='yes'){
				$data['goods_verify'] = 1;
				$rest  = $gm->where(['goods_commonid'=>(int)$_GET['id']])->save($data);
				$rest2 = $cm->where(['goods_commonid'=>(int)$_GET['id']])->save(array('goods_verify'=>1));
				if($rest!==false && $rest2!==false){
					$this->success('操作成功',U('Admin/Goods/goods?type=dsh'),0);
					exit;
				}
			}elseif($_GET['type']=='no'){
				if(IS_POST){ 
					$data['goods_verifyremark'] = I('post.goods_verifyremark');
					$data['goods_verify']       = 0;
					$rest  = $gm->where(['goods_commonid'=>(int)$_GET['id']])->save($data);
					$rest2 = $cm->where(['goods_commonid'=>(int)$_GET['id']])->save(array('goods_verify'=>0));
					if($rest!==false && $rest2!==false){
						$this->success('操作成功',U('Admin/Goods/goods?type=dsh'),0);
						exit;
					}
				}
				$this->assign('goods_verifyremark',$gm->where(['goods_commonid'=>(int)$_GET['id']])->find());
				$this->display('Goods/goods_verify');
				exit;
			}
		}
	}
	
	//商品分类
	public function category(){
		$cate   = M('goods_class');
		IF(isset($_GET['act'])){
			$gc_id = (int)$_GET['id'];
			if($_GET['act']=='edit'){ 
				$cateimg = $this->root."/data/upload/shop/common/category-pic-".$_GET['id'].".jpg";
				if(file_exists($cateimg))
					$cimg = 1;
				else
					$cimg = 0;		
				$gc = $cate->where(['gc_id'=>$gc_id])->find(); 
				if($gc['attr_father']){
					$gcf = json_decode($gc['attr_father']);  
					foreach($gcf as $k=>$gaf){
						$attr_father2[$k] = $gaf;
						$attr_def[$k] = M('attribute_value')->where("attr_id=$gaf")->select();
					}
					//$this->e($attr_def);exit;
				}else{
					$attr_father2 = array();
					$attr_def     = array();
				} 
				
				$attr = json_decode($gc['attr']);
				foreach($attr as $ark=>$arr){
					foreach($arr as $ark2=>$ar){
						$attr2[$ark][$ark2] = $ar;
					}
				}
				//print_r(json_decode($gc['attr_father']));
				//print_r(json_decode($gc['attr']));exit;
				$this->assign('gc',$gc);
				$this->assign('cimg',$cimg); 
				$this->assign('attr_father2',$attr_father2);
				$this->assign('attr_def',$attr_def);
				$this->assign('spec2' ,json_decode($gc['spec']));
				$this->assign('attr' ,$attr2);
			}elseif($_GET['act']=='del'){
				//检查是否有下级分类
				if($cate->where(['gc_parent_id'=>$gc_id])->where("tesu_deleted!=1")->find()){
					$this->error('该分类下存在子分类，不允许删除',U('Admin/Goods/category'),0);
				}
				//检查分类下是否有商品
				if(M('goods_common')->where(['gc_id'=>$gc_id])->find()){
					$this->error('该分类下存在商品，不允许删除',U('Admin/Goods/category'),0);
				}
				$up  = $cate->where(['gc_id'=>$gc_id])->save(['tesu_deleted'=>1]);
				if($up){
					$this->success('删除成功',U('Admin/Goods/category/p/'.$_GET['p']),0);
					exit;
				}
			}
			
			if(IS_POST && $_POST){
				if($_POST['act']=='add'){  
					if(isset($_POST['aid'])){
						foreach($_POST['aid'] as $ai){
							foreach($ai as $ak=>$akk){
								$aid[$akk]=$ak;
							}
						}
					}
					if($_POST['gc_parent_id']==0){      //关联的数据，一级分类直插
						$data['attr_father'] =  json_encode($aid);
						$data['spec']  = isset($_POST['spec']) ?json_encode($_POST['spec']):'';
						$data['attr']  = isset($_POST['attr']) ?json_encode($_POST['attr']):'';
					}elseif($_POST['gc_parent_id']>0){  //非一级分类 且未勾选任何选项时继承上级
						if(empty($_POST['attr_father'])||empty($_POST['spec'])||empty($_POST['attr'])){
							$unionData = $cate->where(['gc_id'=>$_POST['gc_parent_id']])->find(); 
						} 
						$data['attr_father'] = isset($_POST['aid'])?json_encode($aid):$unionData['attr_father'];
						$data['spec']  = isset($_POST['spec']) ?json_encode($_POST['spec']) :$unionData['spec'];
						$data['attr']  = isset($_POST['attr']) ?json_encode($_POST['attr']) :$unionData['attr'];
					}
					//$this->e($_POST,false);
					//$this->e($data); 
					$data['gc_name']     = trim(I('post.gc_name'));
					$data['gc_parent_id']= $_POST['gc_parent_id'];
					$data['type_id']     = 0;
					$data['type_name']   = 0;
					$data['gc_title']    = '';
					if($this->check_duplicate('goods_class','gc_name',$data['gc_name'],1)){
						$this->error('分类名已存在','?act=add',0);
						exit;
					}   	 
					$addResult           = $cate->add($data);
					//图片上床
					if($_FILES['gc_img']['error']==0){
						$this->upload($this->root.'/data/upload/shop/common/',$_FILES['gc_img'],'category-pic-'.$cate->getLastInsID(),['jpg']);
					}
					
					if($addResult){ 
						$this->success('添加成功',U('Admin/Goods/category/p/'.$_GET['p']),0);
						exit;
					}
				}elseif($_POST['act']=='edit'){  
					if(isset($_POST['aid'])){
						foreach($_POST['aid'] as $ai){
							foreach($ai as $ak=>$akk){
								$aid[$akk]=$ak;
							}
						}
					}
					if($_POST['gc_parent_id']==0){      //关联的数据，一级分类直插
						//$this->e($_POST);EXIT; 
						$data['attr_father'] = json_encode($aid);
						$data['spec']  = isset($_POST['spec']) ?json_encode($_POST['spec']):'';
						$data['attr']  = isset($_POST['attr']) ?json_encode($_POST['attr']):'';
					}elseif($_POST['gc_parent_id']>0){  //非一级分类 且未勾选任何选项时继承上级
						if(empty($_POST['attr_father'])||empty($_POST['spec'])||empty($_POST['attr'])){
							$unionData = $cate->where(['gc_id'=>$_POST['gc_parent_id']])->find(); 
						} 
						$data['attr_father'] = isset($_POST['aid'])?json_encode($aid):$unionData['attr_father'];
						$data['spec']  = isset($_POST['spec']) ?json_encode($_POST['spec']) :$unionData['spec'];
						$data['attr']  = isset($_POST['attr']) ?json_encode($_POST['attr']) :$unionData['attr'];
					}
					$data['gc_name'] = trim(I('post.gc_name'));
					$data['gc_parent_id']= $_POST['gc_parent_id'];
					if($this->check_duplicate('goods_class','gc_name',$data['gc_name'],1) && trim(I('post.gc_name'))!=I('post.old_gc_name')){
						$this->error('分类名已存在','?act=edit&id='.$gc_id,0);
						exit;
					} 
					if($_FILES['gc_img']['error']==0){
						$this->upload($this->root.'/data/upload/shop/common/',$_FILES['gc_img'],'category-pic-'.(int)$_GET['id'],array('jpg'));
					}
					$upResult        = $cate->where(['gc_id'=>$gc_id])->save($data);  
						if($upResult ||  trim(I('post.gc_name'))==I('post.old_gc_name')){ 
							$this->success('修改成功',U('Admin/Goods/category/p/'.$_GET['p']),0);
							exit;
						}
				}
			}
			
			//$brand = $this->get_brand_list();
			$spec  = $this->get_spec_list();
			$attr_father  = $this->get_attr_list();
		
			$cate = $this->getGoodsCate(0,0,2);
			
			$this->assign('cate',$cate);
			//$this->assign('brand',$brand);
			$this->assign('spec',$spec);
			$this->assign('attr_father',$attr_father);
			$this->display('Goods/category_edit'); 
			exit;
		}
		
		$cateStore = $this->getGoodsCate(0,0,3);
		foreach($cateStore as $c){
			if($c['level']==0){
				$gcate[$c['gc_id']]   = $c;
			}elseif($c['level']==1){
				$gcate[$c['gc_parent_id']]['two_level'][$c['gc_id']]   = $c;
			}
		}
		foreach($cateStore as $cat){
			if($cat['level']==2){
				foreach($gcate as &$g){
					if(isset($g['two_level'][$cat['gc_parent_id']])){
						$g['two_level'][$cat['gc_parent_id']]['three_level'][$cat['gc_id']] = $cat;
					}
				}
			}
		}
		//print_r($gcate);exit;
		$this->assign('gcate',$gcate);
		$this->assign('count',count($cateStore));
		$this->display('Goods/category');
	}

  
	
	//商品属性
    public function attribute(){    
		IF(isset($_GET['act'])){
			$attribute       = M('attribute');
			$attribute_value = M('attribute_value');
			$attr_id = (int)$_GET['attr_id'];
			if($_GET['act']=='del'){
				$del  = M('attribute')->where(['attr_id'=>$attr_id])->delete();
				if($del){
					$this->delAttrValue($attr_id);
					$this->success('删除成功',U('Admin/Goods/attribute/p/'.$_GET['p']),0);
					exit;
				}
			}elseif($_GET['act']=='edit'){
				$attr       = $attribute->where(['attr_id'=>$attr_id])->find(); 
				$attr_val   = $attribute_value->where(['attr_id'=>$attr_id])->select(); 
				$attr_value = '';
				foreach($attr_val as $v){
					$attr_value .= $v['attr_value_name'].',';
				}
				$this->assign('attr_value',substr($attr_value,0,-1));
				$this->assign('attr',$attr);
			}
			
			if(IS_POST && $_POST){
				if($_POST['act']=='add'){ 
					$data = M('attribute');
					$data->create();  
					$data->attr_name = trim(I('post.attrName'));
					$data->attr_show = I('post.attr_show');
					$data->attr_sort = 0;
					$data->type_id   = 0;
					if($this->checkAttrName($data->attr_name)){
						$this->error('属性名已存在',U('Admin/Goods/attribute/p/'.$_GET['p']),0);
						exit;
					}
					$atrrResult      = $data->add();
					$attr_id         = $data->getLastInsID();
					$atrrValueResult = $this->addAttrValue($attr_id,$_POST['attr_value']);
					if($atrrResult && $atrrValueResult){ 
						$this->success('添加成功',U('Admin/Goods/attribute/p/'.$_GET['p']),0);
						exit;
					}
				}elseif($_POST['act']=='edit'){ 
					if($this->checkAttrName(trim(I('post.attrName')))&&trim(I('post.attrName'))!=I('post.attrName_old')){
						$this->error('属性名已存在',U('Admin/Goods/attribute?act=edit&attr_id='.$attr_id),0);
						exit;
					}
					if($this->delAttrValue($attr_id)){  
						$data['attr_name'] = trim(I('post.attrName'));
						$data['attr_show'] = I('post.attr_show');
						$atrrResult        = $attribute->where(['attr_id'=>$attr_id])->save($data);  //更新属性
						$atrrValueResult   = $this->addAttrValue($attr_id,$_POST['attr_value']);	 //更新属性值
						if($atrrValueResult){ 
							$this->success('修改成功',U('Admin/Goods/attribute/p/'.$_GET['p']),0);
							exit;
						}
					}
				}
			}
			$this->display('Goods/attribute_edit'); 
			exit;
		}
		
		$num    = 15; //每页显示15条
		$page   = isset($_GET['p'])?(int)$_GET['p']:1;
		$star   = ($page-1)*$num;
		$count  = M('attribute')->where(['type_id'=>0])->count();
		if($star>=$count){//手动修改页数，跳回
			$this->success('页数错误','/Admin/Goods/attribute');
			exit;
		}
		$sql    = "SELECT attr_id FROM `".$this->pre ."attribute` WHERE type_id=0 LIMIT $star,$num";
		$id     = $this->db->query($sql);
		$attr_id='';
		foreach($id as $d){
			$attr_id .= $d['attr_id'].',';
		}
		$attr_id = rtrim($attr_id,',');
		$sql = "SELECT at.attr_id,at.attr_name,at.attr_show,atv.attr_value_name FROM `".$this->pre ."attribute` at 
				left JOIN allwood_attribute_value atv on at.attr_id=atv.attr_id 
				WHERE at.attr_id IN($attr_id) AND at.type_id=0 ORDER BY attr_id DESC";
		$attrList =  $this->db->query($sql);
		foreach($attrList as $a){
			$list[$a['attr_name']]['attr_show']  = $a['attr_show'];
			$list[$a['attr_name']]['attr_id']    = $a['attr_id'];
			$list[$a['attr_name']]['attr_value'].= $a['attr_value_name'].',';
		} 
		 
		$Page  = new \Think\Page($count,$num);  
		$show  = $Page->show(); 		        
		 
		$this->assign('list',$list);
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->display('Goods/attribute'); 
    }
	
	//商品规格
	public function specifications(){
		$spec  = M('spec');
		$sp_id = (int)$_GET['sp_id'];
		IF(isset($_GET['act'])){
			if($_GET['act']=='edit'){ 
				$sp = $spec->where(['sp_id'=>$sp_id])->find(); 
				$this->assign('sp',$sp);
			}elseif($_GET['act']=='del'){
				$del  = $spec->where(['sp_id'=>$sp_id])->delete();
				if($del){
					$this->success('删除成功',U('Admin/Goods/specifications/p/'.$_GET['p']),0);
					exit;
				}
			}
			
			if(IS_POST && $_POST){
				if($_POST['act']=='add'){ 
					$data['sp_name'] = trim(I('post.sp_name'));
					$data['sp_sort'] = 0;
					if($this->check_duplicate('spec','sp_name',$data['sp_name'])){
						$this->error('规格名已存在',U('Admin/Goods/specifications/p/'.$_GET['p']),0);
						exit;
					}
					$addResult          = $spec->add($data);
					if($addResult){ 
						$this->success('添加成功',U('Admin/Goods/specifications/p/'.$_GET['p']),0);
						exit;
					}
				}elseif($_POST['act']=='edit'){
					$data['sp_name'] = trim(I('post.sp_name'));
					if($this->check_duplicate('spec','sp_name',$data['sp_name'])&&trim(I('post.sp_name'))!=I('post.sp_name_old')){
						$this->error('规格名已存在',U('Admin/Goods/specifications?act=edit&sp_id='.$sp_id),0);
						exit;
					}
					$upResult        = $spec->where(['sp_id'=>$sp_id])->save($data);  
					if($upResult || trim(I('post.sp_name'))==I('post.sp_name_old')){ 
						$this->success('修改成功',U('Admin/Goods/specifications/p/'.$_GET['p']),0);
						exit;
					}
				}
			}
			$this->display('Goods/specification_edit'); 
			exit;
		}
		
		$count = $spec->count();
		$Page  = new \Think\Page($count,15);   //实例化分页类 传入总记录数和每页显示的记录数
		$show  = $Page->show();   			  //分页显示输出
		$list  = $spec->limit($Page->firstRow.','.$Page->listRows)->order("sp_id DESC")->select();  //获取对应的文章列表信息
		
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign('list',$list);
		$this->display('Goods/specifications'); 
	}
	
	//品牌管理
	public function brand(){
		$brand = M('brand');
		IF(isset($_GET['act'])){
			$brand_id = (int)$_GET['brand_id'];
			if($_GET['act']=='edit'){ 
				$brandInfo = $brand->where(['brand_id'=>$brand_id])->find(); 
				$this->assign('brandInfo',$brandInfo);
			}elseif($_GET['act']=='del'){
				$del  = $brand->where(['brand_id'=>$brand_id])->save(['tesu_deleted'=>1]);
				if($del){
					$this->success('删除成功',U('Admin/Goods/brand/p/'.$_GET['p']),0);
					exit;
				}
			}elseif($_GET['act']=='examine'){
				$examine  = $brand->where(['brand_id'=>$brand_id])->save(['brand_apply'=>1]);
				if($examine){
					$this->success('审核成功',U('Admin/Goods/brand/pend/1/p/'.$_GET['p']),0);
					exit;
				}
			}elseif($_GET['act']=='examine_refuse'){
				$examine  = $brand->where(['brand_id'=>$brand_id])->save(['brand_apply'=>2]);
				if($examine){
					$this->success('操作成功',U('Admin/Goods/brand/pend/1/p/'.$_GET['p']),0);
					exit;
				}
			}
			
			if(IS_POST && $_POST){
				$data['brand_name']     = trim(I('post.brand_name'));
				$data['class_id']       = $_POST['class_id'];
				$data['store_id']       = $_POST['store_id'];
				$data['style_id']       = $_POST['style_id'];
				$data['brand_desc']     = $_POST['brand_desc'];
				$data['brand_recommend']= $_POST['brand_recommend'];
				if($_POST['act']=='add'){ 
					$data['brand_apply']    = 1;
					//上床
					if($_FILES['brand_pic']['error']==0){
						$data['brand_pic'] = $this->upload($this->root.'/data/upload/shop/brand/',$_FILES['brand_pic'],'brand_pic'.time().rand(1000,9999),['jpg','png','jpg']);
					}
					if($_FILES['brand_shoppic']['error']==0){
						$data['brand_shoppic'] = $this->upload($this->root.'/data/upload/shop/brand/',$_FILES['brand_shoppic'],'brand_shoppic'.time().rand(1000,9999),['jpg','png','jpg']);
					}
					if($_FILES['brand_zhaopinpic']['error']==0){
						$data['brand_zhaopinpic'] = $this->upload($this->root.'/data/upload/shop/brand/',$_FILES['brand_zhaopinpic'],'brand_recruit'.time().rand(1000,9999),['jpg','png','jpg']);
					}
				 	if($this->check_duplicate('brand','brand_name',$data['brand_name'],1)){
						$this->error('品牌名已存在','?act=add',0);
						exit;
					}  
					$addResult = $brand->add($data);	   
					if($addResult){ 
						$this->success('添加成功',U('Admin/Goods/brand/p/'.$_GET['p']),0);
						exit;
					}
				}elseif($_POST['act']=='edit'){
					if($this->check_duplicate('brand','brand_name',$data['brand_name'],1) && trim(I('post.brand_name'))!=I('post.old_brand_name')){
						$this->error('品牌名已存在','?act=edit&brand_id='.$brand_id,0);
						exit;
					}               			
					if($_FILES['brand_pic']['error']==0){
						$data['brand_pic'] = $this->upload($this->root.'/data/upload/shop/brand/',$_FILES['brand_pic'],'brand_pic'.time().rand(1000,9999),['jpg','png','jpg']);
					}
					if($_FILES['brand_shoppic']['error']==0){
						$data['brand_shoppic'] = $this->upload($this->root.'/data/upload/shop/brand/',$_FILES['brand_shoppic'],'brand_shoppic'.time().rand(1000,9999),['jpg','png','jpg']);
					}
					if($_FILES['brand_zhaopinpic']['error']==0){
						$data['brand_zhaopinpic'] = $this->upload($this->root.'/data/upload/shop/brand/',$_FILES['brand_zhaopinpic'],'brand_recruit'.time().rand(1000,9999),['jpg','png','jpg']);
					}
				  
					$upResult        = $brand->where(['brand_id'=>$brand_id])->save($data);  
					if($upResult || trim(I('post.brand_name'))==I('post.old_brand_name')){ 
						$this->success('修改成功',U('Admin/Goods/brand/p/'.$_GET['p']),0);
						exit;
					}
				}
			}
			$cate  = $this->getGoodsCate(0,0,2);
			$fact  = $this->getfactory();
			$style = $this->get_brand_style();
			$this->assign('cate',$cate);
			$this->assign('fact',$fact);
			$this->assign('style',$style);
			$this->display('Goods/brand_edit'); 
			exit;
		}
		
		if(isset($_GET['pend'])&&isset($_GET['refuse'])){
			$pend = 2;  //审核不通过
		}elseif(isset($_GET['pend'])){
			$pend = 0; //待审核
		}else{
			$pend = 1; //审核通过
		}
		$count = $brand->where(array('tesu_deleted'=>0,'brand_apply'=>$pend))->count();
		$Page       = new \Think\Page($count,15);
		$show       = $Page->show(); 
		$sql   = "SELECT b.*,s.store_name,gc.gc_name FROM $this->pre"."brand b 
				  LEFT JOIN $this->pre"."store s ON b.store_id=s.store_id 
				  LEFT JOIN $this->pre"."goods_class gc ON b.class_id=gc.gc_id
				  WHERE b.tesu_deleted=0 AND b.brand_apply=$pend ORDER BY brand_id DESC LIMIT $Page->firstRow,$Page->listRows"; 	
		$list  = $this->db->query($sql);

		$this->assign('list',$list);
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->display('Goods/brand'); 
	}
	
	//品牌风格
	public function brand_style(){
		$brand_style  = M('brand_style');
		$id = (int)$_GET['id'];
		IF(isset($_GET['act'])){
			if($_GET['act']=='edit'){ 
				$style = $brand_style->where(['id'=>$id])->find(); 
				$this->assign('style',$style);
			}elseif($_GET['act']=='del'){
				$del  = $brand_style->where(['id'=>$id])->delete();
				if($del){
					$this->success('删除成功',U('Admin/Goods/brand_style/p/'.$_GET['p']),0);
					exit;
				}
			}
			
			if(IS_POST && $_POST){
				if($_POST['act']=='add'){ 
					$data['style_name'] = trim(I('post.style_name'));
					$data['status']     = I('post.status');
					$data['addtime'] 	= time();
					if($this->check_duplicate('brand_style','style_name',$data['style_name'])){
						$this->error('规格名已存在',U('Admin/Goods/brand_style/p/'.$_GET['p']),0);
						exit;
					}
					$addResult          = $brand_style->add($data);
					if($addResult){ 
						$this->success('添加成功',U('Admin/Goods/brand_style/p/'.$_GET['p']),0);
						exit;
					}
				}elseif($_POST['act']=='edit'){
					$data['style_name'] = trim(I('post.style_name'));
					$data['status']     = trim(I('post.status'));
					$data['addtime']    = time();
					if($this->check_duplicate('brand_style','style_name',$data['style_name'])&&trim(I('post.style_name'))!=I('post.style_name_old')){
						$this->error('规格名已存在',U('Admin/Goods/brand_style?act=edit&id='.$id),0);
						exit;
					}
					$upResult           = $brand_style->where(['id'=>$id])->save($data);  
						if($upResult){ 
							$this->success('修改成功',U('Admin/Goods/brand_style/p/'.$_GET['p']),0);
							exit;
						}
				}
			}
			$this->display('Goods/brand_style_edit'); 
			exit;
		}
		
		$count = $brand_style->count();
		$Page  = new \Think\Page($count,15);
		$show  = $Page->show();   	
		$list  = $brand_style->limit($Page->firstRow.','.$Page->listRows)->order("id desc")->select();
		
		$this->assign('count',$count);
		$this->assign('page',$show);
		$this->assign('list',$list);
		$this->display('Goods/brand_style');
	}
	
	/*删除属性值*/
	private function delAttrValue($attr_id){
		$del = M('attribute_value')->where(['attr_id'=>$attr_id])->delete();
		return $del;
	}
	
	/*插入属性值*/
	private function addAttrValue($attr_id,$attr_value){
		$ar = explode(',',trim($attr_value,','));
		$sql= "INSERT INTO `".$this->pre ."attribute_value` (attr_value_name,attr_id,type_id,attr_value_sort) VALUES";
		foreach($ar as $a){
			$sql .= "('".$a."','".$attr_id."',0,0),";
		}
		$sql = substr($sql,0,-1);
		return $atrrValueResult = $this->db->execute($sql);
	}
	
	/*检测重复*/
	public function ajax_check_duplicate(){  
		$value = trim(I('get.value'));
		if($_GET['type']=="goodsCate"){  
			$table = 'goods_class';
			$field = 'gc_name';
			$tesu_deleted = 1;
		}elseif($_GET['type']=="attrName"){
			$table = 'attribute';
			$field = 'attr_name';
			$res = M("$table")->where("$field='".$value."' AND type_id=0")->find();
			if($res){
				echo 1; exit;
			}else{
				echo 0; exit;
			}
		}elseif($_GET['type']=="style_name"){
			$table = 'brand_style';
			$field = 'style_name';
		}elseif($_GET['type']=="spec_name"){
			$table = 'spec';
			$field = 'sp_name';
		}elseif($_GET['type']=="brand_name"){
			$table = 'brand';
			$field = 'brand_name';
			$tesu_deleted = 1;
		}
		
		if($this->check_duplicate($table,$field,$value,$tesu_deleted)){
			echo 1; exit;
		}else{
			echo 0; exit;
		}
	}
	
	/*检测字段值是否重复*/
	private function check_duplicate($table,$field,$field_value,$tesu_deleted=false){
		if($tesu_deleted)
			$res = M("$table")->where("$field='".$field_value."' AND tesu_deleted=0")->find();
		else
			$res = M("$table")->where("$field='".$field_value."'")->find();
		if($res)
			return true;
		else
			return false;
	} 
	
	//检测attrname是否重复
	private function checkAttrName($value){
		$res = M("attribute")->where("attr_name='".$value."' AND type_id=0")->find();
		if($res){
			return true;
		}else{
			return false;
		}
	}
	
	/*商品无限级分类*/
	private function getGoodsCate($pid,$level,$getLevel){
		$getLevel = $getLevel-1; //取到第几级,只取顶级$getLevel为0
		static $res;
		$sql = "SELECT * FROM `".$this->pre."goods_class` WHERE gc_parent_id=".$pid." AND tesu_deleted=0 ORDER BY gc_id DESC";
		$result = $this->db->query($sql);
		if($result){
			foreach($result as $v){
				$v['level'] = $level;
				$res[] 		= $v;
				if($level<=$getLevel)  
					$this->getGoodsCate($v['gc_id'],$level+1,$getLevel);
			}
		}
		return $res;
	}
	 
	
	/*家具厂列表*/
	private function getfactory(){
		return M('store')->where(['com_type'=>3])->select();
	}
	
	/*品牌风格列表*/
	private function get_brand_style(){
		return M('brand_style')->order('id desc')->select();
	}
	
	/*获取品牌列表*/
	private function get_brand_list(){
		return M('brand')->where("tesu_deleted !='1'")->order('brand_id desc')->select();
	}
	
	/*获取规格列表*/
	private function get_spec_list(){
		return M('spec')->order('sp_id desc')->select();
	}
	
	/*获取属性列表*/
	private function get_attr_list(){
		return M('attribute')->where('type_id=0')->order('attr_id desc')->select();
	}
	
	/*获取属性值列表*/
	public function get_attr_value($attr_id=false){
		if(!$attr_id) $attr_id = $_GET['attr_id'];
		$res = M('attribute_value')->where("attr_id=$attr_id")->select();
		if(count($res)==1 && $res[0]['attr_value_name']==''){  
			echo json_encode(['status'=>0]);
			exit;
		}
		else{ 
			$return['status']     = 1;
			$return['count']      = count($res);
			$return['attr_value'] = $res;  
			echo json_encode($return);
			exit;
		}
	}
	

	
	
}