<?php
/*
 * 经销商控制器
 */

namespace Shop\Controller;
use Shop\Controller\StoreCommonController;
class VendorController extends StoreCommonController {

    /*
    * 经销商首页初始化
    */
    public function index(){
        //店铺信息
        $store = D('Store')->getStoreInfo(array('store_id'=>$_SESSION['store_id']));
        $Order = D('Order');
        // 待发货
        $no_delivery = $Order->getOrderStatePayCount(array('store_id' => $_SESSION['store_id']));
        //昨日下单数
        $yesterday_count = $Order->getOrderYesterdayCount(array('store_id' => $_SESSION['store_id']));
        //昨天交易金额
        $yesterday_money = $Order->getOrderYesterdayMoney(array('store_id' => $_SESSION['store_id'])) ?: 0;
        // 单品销售排行
        $goods_list = D('goods')->getGoodsList(array('store_id' =>  $_SESSION['store_id']), 'goods_id,goods_name,goods_image,store_id,goods_salenum,goods_price,goods_storage', '', 'goods_salenum desc', 5);
        $this->assign('store_info',$store);
        $this->assign('no_delivery',$no_delivery);
        $this->assign('yesterday_count',$yesterday_count);
        $this->assign('yesterday_money',$yesterday_money);
        $this->assign('goods_list',$goods_list);
        $this->display();
    }
    
    /*
     * 云端产品库
     */
    public function goodsInCloud(){
        
        $model = D('Store');
        if(isset($_GET['kword'])&&!empty($_GET['kword'])){
            $condition['store_name'] = array('like', '%' . $_GET['kword'] . '%');
        }
        //工厂
        $condition['com_type'] = 3;
        $order = 'store_time desc';
        $field = 'store_id,store_name,store_label,store_tel,store_qq,store_ww,store_description,dealer_verify';
        $curPage = empty($_GET['p']) ? 1 : intval($_GET['p']);
        $list = $model->getStoreOnlineList($condition, $curPage.',10', $order, $field);
        $cout = $model->where($condition)->count();
        foreach ($list as $key=>$val){
            $codition_fac['dealer_store_id'] = $_SESSION['store_id'];
            $codition_fac['factory_store_id'] = $val['store_id'];
            $codition_fac['is_del'] = 0;
            $list[$key]['friend'] = M()->table('allwood_factory_friend')->where($codition_fac)->find();
        }
        
        $this->assign('list',$list);
        $this->assign('page', getPage($cout,10));
        $this->display();
    }
    
    /*
     * 经销商给工厂发起 申请
     */
    public function dealerSendFactory(){
        if(empty($_POST)){
            $this->jsonFail('数据错误！');
        }
        $data['factory_store_id'] = intval($_POST['id']);
        $data['dealer_store_id'] = $_SESSION['store_id'];
        $data['is_del'] = 0;
        $factory = M()->table('allwood_factory_friend')->where($data)->find();
        if(empty($factory)){
            //首次申请
            $store = M('Store')->where(array('store_id'=>intval($_POST['id']),'store_state'=>1))->find();
            if(!empty($store)){
                if($store['dealer_verify'] == 1){
                    $data['created_at'] = time();
                    $data['c_type'] = 1;
                    $result = Model()->table('allwood_factory_friend')->add($data); 
                }else{
                    $data['created_at'] = time();
                    $result = M()->table('allwood_factory_friend')->add($data); 
                }
                if($result){
                    $this->jsonSucc();
                }else{
                    jsonFail('申请失败！');
                }
            }
        }else{            
            if($factory['c_type'] == 2){
                //被拒绝重新申请
                $store = M('Store')->where(array('store_id'=>intval($_POST['id']),'store_state'=>1))->find();
                if(!empty($store)){
                    $codition = $data;
                    if($store['dealer_verify'] == 1){
                        $data['created_at'] = time();
                        $data['c_type'] = 1;
                        $result = M()->table('allwood_factory_friend')->where($codition)->save($data); 
                    }else{
                        $data['created_at'] = time();
                        $data['c_type'] = 0;
                        $result = M()->table('allwood_factory_friend')->where($codition)->save($data);
                    }
                    $this->jsonSucc();
                }
            }else{
                $this->jsonFail('您已经申请过了！');
            }
        }        
    }
    
    /*
     * 供应商管理
     */
    public function supplier(){
        $condition['dealer_store_id'] = $_SESSION['store_id'];
        $condition['is_del'] = 0;
        if($_GET['type']==2){
            //申请记录
            $condition['c_type'] = array('in','0,2');
        }else{
            //我的供应商
            $condition['c_type'] = 1;
        }
        $join = 'INNER JOIN allwood_factory_friend as factory_friend on store.store_id=factory_friend.factory_store_id';        
        $field = 'store.store_id,store.store_name,store.area_info,store.member_name,store.store_tel,factory_friend.id,factory_friend.is_look,factory_friend.c_type,factory_friend.created_at';
        
        $curPage = empty($_GET['p']) ? 1 : intval($_GET['p']);
        $list = M()->table('allwood_store as store')->join($join)->field($field)->where($condition)->page($curPage.',10')->select();
        $cout = M()->table('allwood_store as store')->join($join)->where($condition)->count();
        
        $this->assign('list',$list);
        $this->assign('page',getPage($cout,10));

        $this->display();
    }

    private function filterCondition(){
         $condition = array();
        $condition['buyer_id'] = $_SESSION['store_id'];
        if ($_GET['order_num'] != '') {
            $condition['order_sn'] = array("like", '%' . $_GET['order_num'] . '%');
        }
        //添加买家名称
        if ($_GET['buyer_name'] != '') {
            $condition['buyer_name'] = array('like', '%' . $_GET['buyer_name'] . '%');
        }
        //添加订单状态
        if (isset($_GET['order_state'])) {
            if ($_GET['order_state'] == 1000) {
                showMessage('功能正在开发中!');
            }
            $condition['order_state'] = $_GET['order_state'];
        }
        //工厂订单
        $condition['order_type'] = array('EQ', 4);
        //添加支付方式
        if (isset($_GET['pay_way'])) {
            switch ($_GET['pay_way']) {
                case '1':
                    $condition['payment_code'] = 'alipay';
                    break;
                case '2':
                    $condition['payment_code'] = 'wxpay';
                    break;
                case '3':
                    $condition['payment_code'] = 'predeposit';
                    break;
                case '4':
                    $condition['payment_code'] = 'chinapay';
                    break;
                default:
            }
        }
        //追加时间条件
        if (isset($_GET['order_time1']) && isset($_GET['apply_time2'])) {
            $stime = strtotime($_GET['order_time1']);
            $etime = strtotime($_GET['apply_time2']);
            $condition['add_time'] = array('between', $stime . ',' . $etime);
        }
        //是否是投资购
        $condition['is_investpay'] = '0';
        return $condition;
    }

    public function supplierOrder(){
        //搜索
        $condition = array();
        $condition['o.buyer_id'] = session('store_id');
        $condition['o.order_type'] = ORDER_TYPE_FACTORY;
        //添加订单状态
        if (I('get.order_state')) {
            $condition['o.order_state'] = I('get.order_state');
        }

        $orderby = $_GET['sortby'] ? 'o.add_time '.$_GET['sortby'] : 'o.order_id desc';

        //商品名称
        $kword = I('get.kword');

        if ($kword){
            $condition['o.store_name|og.goods_name'] = array('like', '%' . $kword . '%');
        }

        $model = M("Order");
        $count = $model->alias("o")->join(C('DB_PREFIX').'order_goods og ON o.order_id = og.order_id','LEFT')->where($condition)->count();

        $Page = new \Think\Page($count, 10);
        $field = array('og.goods_id,og.store_id,og.goods_name,og.goods_price,og.goods_num,o.add_time,o.order_state,o.order_amount,o.order_id,o.order_sn,o.payment_code');
        $order_list = $model->alias("o")->join(C('DB_PREFIX').'order_goods og ON o.order_id = og.order_id','LEFT')->where($condition)
                    ->field($field)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();

        $show = $Page->show();// 分页显示输出

        $model_goods = D('goods');
        if (!empty($order_list)){
            foreach ($order_list as $k => $v) {
                //获取规格列表
                $goods_condition['goods_id'] = $v['goods_id'];
                $goods = $model_goods->getGoodsInfo($goods_condition, 'goods_spec,goods_image');
                $spec = unserialize($goods['goods_spec']) ?: [];
                $spec_str = '无';
                if (is_array($spec) && !empty($spec)) {
                    $spec_str = '';
                    foreach ($spec as $k3 => $v3) {
                        $spec_str .= $v3;
                    }
                    if (count($spec) > 1) {
                        $spec_str = substr($spec_str, 0, -1);
                    }
                }

                $order_list[$k]['payment_name'] = orderPaymentName($v['payment_code']);
                $order_list[$k]['goods_spec'] = $spec_str;
                $order_list[$k]['goods_image'] = $goods['goods_image'];
            }
        }
        
        $this->assign('order_list', $order_list);
        $this->assign('sortby', I('get.sortby'));
        $this->assign('kword', I('get.kword'));
        $this->assign('page', $show);
        $this->display();
    }

    public function order_detail(){
        $order_id = I('get.order_id', 0, 'int');
        if ($order_id <= 0) {
            $this->error('订单错误');
        }

        $model_order = D('Order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getOrderInfo($condition, array('order_common', 'order_goods', 'member', 'store'));
        if (empty($order_info)) {
            $this->error('订单不存在');
        }

        if (!empty($order_info['extend_order_goods'])){
            $model_goods = D('goods');
            foreach ($order_info['extend_order_goods'] as $k => $v) {
                //获取规格列表
                $goods_condition['goods_id'] = $v['goods_id'];
                $goods = $model_goods->getGoodsInfo($goods_condition, 'goods_spec,is_offline');
                $spec = unserialize($goods['goods_spec']) ?: [];
                $spec_str = '无';
                if (is_array($spec) && !empty($spec)) {
                    $spec_str = '';
                    foreach ($spec as $k3 => $v3) {
                        $spec_str .= $v3;
                    }
                    if (count($spec) > 1) {
                        $spec_str = substr($spec_str, 0, -1);
                    }
                }
                $order_info['extend_order_goods'][$k]['goods_spec'] = $spec_str;
                $order_info['extend_order_goods'][$k]['is_offline'] = $goods['is_offline'];
            }
        }

        $this->assign('order_info', $order_info);

        switch ($order_info['order_state']) {
            //标准订单状态
            case ORDER_STATE_MAKEPRICE:
                break;
            case ORDER_STATE_CANCEL:
                $order_log = $model_order->getOrderLogList(array('order_id' => $order_info['order_id'], 'log_orderstate' => '0'));
                $this->assign('order_log_info', end($order_log));
                break;
            case ORDER_STATE_NEW:
                break;
            case ORDER_STATE_PAY:
                break;
            case ORDER_STATE_SUCCESS:
                break;
            case ORDER_STATE_HANDLING:
                break;
            default:
                $this->error('404');
                break;
        }
        $this->display();
    }
}