<?php
/*
 * 家居经销商前端店铺首页
 */


namespace Shop\Controller;
use Think\Controller;
class ShowVendorController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $store_id = I('get.store_id');
        if(intval($store_id) === false){
            $this->error('找不到店铺');
        }
        $store_info = M('Store')->where(array('store_id'=>intval($_GET['store_id'])))->find();
        if(is_null($store_info)){
            $this->error('找不到店铺');   
        }
        $this->assign('store_info',$store_info);
        //输出店铺横幅
        $store_slide = explode(',', $store_info['store_slide']);
        foreach ($store_slide as $key => $value) {
            $value = "/data/upload/shop/store/slide".$value;

            $store_slide[$key] = $value;
            $store_slide[$key] = array();
            $store_slide[$key]['img'] = $value;
        }


        $this->assign('TopSlide', $store_slide);
        //设置店铺导航到商家首页
        $this->_setNavigation(intval($_GET['store_id']));
    }

	/*
	 * 家居经销商前端店铺首页
     */
	public function index()
	{

        $condition = array();
        $condition['store_id'] = $this->store_info['store_id'];
        
        $model_goods = D('Goods'); // 字段
        $fieldstr = "goods_id,goods_commonid,goods_name,goods_jingle,store_id,store_name,goods_price,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,color_id,evaluation_good_star,evaluation_count";
        //得到最新12个商品列表
        $new_goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fieldstr, 'goods_id desc', '1,12');
        $this->assign('new_goods_list', $new_goods_list);

        $condition['goods_commend'] = 1;
        //得到12个推荐商品列表
        $recommended_goods_list = $model_goods->getGoodsListByColorDistinct($condition, $fieldstr, 'goods_id desc', '1,12');
        $this->assign('recommended_goods_list', $recommended_goods_list);

        //幻灯片图片
        $bannerImage = array();
        if ($this->store_info['store_slide'] != '' && $this->store_info['store_slide'] != ',,,,') {
            $store_slide = explode(',', $this->store_info['store_slide']);
            $store_slide_url = explode(',', $this->store_info['store_slide_url']);
            foreach ($store_slide as $key=>$val){
                $tmpl = C('TMPL_PARSE_STRING');
                $bannerImage[$key]['img'] = $tmpl['__HOST__'].$tmpl['__UPLOAD__'].'/shop/store/slide/'.$val;
                $bannerImage[$key]['url'] = $store_slide_url[$key];
            }
        }
        $this->assign('HomeSlide',$bannerImage);
		$this->display();
	}
	
	/*
	 * 普通店铺内搜索
	 */
	public function goodsSearch(){
        $goods_class = D('Goods');       
        
        $condition = array();
        $condition['store_id'] = intval($_GET['store_id']);
        if (trim($_GET['keyword']) != '') {
            $condition['goods_name'] = array('like', '%' . trim($_GET['keyword']) . '%');
        }

        // 排序
        $order = $_GET['order'] == 1 ? 'asc' : 'desc';
        switch (trim($_GET['key'])) {
            case '1':
                $order = 'goods_id ' . $order;
                break;
            case '2':
                $order = 'goods_price ' . $order;
                break;
            case '3':
                $order = 'goods_salenum ' . $order;
                break;
            case '4':
                $order = 'goods_collect ' . $order;
                break;
            case '5':
                $order = 'goods_click ' . $order;
                break;
            default:
                $order = 'goods_salenum desc,goods_addtime desc';
                break;
        }

        //如果存在gc_id表面用户查询某个分类下面的所有产品
        $gc_id = $_GET['gc_id'];//顶级分类id
        if (isset($gc_id) && !empty($gc_id)) {
            //获取用户的想要查询的gc_id
            $condition['gc_id'] = $gc_id;
        }
        
        $fieldstr = "goods_id,goods_commonid,goods_name,goods_jingle,brand_id,store_id,store_name,goods_price,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,color_id,evaluation_good_star,evaluation_count,goods_spec";
        
        $p = empty($_GET['p'])?1:$_GET['p'];
        $list = $goods_class->getGoodsListByColorDistinct($condition, $fieldstr ,$order,$p.',20');
        $count = $goods_class->getGoodsListByColorDistinct($condition, $fieldstr ,$order,0,1); //根据条件获取总条数
        $vendor_class = $goods_class->get_factory_class(0);
        
        $this->assign('list',$list);
        $this->assign('vendor_class',$vendor_class);
        $this->assign('page',  getPage($count,20));
		$this->display();
	}

    
    /***
     * 生成店铺导航数据
     */
    private function _setNavigation($store_id)
    {
        //获取所有navigation栏目
        $model_navigation = D('StoreNavigation');
        $n_conditon['sn_store_id'] = $store_id;
        $n_conditon['sn_gc_id'] = array('gt', 0);
        $navigation = $model_navigation->getStoreNavigationList($n_conditon);
        if (is_array($navigation)) {
            foreach ($navigation as $k => $v) {
                $gc_id = $v['sn_gc_id'];
                if (isset($gc_id) && !empty($gc_id)) {
                    //获取用户的想要查询的gc_id
                    $checkgc_ids = implode(',', $this->_fetchAllChildrenCateId($gc_id));
                    $check_condition['gc_id'] = array('in', $checkgc_ids);
                    //获取该分类下面是否存在商品
                    $model_goods = D('Goods');
                    $fieldstr = "goods_id";
                    $recommended_goods_list = $model_goods->getGoodsListByColorDistinct($check_condition, $fieldstr);
                    if (!is_array($recommended_goods_list) || empty($recommended_goods_list)) {
                        unset($navigation[$k]);
                    } else {
                    }
                }
            }
        }
        $this->assign('navigation', $navigation);
    }

    /****
     * 传入一级分类,查询所有子分类id返回子分类数组
     */
    private function _fetchAllChildrenCateId($gc_id){
        $k='';
        $classes=getClasses();
        $this->_getAllArrKey($classes,$gc_id,$k);
        $nk=unserialize($k)?:[];
        $nks=[];
        $this->_recursionArrKey($nk,$nks);
        return $nks;
    }

    /***递归数组,获取所有的key值*/
    private function _recursionArrKey($arr,&$nk){
        if(!is_array($arr)){
            return false;
        }
        foreach ($arr as $k=>$v){
            $nk[]=$k;
            if(is_array($v)){
                $this->_recursionArrKey($v,$nk);
            }
        }
    }

    /****
     * 获取指定key的数组的所有key值
     */
    private function _getAllArrKey($arr,$key,&$ks){
        if(!is_array($arr)){
            return false;
        }
       foreach ($arr as $k=>$v){
           if($k==$key){
               $ks=serialize($v);
           }
       }
    }
}