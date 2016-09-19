<?php
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;
class TBrandController extends ShopCommonController {

	public $pageSize=20;
	/*
	 * 品牌专区首页
     */
	public function index()
	{
		$this->style_list=$style_list=M('brand_style')->select();		
        if($_GET['type']=='getStyle'){            
            $where['style_id']=$_GET['style_id']?$_GET['style_id']:array('gt',0);
            $where['tesu_deleted']  = 0;       
            $blist=M('brand')->where($where)->order('brand_sort asc')->select();
            $str='';
            foreach($blist as $k=>$v){
                $str.="<div class=\"item\" data-src='/data/upload/shop/brand/".$v['brand_shoppic']."'>";
                $str.="<div class=\"style-pic\"><a href=\"".U('TBrand/search', array('brand_id'=>$v['brand_id']))."\"><img src='/data/upload/shop/brand/".$v['brand_pic']."'></a></div>";                
                $str.="<div class=\"style-info\"><a href=\"".U('TBrand/search', array('brand_id'=>$v['brand_id']))."\">{$v['brand_desc']}</a></div>";
                $str.="</div>";
            }
            echo $str;exit;
        }
		$this->display();
	}
	
	/*
	 * 更多门店
	 */
	public function moreStore()
	{
		if($_GET['type']=='ajax'){
			$brand_id=  intval(trim($_GET['brand_id']));
			$con['brand_id']=$brand_id;
			$goods_list=D('goods')->getGoodsList($con);
			foreach($goods_list as $k=>$v){								
				$arr_store_id[$v['store_id']]=$v['store_id'];			
			}
			if($_GET['province']){
				$where['province_id']=$_GET['province'];
			}
			if($_GET['city']){
				$where['city_id']=$_GET['city'];
			}
			if($_GET['county']){
				$where['county']=$_GET['county'];
			}
			$where['store_id']=array('in',$arr_store_id);
			$store_list=M('store')->where($where)->select();
			$str='';
			foreach($store_list as $k=>$v){
				$str.="<li><a href=\"".urlShop('show_store', 'index', array('store_id'=>$v['store_id']))."\">{$v['store_name']}</a></li>";	
			}
			echo $str;exit;
		}
		$this->display();
	}

	/*
	 * 更多家居
	 */
	public function moreFurniture(){
		$brand_id = $_GET['brand_id'];
		if(empty($brand_id)){
			$this->error('品牌id不能为空');
		}
		$goodswhere['brand_id']=$brand_id;
		if($_GET['cateFormSubmit']=='ok'){//print_r($_POST);exit;
			$goodswhere['gc_id']=array('in',$_GET['strCid']);
			$this->arr_strCid=explode(',',$_GET['strCid']);
		}elseif($_GET['cate_id']){
			$goodswhere['gc_id']=$_GET['cate_id'];
		}
		//排序
		$orderBy=$_GET['orderBy'];
		if($orderBy=='salenum_asc'){
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
		if($_GET['bprovince_id']){
			$storeWhere['province']=$_GET['bprovince_id'];
		}
		if($_GET['bcity_id']){
			$storeWhere['city']=$_GET['bcity_id'];
		}
		if($_GET['barea_id']){
			$storeWhere['county']=$_GET['barea_id'];
		}
		$p = empty($_GET['p'])?0:($_GET['p']-1)*$this->pageSize;
		list($goods_list,$arr_attr_value_id,$arr_gc_id)=$this->getGoodsList($goodswhere,$field='*',$group='goods_commonid',$order, $limit=$p.",".$this->pageSize,$page = 0,$storeWhere);
		list($goods_list2)=$this->getGoodsList(array('brand_id'=>$brand_id));
		list($goods_list3)=$this->getGoodsList($goodswhere,$field='*',$group='goods_commonid',$order='', $limit=0,$page = 0,$storeWhere);
		//分类
		foreach($goods_list2 as $k=>$v){
			$arrCateId[$v['gc_id']]=$v['gc_id'];
		}
		if($arr_gc_id){
			$cateWhere['gc_id']=array('in',$arr_gc_id);
		}else{
			$cateWhere['gc_id']=$arrCateId?array('in',$arrCateId):'';
		}		
		$cate_list=M('goods_class')->where($cateWhere)->select();
		//属性
		$attrWhere['attr_value_id']=$arr_attr_value_id?array('in',$arr_attr_value_id):'';
		$attr_value_list=D('attribute')->getAttributeValueList($attrWhere);
		foreach($attr_value_list as $k=>$v){
			$attr_name=M('attribute')->where(array('attr_id'=>$v['attr_id']))->getField('attr_name');
			if($attr_name){
				$new_attr_value_list[$v['attr_id']]['attr_name']=$attr_name;
				$new_attr_value_list[$v['attr_id']]['attr_value'][$v['attr_value_id']]=$v;
			}			
		}
		//print_r($cate_list);exit;
		$this->assign('goods_list',$goods_list);
		$this->assign('cate_list',$cate_list);
		$this->assign('attr_list',$new_attr_value_list);
		$this->assign('page',  getPage(count($goods_list3),$this->pageSize));
		$this->display();

	}

	function getGoodsList($goodswhere,$field='*',$group,$order='', $limit=0,$page = 0,$storeWhere){
		$goods_common_list=D('goods_common')->goodsCommonList($goodswhere);//print_r(D('goods_common')->getLastSql());
		if($goods_common_list){
			foreach($goods_common_list as $k1=>$v1){
				$goodsInfo=D('goods')->where(array('goods_commonid'=>$v1['goods_commonid']))->find();
				if(is_array(unserialize($v1['goods_attr']))){
					//$goods_common_list[$k1]['aaaaaa']=unserialize($v1['goods_attr']);
					foreach(unserialize($v1['goods_attr']) as $k2=>$v2){
						foreach($v2 as $k3=>$v3){
							if($k3&&is_int($k3)){
								if($goodsInfo){
									$arr_attr_value_id[$k3]=$k3;
								}								
								if($_GET['attr_value_id']==$k3){
									$arr_goods_common_id[$v1['goods_commonid']]=$v1['goods_commonid'];	
									if($v1['gc_id']){
										$arr_gc_id[$v1['gc_id']]=$v1['gc_id'];
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
		$goods_list=D('goods')->getGoodsListByArea($goodswhere,$field,$group,$order,$limit,$page=0,$lock = false, $count = 0,$storeWhere );//print_r(D('goods')->getLastSql());				
		//print_r($goods_list);exit;
		return array($goods_list,$arr_attr_value_id,$arr_gc_id);
	}	

	/*
	 * 加盟品牌
	 */
	public function tplJoinBrand(){
		$this->display();
	}

	/*
     * 加盟品牌
     */
    public function joinBrand(){
    	//$this->jsonFail('他很懒，什么都没留下');
		if($_GET['type']=='apply'){
			//$data['dealer_store_id']=$_GET['store_id'];
			$data['factory_store_id']=$_GET['store_id'];
			$data['name']=$_GET['name'];
			$data['tel']=$_GET['tel'];
			$data['province']=$_GET['province_id'];
			$data['city']=$_GET['city_id'];
			$data['county']=$_GET['area_id'];//print_r($_GET);exit;
			$res=array('status'=>-1);
			if($id=M('brand_join')->add($data)){
				$res['status']=1;
			}
			echo json_encode($res);exit;
		}
    }
	
	/*
	 * 精品家居搜索页
	 */
	public function search(){
		$brand_id=  intval(trim($_GET['brand_id']));
        $con['brand_id']=$brand_id;		
        $this->goods_list=$goods_list=D('goods')->getGoodsOnlineList($con,$field='*',$page=0,$order, $limit=20,$group='goods_commonid');
		$goods_list2=D('goods')->getGoodsOnlineList($con,$field='store_id',$page=0,$order, $limit=800);
		$storeModel=M('store');
        foreach($goods_list2 as $k=>$v){
			$arr_store_id[$v['store_id']]=$v['store_id'];
        }
		$where['store_id']=$arr_store_id?array('in',$arr_store_id):'';
		$this->store_list=$storeModel->where($where)->select();//print_r($this->store_list);exit;
		$this->brandInfo=D('brand')->getOneBrand($con);
		$this->display();
	}
	


}