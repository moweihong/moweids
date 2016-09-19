<?php
/*
 * 搜索
 */
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;
class SearchController extends ShopCommonController {
	public $pageSize=20;

	/**
     * 方法功能介绍
     * @access public
     * @param mixed $db_config 
     * @return string
     */
	public function index()
	{
		$keyword = I('get.keyword');
		$cate_id = I('get.cate_id');
		//排序
		$orderBy=$_GET['orderBy'];
		if($orderBy=='salenum_asc'){//销量
			$order='goods_salenum asc';
		}
		if($orderBy=='salenum_desc'){
			$order='goods_salenum desc';
		}
		if($orderBy=='goods_click_asc'){//人气
			$order='goods_click asc';
		}
		if($orderBy=='goods_click_desc'){
			$order='goods_click desc';
		}
		if($orderBy=='goods_time_asc'){//发布时间
			$order='goods_addtime asc';
		}
		if($orderBy=='goods_time_desc'){
			$order='goods_addtime desc';
		}
		if(empty($keyword)&&!$cate_id){

			$this->searchAll($order);
			exit;
		}		
		if(is_null($keyword)||empty($keyword)){
			//根据类目搜索
			$this->searchByCateid($cate_id,$keyword='',$order);
		}

		if(is_null($cate_id)||empty($cate_id)){
			$this->searchByKeyword($cate_id='',$keyword,$order);
		}
	}

	/*
	 * 关键词搜索
	 */
	public function searchByKeyword($cate_id='',$keyword,$order){
		//根据关键字搜索
//		$model = D('goods');
//		$condtion['goods_name'] = array('like', "%{$keyword}%");
//		$p = empty($_GET['p'])?0:($_GET['p']-1)*$this->pageSize;
//		$list = $model->getGoodsOnlineList($condtion, $field = '*', $page = 0, $order, $limit = $p.",".$this->pageSize);
//		$this->assign('goods_list', $list);
//		$this->assign('page',  getPage(count($model->getGoodsOnlineList($condtion)),$this->pageSize));
//		$this->display();
		$this->getAllGoodsList($cate_id, $keyword, $order);

	}

	/*
	 * 分类搜索
	 */
	public function searchByCateid($cate_id,$keyword='',$order){
		$cid = $cate_id;
		if(empty($cid)){
			$this->error('分类id不能为空');
		}
		$this->getAllGoodsList($cid, $keyword, $order);

	}
	
	//搜索页品牌属性
	
	function getAllGoodsList($cid,$keyword,$order){
		if($cid){//按分类id搜索
			$goodswhere['gc_id']=$cid;
			$brandwhere['class_id']=$cid;
			$classwhere['gc_id']=$cid;
		}elseif($keyword){//按关键字搜索
			$glist=D('goods')->getGoodsOnlineList(array('goods_name'=>array('like','%'.$keyword.'%')),'gc_id,goods_name');//print_r($glist);exit;
			foreach($glist as $k=>$v){
				$arr_cid[$v['gc_id']]=$v['gc_id'];
			}
			$goodswhere['gc_id']=$arr_cid?array('in',$arr_cid):'';
			$brandwhere['class_id']=$arr_cid?array('in',$arr_cid):'';
			$classwhere['gc_id']=$arr_cid?array('in',$arr_cid):'';
		}		
		$goodswhere['is_offline']=0;//经销商发布的商品
		$goodswhere['goods_verify']=1;//审核通过
		
		if($_GET['brandFormSubmit']=='ok'){//print_r($_POST);exit;
			$goodswhere['brand_id']=array('in',$_GET['strBid']);
			$this->arr_strBid=explode(',',$_GET['strBid']);
		}elseif($_GET['brand_id']){
			$goodswhere['brand_id']=$_GET['brand_id'];
		}
		
		$p = empty($_GET['p'])?0:($_GET['p']-1)*$this->pageSize;
		list($goods_list,$arr_attr_value_id,$arr_brand_id)=$this->getGoodsList($goodswhere,$field='*',$group='goods_commonid',$order, $limit=$p.",".$this->pageSize);
		//品牌
		
		if($arr_brand_id){//die;
			$brandwhere['brand_id']=array('in',$arr_brand_id);
		}		
		$brand_list=M('brand')->where($brandwhere)->select();
		//属性
		$gcList=D('goods_class')->getGoodsClassList($classwhere);
		foreach($gcList as $k=>$v){
			$arr_type_id[]=$v['type_id'];
		}
		list($goods_list2)=$this->getGoodsList($goodswhere);
		if($cid){//分类搜索时显示分类信息
			$gcInfo=D('goods_class')->getGoodsClassInfo(array('gc_id'=>$cid));//这个地方怎么处理			
		}
		$gcInfo['goods_count']=count($goods_list2);
		if($arr_type_id){
			$attr_list=D('attribute')->getAttributeList(array('type_id'=>array('in',$arr_type_id)));			
			foreach($attr_list as $k=>$v){
				$attrWhere['attr_id']=$v['attr_id'];
				$attrWhere['attr_value_id']=$arr_attr_value_id?array('in',$arr_attr_value_id):'';
				$attr_value_list[]=D('attribute')->getAttributeValueList($attrWhere);
				foreach($attr_value_list as $k2=>$v2){
					foreach($v2 as $k3=>$v3){
						$attr_name=M('attribute')->where(array('attr_id'=>$v3['attr_id']))->getField('attr_name');
						if($attr_name){
							$new_attr_value_list[$v3['attr_id']]['attr_name']=$attr_name;
							$new_attr_value_list[$v3['attr_id']]['attr_value'][$v3['attr_value_id']]=$v3;
						}						
					}
						
				}	
			}
		}
		$this->assign('goods_list',$goods_list);
		$this->assign('attr_list',$new_attr_value_list);
		$this->assign('brand_list',$brand_list);
		$this->assign('gcInfo',$gcInfo);
		$this->assign('page',  getPage(count($goods_list2),$this->pageSize));
		$this->display();	
	}

	public function searchAll($order){
		//根据关键字搜索
		$model = D('goods');
		$p = empty($_GET['p'])?0:($_GET['p']-1)*$this->pageSize;
		$list = $model->getGoodsOnlineList($condtion, $field = '*', $page = 0, $order, $limit = $p.",".$this->pageSize);
		$this->assign('goods_list', $list);
		$this->assign('page',  getPage(count($model->getGoodsOnlineList($condtion)),$this->pageSize));
		$this->display();
	}

	/*
	 * 根据店铺名搜索
	 */
	public function searchStoreByName(){
		$this->display();
	}

	function getGoodsList($goodswhere,$field='*',$group='goods_commonid',$order='', $limit=0){
		$goods_common_list=D('goods_common')->goodsCommonList($goodswhere);
		if($goods_common_list){
			foreach($goods_common_list as $k1=>$v1){
				$goodsInfo=D('goods')->where(array('goods_commonid'=>$v1['goods_commonid']))->find();
				if(is_array(unserialize($v1['goods_attr']))){
					foreach(unserialize($v1['goods_attr']) as $k2=>$v2){
						foreach($v2 as $k3=>$v3){
							if($k3&&is_int($k3)){
								if($goodsInfo){
									$arr_attr_value_id[$k3]=$k3;
								}
								if($_GET['attr_value_id']==$k3){
									$arr_goods_common_id[$v1['goods_commonid']]=$v1['goods_commonid'];	
									if($v1['brand_id']){
										$arr_brand_id[$v1['brand_id']]=$v1['brand_id'];
									}									
								}								
							}
						}

					}																	
				}
			}
		}
		if($_GET['attr_value_id']&&!empty($arr_goods_common_id)){
			$goodswhere['goods_commonid']=array('in',$arr_goods_common_id);
		}
		if(I('get.keyword')){
			$goodswhere['goods_name']=array('like','%'.I('get.keyword').'%');
		}
		$goods_list=D('goods')->getGoodsOnlineList($goodswhere,$field,$page=0,$order,$limit,$group='goods_commonid');//print_r(D('goods')->getLastSql());				
		//print_r($goods_common_list);exit;
		return array($goods_list,$arr_attr_value_id,$arr_brand_id);
	}

}