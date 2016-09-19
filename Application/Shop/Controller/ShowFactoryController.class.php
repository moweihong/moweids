<?php
/*
 * 工厂前端店铺首页
 */

namespace Shop\Controller;
use Think\Controller;
class ShowFactoryController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->checkFactoryFriend();
    }
	/*
	 *  工厂前端店铺首页
     */
	public function index()
	{
        $store_id = I('get.store_id');
        if(intval($store_id) === false){
            $this->error('找不到店铺');
        }
        $condition = array();
        $condition['store_id'] = intval($_GET['store_id']);
        $model_goods = D('Goods'); // 字段
        $fieldstr = "goods_id,goods_commonid,goods_name,goods_jingle,store_id,store_name,goods_price,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,color_id,evaluation_good_star,evaluation_count";
        $order=  'goods_salenum desc,goods_addtime desc';
        //得到最新商品列表
        $p = empty($_GET['p'])?1:$_GET['p'];
        $list = $model_goods->get_factory_goods($condition, $fieldstr,$order,$p.',10');
        $this->assign('list', $list);
        $count = $model_goods->get_factory_goods_count($condition);        
        
        $this->assign('page',  getPage($count));
		$this->display();
	}
    
    /*
     * 工厂商品详情页
     */
    public function goodsDetial()
    {
        $goods_id = intval($_GET ['goods_id']);
        // 商品详细信息
        $model_goods = D('Goods');
        $goods_detail = $model_goods->getGoodsDetail($goods_id, '*');
//        //检查是否是普通商品
//        Model('goods')->is_ordinary_goods($goods_detail);
        //推荐商品
        $field = 'goods_id,goods_commonid,store_id,goods_name,goods_jingle,goods_price,goods_image';
        $order = 'goods_salenum desc,goods_addtime desc';
        $goods_commend = $model_goods->get_factory_goods_commend(array('store_id'=>$goods_detail['goods_info']['store_id']),$field,$order,5);
        //是否上架了
        $is_up = false;
        $up_goods_record =M('up_goods_record')->where(array('dealer_store_id'=>$_SESSION['store_id'],'factory_goods_commonid'=>$goods_detail['goods_info']['goods_commonid']))->find();
        if(!empty($up_goods_record)){
            $is_up = true;
        }

        $this->assign('goods',$goods_detail['goods_info']);
        $this->assign('is_up',$is_up);
        $this->assign('store_info',$goods_detail['store_info']);
        $this->assign('goods_commend',$goods_commend);
        $this->assign('spec_list',$goods_detail['spec_list_mobile']);
        $this->assign('goods_image', $goods_detail['goods_image']);
        $this->display();
    }

    
    /*
     * 访问工厂首页  权限检查
     */
    public function checkFactoryFriend()
    {
       if(!isset($_GET['store_id'])||empty($_GET['store_id']))
            $this->error('数据错误');
         //未登录去登陆
        if(!isset($_SESSION['member_id']) || empty($_SESSION['member_id']))
            $this->redirect('login/index');
        
        // 验证店铺是否存在
        $store_info = M('Store')->where(array('store_id'=>intval($_GET['store_id'])))->find();
        if (empty($store_info)) {
            $this->error('工厂店铺不存在!');
        }

        // 店铺关闭标志
        if (intval($store_info['store_state']) === 0) {
            $this->error('工厂店铺已关闭!');
        }
        //权限
        if($_SESSION['store_id'] != intval($_GET['store_id'])){
            $factory_friend = Model()->table('allwood_factory_friend')->where(array('dealer_store_id'=>$_SESSION['store_id'], 'factory_store_id'=>intval($_GET['store_id'])))->find();
            if(empty($factory_friend) || (!empty($factory_friend)&&($factory_friend['is_look'] == 1||$factory_friend['is_del'] == 1)||$factory_friend['c_type']!=1)){
                //审核中 拒绝 删除的经销商 都没有访问权限
                $this->error('没有访问权限');
            }
        }
        //判断商品是否是自己 商店发布的 
        if($_SESSION['store_id'] == $_GET['store_id']){
            $this->assign('is_mygoods',1);
        }
        $this->assign('store_info',$store_info);
    }

    /*
     * 搜索工厂商品
     */
    public function goodsSearch()
    {
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
            $condition = $goods_class ->_getRecursiveClass($condition);
        }
        
        $fieldstr = "goods_id,goods_commonid,goods_name,goods_jingle,brand_id,store_id,store_name,goods_price,goods_marketprice,goods_storage,goods_image,goods_freight,goods_salenum,color_id,evaluation_good_star,evaluation_count,goods_spec";
        
        $p = empty($_GET['p'])?1:$_GET['p'];
        $list = $goods_class->get_factory_goods($condition, $fieldstr,$order ,$p.',10');
        $count = $goods_class->get_factory_goods_count($condition);
        $factory_class = $goods_class->get_factory_class();
        
        $this->assign('list',$list);
        $this->assign('factory_class',$factory_class);
        $this->assign('page',  getPage($count));

        $this->display();
        
    }
    

    /*
    * 经销商上架工厂商品到自己店铺
    */
   
    public function upGoods(){
        $goods_commonid = I('get.goods_commonid/d');

        if ($goods_commonid <= 0){
            $this->jsonFail('参数错误！');
        }

        $model = D('Goods');

        $common_array = M('GoodsCommon')->field('goods_commonid',true)->where(array('goods_commonid' => $goods_commonid))->find();

        if($common_array['store_id'] == session('store_id')){
            $this->jsonFail('不能上架自己的商品！');
        }
        $up_goods_record = M()->table(C('DB_PREFIX').'up_goods_record')->where(array('dealer_store_id' => session('store_id'), 'factory_goods_commonid' => $goods_commonid))->find();
        if(!empty($up_goods_record)){
            $this->jsonFail('您已经上架过此商品！');
        }

        $condition['goods_commonid'] = $goods_commonid;
        $condition['is_offline'] = 2;
        $goods = $model->where($condition)->field('goods_id',true)->select();
        $factory_store_id = $common_array['store_id'];

        try {
            $model->startTrans();
            $now = $_SERVER['REQUEST_TIME'];
            $common_array['store_id'] = session('store_id');
            $common_array['store_name'] = session('store_name');
            $common_array['goods_addtime'] = $now;
            $common_array['goods_selltime'] = $now;
            $common_array['tesu_offline_time'] = 0;
            $common_array['is_offline'] = 0;
            $common_id = $model->addGoods($common_array, 'goods_common');
            if(empty($common_id)){
                throw new Exception('goods_common add fail');
            }
            foreach($goods as $val){
                $goods_arr = $val;
                $goods_arr['goods_commonid'] = $common_id;
                $goods_arr['store_id'] = session('store_id');
                $goods_arr['store_name'] = session('store_name');
                $goods_arr['transport_id'] = 0;
                $goods_arr['goods_freight'] = 0;
                $goods_arr['goods_addtime'] = $now;
                $goods_arr['goods_edittime'] = $now;
                $goods_arr['tesu_offline_time'] = 0;
                $goods_arr['is_offline'] = 0;
                $goods_add = $model->addGoods($goods_arr);
                if(empty($goods_add)){
                    throw new Exception('goods add fail');
                }
            }
            //保存上架工厂商品到自己店铺记录表
            $data['dealer_store_id'] = session('store_id');
            $data['factory_store_id'] = $factory_store_id;
            $data['factory_goods_commonid'] = $goods_commonid;
            $data['dealer_goods_commonid'] = $common_id;
            $data['factory_goods_id'] = I('get.goods_id/d');
            $data['created_at'] = $now;
            $up_goods_record_add = M()->table(C('DB_PREFIX').'up_goods_record')->add($data);
            if(empty($up_goods_record_add)){
                throw new Exception('up_goods_record  add fail');
            }
            $model->commit();
            $this->jsonSucc();
        } catch (Exception $e) {
            $model->rollback();
            $this->jsonFail('上架失败！');
        }
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
        $result['message'] = '成功';
        $result['resultText'] = $msg_arr;
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