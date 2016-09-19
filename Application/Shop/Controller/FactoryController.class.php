<?php
/*
 * 卖家中心（工厂）
 */

namespace Shop\Controller;
use Shop\Controller\StoreCommonController;
class FactoryController extends StoreCommonController {
    
    public function index(){
        $model_order = D('Order');
        // 待发货
        $no_delivery = $model_order->getOrderStatePayCount(array('store_id' => $_SESSION['store_id']));
        //昨日下单数
        $yesterday_count = $model_order->getOrderYesterdayCount(array('store_id' => $_SESSION['store_id']));
        //昨天交易金额
        $yesterday_money = $model_order->getOrderYesterdayMoney(array('store_id' => $_SESSION['store_id'])) ?: 0;
        //查询店铺信息
        $store = D('Store');
        $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
        $store_info = $store->getStoreInfo($condition);
        // 单品销售排行Top5
        $goods_list = D('Goods')->getGoodsList(array('store_id' => $_SESSION['store_id']), 'goods_id,goods_name,goods_image,store_id,goods_salenum,goods_price,goods_storage', '', 'goods_salenum desc', 5);
        foreach ($goods_list as $key=>$val){
            $goods_list[$key]['goods_image'] = thumb($val,60);
        }
        $this->assign('no_delivery',$no_delivery);
        $this->assign('yesterday_count',$yesterday_count);
        $this->assign('yesterday_money',$yesterday_money);
        $this->assign('store_info',$store_info);
        $this->assign('goods_list',$goods_list);
        $this->display();
    }

    /*
     * 经销商管理
     */
    public function vendor(){
        //工厂
        if($_GET['type'] == 3){
            //是否需要通过审核
            $store = M('Store')->where(array('store_id'=>$_SESSION['store_id']))->find();
            $is_check = $store['dealer_verify'];
            $this->assign('is_check',$is_check);
        }else{
            $condition['factory_friend.factory_store_id'] = $_SESSION['store_id'];
            $condition['factory_friend.is_del'] = 0;
            $join = 'INNER JOIN allwood_factory_friend as factory_friend on store.store_id=factory_friend.dealer_store_id';
            $field = 'store.store_id, store.store_name, store.area_info, store.member_name, store.store_tel, factory_friend.id, factory_friend.is_look, factory_friend.c_type, factory_friend.created_at';

            if(!isset($_GET['type'])||$_GET['type'] == 1){
                //所有经销商
                $condition['factory_friend.c_type'] = 1;
            }else{
                //待审核
                $condition['factory_friend.c_type'] = array('in','0,2');
            }
            $curPage = empty($_GET['p']) ? 1 : intval($_GET['p']);
            $list = M()->table('allwood_store as store')->join($join)
                ->where($condition)->field($field)->page($curPage.',10')->select();
            $cout = M()->table('allwood_store as store')->join($join)->where($condition)->count();

            $this->assign('page',getPage($cout));
            $this->assign('list',$list);
        }
        $this->display();
    }
    
    /*
     *审核 
     */
    public function vendorCheck()
    {
        $this->check_post($_POST);
        $condition['id'] = $_POST['id'];
        $condition['factory_store_id'] = $_SESSION['store_id'];
        $result = M()->table('allwood_factory_friend')->where($condition)->find();
        if(!empty($result)){
            if($_POST['state'] == 1){
                //通过
                $rs = M()->table('allwood_factory_friend')->where($condition)->save(array('c_type'=>1,'pass_at'=>time()));
            }else{
                //拒绝
                $rs = M()->table('allwood_factory_friend')->where($condition)->save(array('c_type'=>2));
            }
            $this->jsonSucc();
            
        }else{
            $this->jsonFail('审核记录不存在');
        }
    }
    /*
     * 保存 所有经销商是否需通过审核之后才能成为本店经销商
     */
    public function  vendorSet()
    {
        $this->check_post($_POST);
        $condition['store_id'] = $_SESSION['store_id'];
        
        if($_POST['check_setting'] == 1){
            $result = M('Store')->where($condition)->save(array('dealer_verify'=>0));
        }else{
            $result = M('Store')->where($condition)->save(array('dealer_verify'=>1));
        }
        $this->jsonSucc();
    }
    /*
     * 设置 是否可以 访问工厂首页
     */
    public function vendorSetLook()
    {
        $this->check_post($_POST);
        $condition['factory_store_id'] = $_SESSION['store_id'];
        $condition['id'] = $_POST['id'];
        if($_POST['is_visit'] == 0){
            $result = M()->table('allwood_factory_friend')->where($condition)->save(array('is_look'=>0));
        }else{
            $result = M()->table('allwood_factory_friend')->where($condition)->save(array('is_look'=>1));
        }
        $this->jsonSucc();
    }

    /*
     * 经销商编辑页面
     */
    public function vendorEdit(){
        $condition['id'] = $_GET['id'];
        $condition['factory_store_id'] = $_SESSION['store_id'];
        $condition['is_del'] = 0;
        $condition['c_type'] = 1;
        $join = 'INNER JOIN allwood_factory_friend as factory_friend on store.store_id=factory_friend.dealer_store_id';
        $field = 'store.store_id,store.store_name,store.area_info,store.member_name,store.store_tel,factory_friend.id,factory_friend.is_look,factory_friend.c_type,factory_friend.created_at';
        $factory_friend = M()->table('allwood_store as store')->join($join)->field($field)->where($condition)->find();

        $this->assign('factory_friend', $factory_friend);
        $this->display();
    }
    
    /*
     * check post
     */
    public function check_post($post)
    {
        if(empty($post)){
           $this->jsonFail('参数错误');
        }
    }

    private function filterCondition(){
         $condition = array();
        $condition['store_id'] = $_SESSION['store_id'];
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
        //不是工厂订单
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

    /*
     * 工厂订单
     */
    public function factoryOrder(){
        $condition = array();
        $condition['o.store_id'] = session('store_id');
        if (I('get.order_num') != '') {
            $condition['o.order_sn'] = array("like", '%' . I('get.order_num') . '%');
        }
        //添加买家名称
        if (I('get.buyer_name') != '') {
            $condition['o.buyer_name'] = array('like', '%' . I('get.buyer_name') . '%');
        }
        //商品名称
        if (I('get.goods_name') != ''){
            $condition['og.goods_name'] = array('like', '%' . I('get.goods_name') . '%');
        }
        //添加订单状态

        if (I('get.order_state')) {
            $condition['o.order_state'] = I('get.order_state');
        }
        //工厂订单
        $condition['o.order_type'] = array('EQ', 4);
        //添加支付方式
        if (I('get.pay_way')) {
            switch (I('get.pay_way')) {
                case '1':
                    $condition['o.payment_code'] = 'alipay';
                    break;
                case '2':
                    $condition['o.payment_code'] = 'wxpay';
                    break;
                case '3':
                    $condition['o.payment_code'] = 'predeposit';
                    break;
                case '4':
                    $condition['o.payment_code'] = 'chinapay';
                    break;
                default:
            }
        }
        //追加时间条件
        if (I('get.start_add_time') != '' || I('get.end_add_time') != ''){
            $stime = I('get.start_add_time') != '' ? strtotime(I('get.start_add_time')) : 0;
            $etime = I('get.end_add_time') != '' ? strtotime(I('get.end_add_time')) : time();
            $condition['o.add_time'] = array('between', $stime . ',' . $etime);
        }


        //是否是投资购
        $condition['is_investpay'] = '0';

        $_SESSION['orders_search'] = $condition;
        $_SESSION['orders_search_join'] = false;

        $orderby = I('get.sortby') ? 'o.add_time '.I('get.sortby') : 'o.order_id desc';

        $model = M("Order");
        $count = $model->alias("o")->join(C('DB_PREFIX').'order_goods og ON o.order_id = og.order_id','LEFT')->where($condition)->count();

        $Page = new \Think\Page($count, 10);
        $field = array('og.goods_id,og.store_id,og.goods_name,og.goods_price,og.goods_num,o.add_time,o.order_state,o.order_amount,o.order_id,o.order_sn,o.payment_code,o.buyer_name,o.tesu_seller_remark');
        $order_list = $model->alias("o")->join(C('DB_PREFIX').'order_goods og ON o.order_id = og.order_id','LEFT')->where($condition)
            ->field($field)->order($orderby)->limit($Page->firstRow.','.$Page->listRows)->select();

        $show = $Page->show();// 分页显示输出

        $model_goods = D('goods');
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
            $order_list[$k]['goods_spec'] = $spec_str;
            $order_list[$k]['goods_image'] = $goods['goods_image'];

        }
        $this->assign('order_list', $order_list);
        $this->assign('sortby', I('get.sortby'));
        $this->assign('page', $show);
        $this->display('factoryorder');
    }

    /*
     * 发货
     */
    public function tplSend(){
        $order_id = I('get.order_id', 0, 'int');
        if ($order_id <= 0) {
            $this->error('订单不存在');
        }

        $order_info = $this->get_order_info($order_id, array('order_common'));
        if (empty($order_info)) {
            $this->error('订单不存在');
        }
        $this->assign('order_info', $order_info);

        $fields = 'id,e_name,e_code,e_letter,e_order,e_url';
        $list = M('express')->field($fields)->order('e_order,e_letter')->where(array('e_state' => 1))->select();
        if (!is_array($list)){
            $this->error('没有物流公司');
        }
        $express_list = array();
        foreach ($list as $k=>$v) {
            $express_list[$v['id']] = $v;
        }
        $this->assign('express_list', $express_list);
        $this->display();
    }

    /**
     * 保存发货信息
     * @return [type] [description]
     */
    public function save_express_info(){
        $order_id = I('post.order_id', 0, 'int');
        if ($order_id <= 0){
            $this->jsonFail('订单出错！');
        }

        $order_model = D('order');

        //检查是否是分批发货
        //商品个数为1，一定是打包发货
        $batch = I('post.batch');
        $condition['order_id'] = $order_id;
        $order_goods_record = M('order_goods')->where($condition)->select();

        $count = sizeof($order_goods_record);
        if ($count == 1){
            $batch = true;
        }

        $order_express_id = $batch ? I('batch_exprss_id') : "-1";
        $order_shipping_code = $batch ? I('batch_shippingcode') : "00000000";

        try {
            $order_model->startTrans();
            $data = array();

            //物流公司信息
            $data['shipping_express_id'] = $order_express_id;
            $data['shipping_time'] = $_SERVER['REQUEST_TIME'];

            $condition = array();
            $condition['order_id'] = $order_id;
            $condition['store_id'] = session('store_id');

            //更新订单
            $update = $order_model->editOrderCommon($data, $condition);
            if (!$update) {
                throw new Exception("save error", 1);
            }

            $condition['order_type'] = ORDER_TYPE_FACTORY;
            $data = array();
            $data['shipping_code'] = $order_shipping_code;
            $data['order_state'] = ORDER_STATE_SUCCESS;
            $data['delay_time'] = $_SERVER['REQUEST_TIME'];
            $update = $order_model->editOrder($data, $condition);

            foreach ($order_goods_record as $key => $value) {
                $data2['order_goods_state'] = ORDER_STATE_SEND;
                if ($batch) {
                    $data2['shipping_code'] = $order_shipping_code;
                    $data2['express_id'] = $order_express_id;
                } else {
                    //分开发送，从post参数中解析
                    $rec = $_POST['express'][$value['rec_id']];
                    list($exp_tmp, $code_tmp) = explode(',', $rec);
                    $data2['shipping_code'] = $code_tmp;
                    $data2['express_id'] = $exp_tmp;
                }
                $update_data = array_merge($value, $data2);
                M('order_goods')->save($update_data, array('rec_id' => $value));
            }
            //----------------------------------------------------------------------------------
            if (!$update) {
                throw new Exception(L('nc_common_save_fail'), 1);
            }
            $order_model->commit();
            $this->jsonSucc();
        } catch (Exception $e) {
            $order_model->rollback();
            $this->jsonFail($e->getMessage());
        }
    }

    /**
     * 工厂备注
     * @return [type] [description]
     */
    public function seller_remark(){
        $order_id = I('post.order_id', 0, 'int');
        $seller_remark = I('post.seller_remark/s');

        if ($order_id <= 0 || empty($seller_remark)) {
            $this->jsonFail('没有订单号或留言内容');
        } else {
            $model_order = D('order');
            $data['tesu_seller_remark'] = $seller_remark;
            $condition['order_id'] = $order_id;
            $status = $model_order->editOrder($data, $condition);
            if ($status != false) {
                $this->jsonSucc();
            } else {
                $this->jsonFail();
            }
        }
    }

    public function order_detail(){
        $order_id = I('get.order_id', 0, 'int');
        if ($order_id <= 0) {
            $this->error('订单错误');
        }

        $model_order = D('Order');
        $condition = array();
        $condition['order_id'] = $order_id;
        $order_info = $model_order->getOrderInfo($condition, array('order_common', 'order_goods', 'member'));
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
                $this->assign('order_log_info', $order_log[0]);
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

    /**
     * 修改收货地址
     * @return [type] [description]
     */
    public function modifyReciverInfo(){
        $order_id = I('get.order_id', 0, 'int');
        if ($order_id <= 0) {
            $this->error('订单不存在');
        }

        $order_info = $this->get_order_info($order_id, array('order_common'));
        if (empty($order_info)) {
            $this->error('订单不存在');
        }

        $this->assign('order_info', $order_info);
        $this->display();
    }

    /**
     * 修改收货地址
     * @return [type] [description]
     */
    public function saveReciverInfo(){
        $order_id = I('post.order_id', 0, 'int');
        if ($order_id <= 0) {
            $this->jsonFail('没有此订单');
        }

        //收货人信息
        $reciver_info = array();
        $reciver_info['address'] = I('post.area_info') . '&nbsp;' . I('post.address');
        $reciver_info['phone'] = I('post.mob_phone') . (I('post.tel_phone') ? ',' . I('post.tel_phone') : null);
        $reciver_info['zip_code'] = I('post.zip_code') ? I('post.zip_code') : null;
        $data['reciver_info'] = serialize($reciver_info);
        $data['reciver_name'] = I('post.reciver_name');
        $data['reciver_province_id'] = I('post.reciver_province_id');

        $result = D('order')->editOrderCommon($data, array('order_id' => $order_id));
        if (!$result) {
            $this->jsonFail("数据库写入失败");
        } else {
            $this->jsonSucc();
        }
    }
}