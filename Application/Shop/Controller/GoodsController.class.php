<?php
/*
 * 商品详情页
 */
namespace Shop\Controller;
use Think\Controller;
class GoodsController extends Controller {

    public function __construct() {
        parent::__construct();
        
    }
	/**
     * 方法功能介绍
     * @access public
     * @param mixed $db_config 
     * @return string
     */
	public function index()
	{
       $goods_id = intval($_GET ['goods_id']);
        // 商品详细信息
        $model_goods = D('Goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id, '*');
      //  $model_goods->is_cloud_goods($goods_detail);
        $goods_info = $goods_detail['goods_info'];
        if (empty($goods_info)) {
            $this->error('商品已下架或不存在');
        }
        if(!empty($_SESSION['admin']['username'])){
            //后台可以跳转访问商品详情
        }else if($goods_detail['store_info']['com_type'] == COM_TYPE_FACTORY){
            $this->error('没有权限访问此商品！');
        }
        
        $store_info = M('Store')->where(array('store_id'=>$goods_info['store_id']))->find();
        $this->assign('store_info',$store_info);
        //设置店铺导航到商家首页
        $this->_setNavigation($goods_info['store_id']);
        
        if($goods_info['promotion_type']=="xianshi"){
            $tmp = $goods_info['promotion_price'];
        }else if($goods_info['promotion_type']=="groupbuy"){
            $tmp = $goods_info['promotion_price'];
        }else{
            $tmp = $goods_info['goods_price'];
        }
 
        
        $result['easypay'] = $this->_getEasypayInfo($tmp, $goods_info['store_id']);
        //返回easyapay
        //-1 没有额度，去充值
        //0 额度低于1000
        //额度大于1000
        //
        $this->assign('easypay', $result['easypay']);
        $this->assign('spec_list', $goods_detail['spec_list']);
        $this->assign('spec_image', $goods_detail['spec_image']);
        $this->assign('goods_image', $goods_detail['goods_image']);
        $this->assign("price", $tmp);
        $this->assign('image_share',cthumb($goods_info['goods_image']));
//        // 输出一级地区
//        $area_list = array(1 => '北京', 2 => '天津', 3 => '河北', 4 => '山西', 5 => '内蒙古', 6 => '辽宁', 7 => '吉林', 8 => '黑龙江', 9 => '上海',
//            10 => '江苏', 11 => '浙江', 12 => '安徽', 13 => '福建', 14 => '江西', 15 => '山东', 16 => '河南', 17 => '湖北', 18 => '湖南',
//            19 => '广东', 20 => '广西', 21 => '海南', 22 => '重庆', 23 => '四川', 24 => '贵州', 25 => '云南', 26 => '西藏', 27 => '陕西',
//            28 => '甘肃', 29 => '青海', 30 => '宁夏', 31 => '新疆', 32 => '台湾', 33 => '香港', 34 => '澳门', 35 => '海外'
//        );
//        if (strtoupper(CHARSET) == 'GBK') {
//            $area_list = Language::getGBK($area_list);
//        }
//        $this->assign('area_list', $area_list);
        // 生成缓存的键值
        $hash_key = $goods_info['goods_id'];
     
        // 先查找$hash_key缓存
        $cachekey_arr = array (
                'likenum',
                'sharenum'
        );
//        if ($_cache = rcache($hash_key, 'product')) {
//            foreach ($_cache as $k => $v) {
//                $goods_info[$k] = $v;
//            }
//        } else {
            // 查询SNS中该商品的信息
            $snsgoodsinfo = D('SnsGoods')->getSNSGoodsInfo(array('snsgoods_goodsid' => $goods_info['goods_id']), 'snsgoods_likenum,snsgoods_sharenum');
            $goods_info['likenum'] = $snsgoodsinfo['snsgoods_likenum'];
            $goods_info['sharenum'] = $snsgoodsinfo['snsgoods_sharenum'];
            
            $data = array();
            if (! empty ( $goods_info )) {
                foreach ( $goods_info as $k => $v ) {
                    if (in_array ( $k, $cachekey_arr )) {
                        $data [$k] = $v;
                    }
                }
            }
            // 缓存商品信息
            //wcache ( $hash_key, $data, 'product' );
        //}
        // 检查是否为店主本人
        $store_self = false;
        if (!empty($_SESSION['store_id'])) {
            if ($goods_info['store_id'] == $_SESSION['store_id']) {
                $store_self = true;
            }
        }
        $this->assign('store_self',$store_self );
        //通过goods_info['store_id']获取company_phone字段
        $store_joinin=D('StoreJoinin');
        $store_joinin_con['member_id']=$goods_info['store_id'];
        $company_phone=$store_joinin->getOne($store_joinin_con);
        $company_phone=$company_phone['company_phone'];
        $this->assign('company_phone',$company_phone);
        //获取用户的地址信息
        $add=getAddrByIp();
        if(isset($add['address'])){
            $st=explode('|',$add['address']);
            $goods_info['address']=$st[1];
        }
        // 如果使用运费模板
        if ($goods_info['transport_id'] > 0) {
            $model_transport = D('Transport');
            //计算运费
            $ks=array_search($goods_info['address'],$area_list);
            $res=$model_transport->_calcTransportFee($goods_id,$ks,1);
            $goods_info['transport_fee']=$res;
        }
        $goodsCInfo=M('GoodsCommon')->where(array('goods_commonid'=>$goods_info['goods_commonid']))->find();
        $goods_info['gcommon_is_offline']=$goodsCInfo['is_offline'];
        $this->assign('goods', $goods_info);
        // 关联版式
        $plateid_array = array();
        if (!empty($goods_info['plateid_top'])) {
            $plateid_array[] = $goods_info['plateid_top'];
        }
        if (!empty($goods_info['plateid_bottom'])) {
            $plateid_array[] = $goods_info['plateid_bottom'];
        }
        if (!empty($plateid_array)) {
            $plate_array = D('StorePlate')->getPlateList(array('plate_id' => array('in', $plateid_array), 'store_id' => $goods_info['store_id']));
            $plate_array = array_under_reset($plate_array, 'plate_position', 2);
            $this->assign('plate_array', $plate_array);
        }
        $this->assign('store_id', $goods_info ['store_id']);
        // 生成浏览过产品
        $cookievalue = $goods_id . '-' . $goods_info ['store_id'];
        if (cookie('viewed_goods')) {
            $string_viewed_goods = decrypt(cookie('viewed_goods'), MD5_KEY);
            if (get_magic_quotes_gpc()) {
                $string_viewed_goods = stripslashes($string_viewed_goods); // 去除斜杠
            }
            $vg_ca = @unserialize($string_viewed_goods);
            $sign = true;
            if ( !empty($vg_ca) && is_array($vg_ca)) {
                if($vg_ca[0] == $cookievalue)
                    $sign = false;
            } else {
                $vg_ca = array();
            }
            
            if ($sign) {
                if (count($vg_ca) >= 12) {
                    $vg_ca[] = $cookievalue;
                    array_shift($vg_ca);
                } else {
                    $vg_ca[] = $cookievalue;
                }
            }
        } else {
            $vg_ca[] = $cookievalue;
        }
        $vg_ca = encrypt(serialize($vg_ca), MD5_KEY);
        cookie('viewed_goods', $vg_ca);
        //优先得到推荐商品
        $goods_commend_list = $model_goods->getGoodsOnlineList(array('store_id' => $goods_info['store_id'], 'goods_commend' => 1), 'goods_id,goods_name,goods_jingle,goods_image,store_id,goods_price', 0, 'rand()', 5, 'goods_commonid');
        $this->assign('goods_commend',$goods_commend_list);
        // 当前位置导航
//        $nav_link_list = M('GoodsClass')->getGoodsClassNav($goods_info['gc_id'], 0);
//        $nav_link_list[] = array('title' => $goods_info['goods_name']);
//        $this->assign('nav_link_list', $nav_link_list );
        //评价信息
        $goods_evaluate_info = D('EvaluateGoods')->getEvaluateGoodsInfoByGoodsID($goods_id);
        $this->assign('goods_evaluate_info', $goods_evaluate_info);
       
//        $seo_param = array ();
//        $seo_param['name'] = $goods_info['goods_name'];
//        $seo_param['key'] = $goods_info['goods_keywords'];
//        $seo_param['description'] = $goods_info['goods_description'];
//        Model('seo')->type('product')->param($seo_param)->show();
        
        $this->assign('goods_id', $goods_id);
		$this->display();
	}
    
   /****
     * 商品预览功能
     */
    public function preview(){
        if(isset($_GET['randkey']) && !empty($_GET['randkey'])){
            //如果存randkey,从session中取出数据显示到前端
            $data=S($_GET['randkey']);
            if(!$data){
                $this->error('预览商品已过期！');
            }
            // 输出一级地区
//            $area_list = array(1 => '北京', 2 => '天津', 3 => '河北', 4 => '山西', 5 => '内蒙古', 6 => '辽宁', 7 => '吉林', 8 => '黑龙江', 9 => '上海',
//                10 => '江苏', 11 => '浙江', 12 => '安徽', 13 => '福建', 14 => '江西', 15 => '山东', 16 => '河南', 17 => '湖北', 18 => '湖南',
//                19 => '广东', 20 => '广西', 21 => '海南', 22 => '重庆', 23 => '四川', 24 => '贵州', 25 => '云南', 26 => '西藏', 27 => '陕西',
//                28 => '甘肃', 29 => '青海', 30 => '宁夏', 31 => '新疆', 32 => '台湾', 33 => '香港', 34 => '澳门', 35 => '海外'
//            );
//            if (strtoupper(CHARSET) == 'GBK') {
//                $area_list = Language::getGBK($area_list);
//            }
            foreach ($data['goods_images'] as $k=>$v){
                $image=explode('/',$v['m_pic']);
                $image=$image[count($image)-1];
                $goods_image[] = "{ title : '', levelA : '" . cthumb($image, 60) . "', levelB : '" . cthumb($image, 360) . "', levelC : '" . cthumb($image, 360) . "', levelD : '" . cthumb($image, 1280) . "'}";
            }
//            $this->assign('area_list', $area_list);
            $this->assign('goods_image',$goods_image);
            //获取商品的基础属性
            $goods_info['goods_name']=$data['g_name'];
            $goods_info['goods_jingle']=$data['g_jingle'];
            $goods_info['spec_name']=$data['sp_name'];
            $goods_info['brand_id']=$data['b_id'];
            $goods_info['brand_name']=$data['b_name'];
            $goods_info['goods_attr']=$data['attr'];
            $goods_info['goods_body']=htmlspecialchars_decode($data['editorValue']);
            $goods_info['goods_state']=1;
            $goods_info['goods_verify']=1;
            $goods_info['goods_price']=$data['g_price'];
            $goods_info['goods_marketprice']=$data['g_marketprice'];
            $goods_info['goods_storage']=$data['g_storage'];
            $goods_info['goods_serial']=$data['g_serial'];
            $goods_info['spec_name']=$data['sp_name'];
            $goods_info['spec_value']=$data['sp_val'];

            $this->assign('goods', $goods_info);
            $this->assign('goods_evaluate_info', array('good'=>'0','normal'=>'0','bad'=>'0','all'=>'0','good_percent'=>'100','normal_percent'=>'0','bad_percent'=>'0','goods_star'=>'5'));
            //评价信息
//            $goods_evaluate_info = D('EvaluateGoods')->getEvaluateGoodsInfoByGoodsID(0);
//            $this->assign('goods_evaluate_info', $goods_evaluate_info);
//            
//            $seo_param = array ();
//            $seo_param['name'] = $goods_info['goods_name'];
//            $seo_param['key'] = $goods_info['goods_keywords'];
//            $seo_param['description'] = $goods_info['goods_description'];
//            Model('seo')->type('product')->param($seo_param)->show();
//
//            $seo_param = array ();
//            $seo_param['name'] = $goods_info['goods_name'];
//            $seo_param['key'] = $goods_info['goods_keywords'];
//            $seo_param['description'] = $goods_info['goods_description'];
//            Model('seo')->type('product')->param($seo_param)->show();
            // 检查是否为店主本人
            $store_self = false;
            if (!empty($_SESSION['store_id'])) {
                if ($goods_info['store_id'] == $_SESSION['store_id']) {
                    $store_self = true;
                }
            }
            $this->assign('store_self',$store_self );
            //通过goods_info['store_id']获取company_phone字段
            $store_joinin=D('StoreJoinin');
            $store_joinin_con['member_id']=$goods_info['store_id'];
            $company_phone=$store_joinin->getOne($store_joinin_con);
            $company_phone=$company_phone['company_phone'];
            $this->assign('company_phone',$company_phone);


            $model_store = D('Store');
            $store_info = $model_store->getStoreOnlineInfoByID($_SESSION['store_id']);
            if (empty($store_info)) {
                $this->error('店铺已关闭');
            } else {
                $this->assign('store_info',$store_info);
            }
            $this->display();
        }
        $randkey=rand(1,99999);
        S($randkey,$_POST,300);
        $_SESSION['preview_goods'][$randkey]=($_POST);
        $this->jsonArr(array('randkey'=>$randkey));
    }
    
    /**
    *获取分期购信息
    */
    private function _getEasypayInfo($amount, $store_id){
        //检查是否开启
        if(is_null(C('easybuy_status'))||(C('easybuy_status') == 0))
            return array();
        
        $pay_by_period = unserialize(C('pay_by_period'));
        $pay_by_period2 = $pay_by_period;
        $qishu = implode(';', array_keys($pay_by_period));
        $rate = array_values($pay_by_period);
        $rate2 = $rate;
        foreach ($rate as $key => $value) {
            $rate[$key] = number_format($value/100,3,'.','');
        }
        $rate = implode(';', $rate);

        //计算分期费用
        $easypay_api = API('easypay');
        $options['amount']        = $amount;
        //1 tie  0 bu tie 
        $tiexi = $this->_is_tiexi($store_id);
        $options['interest_type'] = $tiexi;
        $options['periods']       = $qishu;
        $options['interest_rate'] = $rate;
        $options['factorage']     = C('easypay_charge_by_ccfax')/ 100;
        $result = json_decode($easypay_api->get_interest_info($options), true);
        if($result['code'] == 0){
            $pay_by_period = $result['return_param']['interests_list'];
        }

        //封装利息参数
        foreach ($pay_by_period as $key => $value) {
            $pay_by_period[$key]['factorage_rate'] = $options['factorage'];
            $pay_by_period[$key]['interest_rate'] = $pay_by_period2[$value['period']];
        }
        return $pay_by_period;
    }
	
	/*
	 * 云端商品详情页
	 */
	public function goodsInCloud(){
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
    
    /**
     * 判断是否贴息
     * @param  [type]  $store_id [description]
     * @return 0    不贴息
     *         1    贴息
     *         -1  错误
     */
    public function _is_tiexi($store_id){
        //判断商家免息还是不免息
        $store_model = D('Store');
        $storeinfo = $store_model->getStoreInfoByID($store_id);
         return $storeinfo['is_discount'] == 1 ? 1 : 0;
    }
    
    /*
    * 输出json 成功信息
     */
    function jsonSucc($msg='成功！'){
        $result['code'] = 1;
        $result['resultText']['message'] = $msg;
        $this->ajaxReturn($result);
    }
    
    /*
    * 输出json 数组信息
     */
    function jsonArr($msg_arr=array()){
        $result['code'] = 1;
        $result['resultText']['data'] = $msg_arr;
        $result['resultText']['message'] = '成功';
        $this->ajaxReturn($result);
    }
    
    /*
    * 输出json 错误信息
     */
    function jsonFail($msg){
        $result['code'] = 0;
        $result['resultText']['message'] = $msg;
        $this->ajaxReturn($result);
    }
    
}