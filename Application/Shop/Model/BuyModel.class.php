<?php
/**
 * 下单业务模型
 *




 */
namespace Shop\Model;
use Think\Model;
class BuyModel extends Model {
    protected $tableName = 'order';//因为没有buy数据表

    /**
     * 输出有货到付款时，在线支付和货到付款及每种支付下商品数量和详细列表
     * @param $buy_list 商品列表
     * @return 返回 以支付方式为下标分组的商品列表
     */
    public function getOfflineGoodsPay($buy_list) {
        //以支付方式为下标，存放购买商品
        $buy_goods_list = array();        
        $offline_pay = Model('payment')->getPaymentOpenInfo(array('payment_code'=>'offline'));
        if ($offline_pay) {
            //下单里包括平台自营商品并且平台已开户货到付款，则显示货到付款项及对应商品数量,取出支持货到付款的店铺ID组成的数组，目前就一个，DEFAULT_PLATFORM_STORE_ID
            $offline_store_id_array = array(DEFAULT_PLATFORM_STORE_ID);
            foreach ($buy_list as $value) {
                //if (in_array($value['store_id'],$offline_store_id_array)) {
                    $buy_goods_list['offline'][] = $value;
                //} else {
                    //$buy_goods_list['online'][] = $value;
                //}
            }//print_r($buy_goods_list);exit;
        }
        return $buy_goods_list;
    }

    /**
     * 计算每个店铺(所有店铺级优惠活动)总共优惠多少金额
     * @param array $store_goods_total 最初店铺商品总金额 
     * @param array $store_final_goods_total 去除各种店铺级促销后，最终店铺商品总金额(不含运费)
     * @return array
     */
    public function getStorePromotionTotal($store_goods_total, $store_final_goods_total) {
        if (!is_array($store_goods_total) || !is_array($store_final_goods_total)) return array();
        $store_promotion_total = array();
        foreach ($store_goods_total as $store_id => $goods_total) {
            $store_promotion_total[$store_id] = abs($goods_total - $store_final_goods_total[$store_id]);
        }
        return $store_promotion_total;
    }

    /**
     * 返回需要计算运费的店铺ID组成的数组 和 免运费店铺ID及免运费下限金额描述
     *
     * 满额免运费
     * store_free_price  0  不免运费
     * store_free_price <> 0 满额免运费
     * @param array $store_goods_total 每个店铺的商品金额小计，以店铺ID为下标
     * @return array
     */
    public function getStoreFreightDescList($store_goods_total) {
        if (empty($store_goods_total) || !is_array($store_goods_total)) return array(array(),array());

        //定义返回数组
        $need_calc_sid_array = array();
        $cancel_calc_sid_array = array();

        //如果商品金额未达到免运费设置下线，则需要计算运费
        $condition = array('store_id' => array('in',array_keys($store_goods_total)));
        $store_list = D('Store')->getStoreOnlineList($condition,null,'','store_id,store_free_price');
        foreach ($store_list as $store_info) {
            $limit_price = floatval($store_info['store_free_price']);
            if ($limit_price == 0 || $limit_price > $store_goods_total[$store_info['store_id']]) {
                //需要计算运费
                $need_calc_sid_array[] = $store_info['store_id'];
            } else {
                //返回免运费金额下限
                $cancel_calc_sid_array[$store_info['store_id']]['free_price'] = $limit_price;
                $cancel_calc_sid_array[$store_info['store_id']]['desc'] = sprintf('满%s免运费',$limit_price);
            }
        }
        return array($need_calc_sid_array,$cancel_calc_sid_array);
    }

    /**
     * 取得店铺运费(使用运费模板的商品运费不会计算，但会返回模板信息)
     * 先将免运费的店铺运费置0，然后算出店铺里没使用运费模板的商品运费之和 ，存到iscalced下标中
     * 然后再计算使用运费模板的信息(array(店铺ID=>array(运费模板ID=>购买数量))，放到nocalced下标里
     * 
     * ps:分为三层概念
     * ①在免运费列表中的商品不需要计算韵味
     * ②绑定商品需要做第二次处理，计算运费
     * ③不在①②之列的，根据transportid
     * @param array $buy_list 购买商品列表
     * @param array $free_freight_sid_list 免运费的店铺ID数组
     */
    public function getStoreFreightList($buy_list = array(), $free_freight_sid_list, $city_id=null) {
        $tmp = $buy_list;
        //定义返回数组
        $return = array();

        //先将免运费的店铺运费置0(格式:店铺ID=>0)
        
        $freight_list = array();
        if (!empty($free_freight_sid_list) && is_array($free_freight_sid_list)) {
            foreach ($free_freight_sid_list as $store_id) {
                $freight_list[$store_id] = 0;
            }
        }

        //然后算出店铺里没使用运费模板(优惠套装商品除外)的商品运费之和(格式:店铺ID=>运费)
        //定义数组，存放店铺优惠套装商品运费总额 store_id=>运费
        //
        //不计算运费的商品条件：
        //满金额免运费
        //不参与活动
        //没有设置运费模板的
    
        $store_bl_goods_freight = array();
        foreach ($buy_list as $key => $goods_info) {
            
            //免运费店铺的商品不需要计算
            if (in_array($goods_info['store_id'], $free_freight_sid_list)) {
                unset($buy_list[$key]);
            
                //免运费
                $tmpgoods[$goods_info['goods_id']]['transporttype'] = -1;
                $tmpgoods[$goods_info['goods_id']]['freight'] = 0;
            }
            //优惠套装商品运费另算
            if (intval($goods_info['bl_id'])) {
                unset($buy_list[$key]);
                $store_bl_goods_freight[$goods_info['store_id']] = $goods_info['bl_id'];
                continue;
            }
            if (!intval($goods_info['transport_id']) &&  !in_array($goods_info['store_id'],$free_freight_sid_list)) {
                $freight_list[$goods_info['store_id']] += $goods_info['goods_freight'];
                unset($buy_list[$key]);

                //固定运费
                $tmpgoods[$goods_info['goods_id']]['transporttype'] = -1;
                $tmpgoods[$goods_info['goods_id']]['freight'] = $goods_info['goods_freight'];
            }
        }
        
        //计算优惠套装商品运费
        
        if (!empty($store_bl_goods_freight)) {
            $model_bl = Model('p_bundling');
            foreach (array_unique($store_bl_goods_freight) as $store_id => $bl_id) {
                $bl_info = $model_bl->getBundlingInfo(array('bl_id'=>$bl_id));
                if (!empty($bl_info)) {
                    $freight_list[$store_id] += $bl_info['bl_freight'];

                    //兼容大宗商品
                    //TODO
                }
            }
        }
        
        //合并freight_list 和 freight_list1 
        $return['iscalced'] = $freight_list;

        //最后再计算使用运费模板的信息(店铺ID，运费模板ID，购买数量),使用使用相同运费模板的商品数量累加
        
        $freight_list = array();

        $model_transport = Model('transport');
        foreach ($buy_list as $goods_info) {
            $freight_list[$goods_info['store_id']][$goods_info['transport_id']] += $goods_info['goods_num'];
            
            //获得模板个数
            //0 0
            //单物流  2
            //单快递  1
            //物流和快递  3
           
            $fie = "transport_extend.transport_type";
            $res = Model('goods')->table('goods,transport,transport_extend')->on('goods.transport_id=transport.id,transport.id=transport_extend.transport_id')->field($fie)->where(array('goods_id'=>$goods_info['goods_id']))->group('transport_type')->select();
            if(sizeof($res) == 0){
                $tmpgoods[$goods_info['goods_id']]['transporttype'] = 0;
            }else if(sizeof($res) == 1){
                $tmpgoods[$goods_info['goods_id']]['transporttype'] = $res[0]['transport_type'];
                if($res[0]['transport_type'] == 1){
                    $tmpgoods[$goods_info['goods_id']][1] = $model_transport->_calcTransportFeeTotal($goods_info['goods_id'],$city_id,$goods_info['goods_num'],1);
                }else{
                    $tmpgoods[$goods_info['goods_id']][2] = $model_transport->_calcTransportFeeTotal($goods_info['goods_id'],$city_id,$goods_info['goods_num'],2);
                }
                //计算运费
            }else if(sizeof($res) == 2){
                $tmpgoods[$goods_info['goods_id']]['transporttype'] = 3;
                //计算运费
                //计算物流费用
               
                $tmpgoods[$goods_info['goods_id']][1] = $model_transport->_calcTransportFeeTotal($goods_info['goods_id'],$city_id,$goods_info['goods_num'],1);
                //计算快递费用
                $tmpgoods[$goods_info['goods_id']][2] = $model_transport->_calcTransportFeeTotal($goods_info['goods_id'],$city_id,$goods_info['goods_num'],2);
                //设置默认
                $tmpgoods[$goods_info['goods_id']]['default_template'] = 1;
            }
            
        }

        $return['nocalced'] = $freight_list;
        $return['tmpgoods'] = $tmpgoods;

        return $return;
    }

    /**
     * 根据地区选择计算出所有店铺最终运费
     * @param array $freight_list 运费信息(店铺ID，运费，运费模板ID，购买数量)
     * @param int $city_id 市级ID
     * @return array 返回店铺ID=>运费
     */
    public function calcStoreFreight($freight_list, $city_id) {
		if (!is_array($freight_list) || empty($freight_list) || empty($city_id)) return;

		//免费和固定运费计算结果
		$return_list = $freight_list['iscalced'];

		//使用运费模板的信息(array(店铺ID=>array(运费模板ID=>购买数量))
		$nocalced_list = $freight_list['nocalced'];

		//然后计算使用运费运费模板的在该$city_id时的运费值
		if (!empty($nocalced_list) && is_array($nocalced_list)) {
		    //如果有商品使用的运费模板，先计算这些商品的运费总金额
            $model_transport = Model('transport');
            foreach ($nocalced_list as $store_id => $value) {
                if (is_array($value)) {
                    foreach ($value as $transport_id => $buy_num) {
                        $freight_total = $model_transport->calc_transport($transport_id,$buy_num, $city_id);
                        if (empty($return_list[$store_id])) {
                            $return_list[$store_id] = $freight_total;
                        } else {
                            $return_list[$store_id] += $freight_total;
                        }
                    }
                }
            }
		}

		return $return_list;
    }

    /**
     * 根据地区选择计算出所有店铺最终运费
     * @param array $freight_list 运费信息(店铺ID，运费，运费模板ID，购买数量)
     * @param int $city_id 市级ID
     * @return array 返回店铺ID=>运费
     *
     * $is_buynow  区分立即购买和添加购物车购买  1 立即购买 0 购物车购买
     */
    public function calcStoreFreightNew($freight_list, $city_id, $is_buynow) {
        if (!is_array($freight_list) || empty($freight_list) || empty($city_id)) return;

        //免费和固定运费计算结果
        $return_list = $freight_list['iscalced'];

        //使用运费模板的信息(array(店铺ID=>array(运费模板ID=>购买数量))
        $nocalced_list = $freight_list['nocalced'];

        //运送类型 1.物流 ,2.快递
        $transport_type=$_REQUEST['transport_type'];
//        $buynow_transport_type = $_REQUEST['buynow_transport_type'];
        $buynow_transport_type = $_REQUEST['buynow_transport_type'];
        $if_cart = $_REQUEST['if_cart'];

        //获取商品信息
        $goodsinfo=json_decode(_htmtodecode($_REQUEST['goodsinfo']),true)?:[];
        //然后计算使用运费运费模板的在该$city_id时的运费值
        if (!empty($nocalced_list) && is_array($nocalced_list)) {
            //如果有商品使用的运费模板，先计算这些商品的运费总金额
            $model_transport = Model('transport');
            foreach ($goodsinfo as $store_id=>$v){
                foreach ($v  as $k2=>$v2){
                    //循环该店铺中的商品
                    
                    //过滤非重算商品
                    if(!in_array($v2['store_id'], array_keys($nocalced_list))){
                        continue;
                    }

                    //区分立即购买和加入购物车购买
                    //$is_buynow
                    if($buynow_transport_type){
                        $transport_type = $_REQUEST['buynow_transport_type'];
                    }else{

                        //从cart表中取transporttype 字段
                        $condition['buyer_id'] = $_SESSION['member_id'];
                        $condition['goods_id'] = $v2['goods_id'];
                        $res =  Model('cart')->where($condition)->find();
                        
                        if(!is_null($res)){
                            $transport_type = $res['transport_type'];
                        }
                    }
                    
                    $money=$model_transport->_calcTransportFeeTotal($v2['goods_id'],$city_id,$v2['goods_num'],$transport_type);
                    if(intval($money)!== false){
                        $return_list[$store_id]+=$money;
                    }
                }
            }
        }

        return $return_list;
    }

    /**
     * 立即购买，根据传入的cityid frieght hash buynow_transport_type 计算运费
     * @return [type] [description]
     */
    public function _calcTransportFee_buynow(){
         if (!is_array($freight_list) || empty($freight_list) || empty($city_id)) return;

        //免费和固定运费计算结果
        $return_list = $freight_list['iscalced'];

        //使用运费模板的信息(array(店铺ID=>array(运费模板ID=>购买数量))
        $nocalced_list = $freight_list['nocalced'];

        //运送类型 1.物流 ,2.快递
        $transport_type=$_REQUEST['transport_type'];
//        $buynow_transport_type = $_REQUEST['buynow_transport_type'];
        $buynow_transport_type = $_REQUEST['buynow_transport_type'];
        $if_cart = $_REQUEST['if_cart'];
        

        //获取商品信息
        $goodsinfo=json_decode(_htmtodecode($_REQUEST['goodsinfo']),true)?:[];
       
        //然后计算使用运费运费模板的在该$city_id时的运费值
        if (!empty($nocalced_list) && is_array($nocalced_list)) {
            //如果有商品使用的运费模板，先计算这些商品的运费总金额
            $model_transport = Model('transport');
            foreach ($goodsinfo as $store_id=>$v){
                //此处的goodsinfo是否有问题，运费计算是一个复杂过程，
                //需要考虑满送，满免，活动以及运费模板
                //
                //所以在进入结算页面之前
                //必须对需要将需要重新计算的商品列表筛选出来。
                //
                //ps:目前没有活动，可以暂时不用管上述顾虑
                //
                
                //如果在需要计算的列表中，继续，否则，跳到下一个循环
                
                //$return_list[$store_id] = 0;
                //循环每一个店铺
                foreach ($v  as $k2=>$v2){
                    //循环该店铺中的商品
                    
                    //过滤非重算商品
                    if(!in_array($v2['store_id'], array_keys($nocalced_list))){
                        continue;
                    }

                    //从cart表中取transporttype 字段
                    $condition['buyer_id'] = $_SESSION['member_id'];
                    $condition['goods_id'] = $v2['goods_id'];
                    $res =  Model('cart')->where($condition)->find();
                    //$transport_type = $freight_list['tmpgoods'][$v2['goods_id']]['default_template'];
                    if(!is_null($res)){
                        $transport_type = $res['transport_type'];
                    }


                  
                    if(is_null($if_cart)){
                        //立即购买
                    //    $transport_type = $buynow_transport_type;
                    }
                 
                    $money=$model_transport->_calcTransportFeeTotal($v2['goods_id'],$city_id,$v2['goods_num'],$transport_type);
                    if($money){
                        $return_list[$store_id]+=$money;
                    }
                }
            }
        }

        
        return $return_list;
    }


    /**
     * 取得店铺下商品分类佣金比例
     * @param array $goods_list
     * @return array 店铺ID=>array(分类ID=>佣金比例)
     */
    public function getStoreGcidCommisRateList($goods_list) {
        if (empty($goods_list) || !is_array($goods_list)) return array();

        //定义返回数组
        $store_gc_id_commis_rate = array();

        //取得每个店铺下有哪些商品分类
        $store_gc_id_list = array();
        foreach ($goods_list as $goods) {
            if (!intval($goods['gc_id'])) continue;
            if (!in_array($goods['gc_id'],(array)$store_gc_id_list[$goods['store_id']])) {
                if (in_array($goods['store_id'],array(DEFAULT_PLATFORM_STORE_ID))) {
                    //平台店铺佣金为0
                    $store_gc_id_commis_rate[$goods['store_id']][$goods['gc_id']] = 0;
                } else {
                    $store_gc_id_list[$goods['store_id']][] = $goods['gc_id'];
                }
            }
        }

        if (empty($store_gc_id_list)) return array();

        $model_bind_class = Model('store_bind_class');
        $condition = array();
        foreach ($store_gc_id_list as $store_id => $gc_id_list) {
            $condition['store_id'] = $store_id;
            $condition['class_1|class_2|class_3'] = array('in',$gc_id_list);
            $bind_list = $model_bind_class->getStoreBindClassList($condition);
            if (!empty($bind_list) && is_array($bind_list)) {
                foreach ($bind_list as $bind_info) {
                    if ($bind_info['store_id'] != $store_id) continue;
                    //如果class_1,2,3有一个字段值匹配，就有效
                    $bind_class = array($bind_info['class_3'],$bind_info['class_2'],$bind_info['class_1']);
                    foreach ($gc_id_list as $gc_id) {
                        if (in_array($gc_id,$bind_class)) {
                            $store_gc_id_commis_rate[$store_id][$gc_id] = $bind_info['commis_rate'];
                        }
                    }
                }
            }
        }
        return $store_gc_id_commis_rate;

    }

    /**
     * 更新购买数量
     * @param array $store_cart_list 购买列表
     */
    public function appendPremiumsToCartList($store_cart_list) {
        if (empty($store_cart_list)) return array();

        //取得每种商品的库存
        $goods_storage_quantity = $this->_getEachGoodsStorageQuantity($store_cart_list);

        //取得每种商品的购买量
        $goods_buy_quantity = $this->_getEachGoodsBuyQuantity($store_cart_list);

        foreach ($goods_buy_quantity as $goods_id => $quantity) {
            $goods_storage_quantity[$goods_id] -= $quantity;
            if ($goods_storage_quantity[$goods_id] < 0) {
                return array('error' => '抱歉，您购买的商品库存不足，请重购买'); 
            }
        }

        return array($store_cart_list,$goods_buy_quantity);
    }

    /**
     * 取得每种商品的库存
     * @param array $store_cart_list 购买列表
     * @param array $store_premiums_list 赠品列表
     * @return array 商品ID=>库存
     */
    private function _getEachGoodsStorageQuantity($store_cart_list) {
        if(empty($store_cart_list) || !is_array($store_cart_list)) return array();
        $goods_storage_quangity = array();
        foreach ($store_cart_list as $store_cart) {
            foreach ($store_cart as $cart_info) {
                //正常商品
                $goods_storage_quangity[$cart_info['goods_id']] = $cart_info['goods_storage'];
            }
        }

        return $goods_storage_quangity;
    }

    /**
     * 取得每种商品的购买量
     * @param array $store_cart_list 购买列表
     * @return array 商品ID=>购买数量
     */
    private function _getEachGoodsBuyQuantity($store_cart_list) {
        if(empty($store_cart_list) || !is_array($store_cart_list)) return array();
        $goods_buy_quangity = array();
        foreach ($store_cart_list as $store_cart) {
            foreach ($store_cart as $cart_info) {
                //正常商品
                $goods_buy_quangity[$cart_info['goods_id']] += $cart_info['goods_num'];

            }
        }
        return $goods_buy_quangity;
    }

    /**
     * 生成订单
     * @param array $input
     * @throws Exception
     * @return array array(支付单sn,订单列表)
     */
    public function createOrder($input, $member_id, $member_name, $member_email) {
        extract($input);
        $model_order = D('Order');
        //存储生成的订单,函数会返回该数组
        $order_list = array();

        //每个店铺订单是货到付款还是线上支付,店铺ID=>付款方式[在线支付/货到付款]
//        $store_pay_type_list = $this->_getStorePayTypeList(array_keys($store_cart_list), $if_offpay, $pay_name);

        $pay_sn = $this->makePaySn($member_id);
        $order_pay = array();
        $order_pay['pay_sn'] = $pay_sn;
        $order_pay['buyer_id'] = $member_id;
        $order_pay_id = $model_order->addOrderPay($order_pay);
        if (!$order_pay_id) {
            throw new Exception('订单保存失败');
        }

        //收货人信息
        $reciver_info = array();
        $reciver_info['address'] = $address_info['area_info'].'&nbsp;'.$address_info['address'];
        $reciver_info['phone'] = $address_info['mob_phone'].(trim($address_info['tel_phone']) != '-' ? ','.$address_info['tel_phone'] : null);
        $reciver_info = serialize($reciver_info);
        $reciver_name = $address_info['true_name'];

        foreach ($store_cart_list as $store_id => $goods_list) {
            $order = array();
            $order_common = array();
            $order_goods = array();

            $order['order_sn'] = $this->makeOrderSn($order_pay_id);
            $order['pay_sn'] = $pay_sn;
            $order['store_id'] = $store_id;
            $order['store_name'] = $goods_list[0]['store_name'];
            $order['buyer_id'] = $member_id;
            $order['buyer_name'] = $member_name;
            $order['buyer_email'] = $member_email;
            $order['add_time'] = TIMESTAMP;
//            $order['payment_code'] = $store_pay_type_list[$store_id];
            /*if($store_pay_type_list[$store_id] == 'online'){
                $order['order_state'] = $store_pay_type_list[$store_id] == 'online' ? ORDER_STATE_NEW : ORDER_STATE_PAY;    
            }else if($store_pay_type_list[$store_id] == 'easypay'){
                //用户取消pay_name是easypay 但是分期信息为空，
                //是一个bug
                $order['order_state'] = ORDER_STATE_NEW;    
                $order['payment_code'] = 'online';
            }*/
            $order['payment_code'] = 'online';
            $order['order_state'] = 1;//待定价
            
            $order['order_amount'] = $store_final_order_total[$store_id];
            $order['goods_amount'] = $order['order_amount'];
            $order['order_from'] = 1;
            $order['order_charity'] = "0";
	        $order['add_time'] = time();

            $order_id = $model_order->addOrder($order);
            if (!$order_id) {
                throw new Exception('订单保存失败');
            }
            $order['order_id'] = $order_id;
            $order_list[$order_id] = $order;

            $order_common['order_id'] = $order_id;
            $order_common['store_id'] = $store_id;
            $order_common['order_message'] = $pay_message;

            $order_common['reciver_info']= $reciver_info;
            $order_common['reciver_name'] = $reciver_name;
            $order_common['reciver_address_id'] = $reciver_address_id;

            //取得省ID
            require_once(BASE_DATA_PATH.'/area/area.php');
            $order_common['reciver_province_id'] = intval($area_array[$input_city_id]['area_parent_id']);
            $order_id = $model_order->addOrderCommon($order_common);
            if (!$order_id) {
                throw new Exception('订单保存失败');
            }

            //生成order_goods订单商品数据
            $i = 0;
	        $order_goods = array();
            foreach ($goods_list as $goods_info) {
                if (!$goods_info['state'] || !$goods_info['storage_state']) {
                    throw new Exception('部分商品已经下架或库存不足，请重新选择');
                }

                $order_goods[$i]['order_id'] = intval($order_id);
                $order_goods[$i]['goods_id'] = intval($goods_info['goods_id']);
                $order_goods[$i]['store_id'] = intval($store_id);
                $order_goods[$i]['goods_name'] = $goods_info['goods_name'];
                $order_goods[$i]['goods_price'] = floatval($goods_info['goods_price']);
                $order_goods[$i]['goods_num'] = intval($goods_info['goods_num']);
                $order_goods[$i]['goods_image'] = $goods_info['goods_image'];
	            $order_goods[$i]['goods_pay_price'] = floatval($goods_info['goods_price']);
                $order_goods[$i]['buyer_id'] = intval($member_id);
	            $order_goods[$i]['goods_type'] = 1;

                $order_goods[$i]['promotions_id'] = $goods_info['promotions_id'] ? $goods_info['promotions_id'] : 0;
//                $order_goods[$i]['commis_rate'] = floatval($store_gc_id_commis_rate_list[$store_id][$goods_info['gc_id']]);
                //计算商品金额
                $goods_total = $goods_info['goods_price'] * $goods_info['goods_num'];
                $i++;
            }

            $insert = $model_order->addOrderGoods($order_goods);
            if (!$insert) {
                throw new Exception('订单保存失败');
            }
        }
        return array($pay_sn,$order_list);
    }

    /**
     * 创建分期购订单
     * @param array $input
     * @throws Exception
     * @return array array(支付单sn,订单列表)
     */
    public function createEasypayOrder($input, $member_id, $member_name, $member_email) {
        extract($input);
        $model_order = Model('order');
        //存储生成的订单,函数会返回该数组
        $order_list = array();

        //每个店铺订单是货到付款还是线上支付,店铺ID=>付款方式[在线支付/货到付款]
        $store_pay_type_list    = $this->_getStorePayTypeList(array_keys($store_cart_list), $if_offpay, $pay_name);

        $pay_sn = $this->makePaySn($member_id);
        $order_pay = array();
        $order_pay['pay_sn'] = $pay_sn;
        $order_pay['buyer_id'] = $member_id;
        $order_pay_id = $model_order->addOrderPay($order_pay);
        if (!$order_pay_id) {
            exit(json_encode(array('code' => 0, 'resultText' => array('message'=>"订单保存失败"))));
            //throw new Exception('订单保存失败');
        }

        //收货人信息
        $reciver_info = array();
        $reciver_info['address'] = $address_info['area_info'].'&nbsp;'.$address_info['address'];
        $reciver_info['phone'] = $address_info['mob_phone'].($address_info['tel_phone'] ? ','.$address_info['tel_phone'] : null);
        $reciver_info = serialize($reciver_info);
        $reciver_name = $address_info['true_name'];

        /*
        *循环购物车，封装普通订单和
        *分期购订单
         */
        if(!is_null($_POST['easypay_storestore_list'])){
            //重新封装easypay_store_list
            $_POST['easypay_store_list'] = $this->restore_easypay_parameter($_POST['easypay_storestore_list']);
        }

        foreach ($store_cart_list as $store_id => $goods_list) {

            //取得本店优惠额度(后面用来计算每件商品实际支付金额，结算需要)
            $promotion_total = !empty($store_promotion_total[$store_id]) ? $store_promotion_total[$store_id] : 0; 

            //本店总的优惠比例,保留3位小数
            $should_goods_total = $store_final_order_total[$store_id]-$store_freight_total[$store_id]+$promotion_total;
            $promotion_rate = abs($promotion_total/$should_goods_total);
            if ($promotion_rate <= 1) {
                $promotion_rate = floatval(substr($promotion_rate,0,5));
            } else {
                $promotion_rate = 0;
            }

            //每种商品的优惠金额累加保存入 $promotion_sum
            $promotion_sum = 0;

            $order = array();
            $order_common = array();
            $order_goods = array();
            
            $order['order_sn']           = $this->makeOrderSn($order_pay_id);
            $order['pay_sn']             = $pay_sn;
            $order['store_id']           = $store_id;
            $order['store_name']         = $goods_list[0]['store_name'];
            $order['buyer_id']           = $member_id;
            $order['buyer_name']         = $member_name;
            $order['buyer_email']        = $member_email;
            $order['add_time']           = TIMESTAMP;
            $order['payment_time']       = TIMESTAMP;
            $order['payment_code']       = $store_pay_type_list[$store_id];
            $order['order_amount']       = $store_final_order_total[$store_id];
            $order['shipping_fee']       = $store_freight_total[$store_id];
            $order['goods_amount']       = $order['order_amount'] - $order['shipping_fee'];
            $order['order_from']         = 1;
            $order['order_charity']      = "0";
            //标记是否是投资购订单
            $order['is_investpay']       = $_POST['investpay']?1:0;

            //兼容分期购思路：引入变量 easypay_only 区分不同订单和分期购订单，如果是分期购订单，需要
            //通过另外三个参数 easypay_store_list ,amount_list,qishu_list 来区分各商家的分期购状态
            //同时，对订单状态的分析逻辑也需要重新设计
            //存入分期购信息
            //订单类型 分期购订单
            if(array_key_exists($store_id, $_POST['easypay_store_list'])){
                $easypay_info = $_POST['easypay_store_list'][$store_id];
                log::i(' easypay info =------------------------------------- '.json_encode($easypay_info), 'api');
                //订单类型
                $order['order_type']         = ORDER_TYPE_EASYPAY;
                //参与分期购的金额（不含利息 手续费)
                $order['order_amount_delta'] = $easypay_info['order_amount_delta'];
                //期数
                $order['period']             = $easypay_info["period"];
                //分期年化利率
                $order['interest_rate']      = $easypay_info['interest_rate'];
                //平均到每一期的手续费
                $order['factorage']          = $easypay_info['factorage'];
                //总利息
                $order['interest_total']     = $easypay_info['interest_total'];
                //购销合同编号
                $order['gxht_code']          = $this->makesn_for_easypay('GXHT');
                //发标协议编号
                $order['fbxy_code']          = $this->makesn_for_easypay('FBXY');
                //借款协议编号
                $order['jkxy_code']          = $this->makesn_for_easypay('JKXY');
            }
            
            //设置订单状态
            //根据支付类型无法兼容分期购订单，
            if($store_pay_type_list[$store_id] == 'online'){
                $order['order_state'] = ORDER_STATE_NEW;
            }else if($store_pay_type_list[$store_id] == 'easypay'){
                if($order['order_amount_delta'] == 0){
                    $order['order_state'] = ORDER_STATE_PAY;
                }else{
                    $order['order_state'] = ORDER_STATE_NEW;
                }
            }
            $order['order_state'] = 1;//待定价

            //设置订单状态--分期购
            if(array_key_exists($store_id, $_POST['easypay_store_list'])){
                if($order['order_amount'] == $order['order_amount_delta']){
                    $order['order_state'] = ORDER_STATE_PAY;
                }else if($order['order_amount'] > $order['order_amount_delta']){
                    $order['order_state'] = ORDER_STATE_NEW;
                }else{
                    $order['order_state'] = ORDER_STATE_NEW;
                }
            }
            $order_id = $model_order->addOrder($order);
            if (!$order_id) {
                exit(json_encode(array('code' => 0, 'resultText' => array('message'=>"订单保存失败"))));
                //throw new Exception('订单保存失败');
            }

            $order['order_id'] = $order_id;
            $order_list[$order_id] = $order;

            $order_common['order_id'] = $order_id;
            $order_common['store_id'] = $store_id;
            $order_common['order_message'] = $pay_message[$store_id];

            //代金券
            if (isset($voucher_list[$store_id])){
                $order_common['voucher_price'] = $voucher_list[$store_id]['voucher_price'];
                $order_common['voucher_code'] = $voucher_list[$store_id]['voucher_code'];
            }

            $order_common['reciver_info']= $reciver_info;
            $order_common['reciver_name'] = $reciver_name;

            //发票信息
            $order_common['invoice_info'] = $this->_createInvoiceData($invoice_info);

            //保存促销信息
            if(is_array($store_mansong_rule_list[$store_id])) {
                $order_common['promotion_info'] = addslashes($store_mansong_rule_list[$store_id]['desc']);
            }

            //取得省ID
            require_once(BASE_DATA_PATH.'/area/area.php');
            $order_common['reciver_province_id'] = intval($area_array[$input_city_id]['area_parent_id']);
            $order_id = $model_order->addOrderCommon($order_common);
            if (!$order_id) {
                exit(json_encode(array('code' => 0, 'resultText' => array('message'=>"订单保存失败"))));
                //throw new Exception('订单保存失败');
            }

            //生成order_goods订单商品数据
            $i = 0;
            foreach ($goods_list as $goods_info) {
                if (!$goods_info['state'] || !$goods_info['storage_state']) {
                    throw new Exception('部分商品已经下架或库存不足，请重新选择');
                }
                if (!intval($goods_info['bl_id'])) {
                    //如果不是优惠套装
                    $order_goods[$i]['order_id'] = $order_id;
                    $order_goods[$i]['goods_id'] = $goods_info['goods_id'];
                    $order_goods[$i]['store_id'] = $store_id;
                    $order_goods[$i]['goods_name'] = $goods_info['goods_name'];
                    $order_goods[$i]['goods_price'] = $goods_info['goods_price'];
                    $order_goods[$i]['goods_num'] = $goods_info['goods_num'];
                    $order_goods[$i]['goods_image'] = $goods_info['goods_image'];
                    $order_goods[$i]['buyer_id'] = $member_id;
                    if ($goods_info['ifgroupbuy']) {
                        $order_goods[$i]['goods_type'] = 2;
                    }elseif ($goods_info['ifxianshi']) {
                        $order_goods[$i]['goods_type'] = 3;
                    }elseif ($goods_info['ifzengpin']) {
                        $order_goods[$i]['goods_type'] = 5;
                    }else {
                        $order_goods[$i]['goods_type'] = 1;
                    }
                    $order_goods[$i]['promotions_id'] = $goods_info['promotions_id'] ? $goods_info['promotions_id'] : 0;
                    $order_goods[$i]['commis_rate'] = floatval($store_gc_id_commis_rate_list[$store_id][$goods_info['gc_id']]);
                    //计算商品金额
                    $goods_total = $goods_info['goods_price'] * $goods_info['goods_num'];
                    //计算本件商品优惠金额
                    $promotion_value = floor($goods_total*($promotion_rate));
                    $order_goods[$i]['goods_pay_price'] = $goods_total - $promotion_value;
                    //传入运送方式
                    $order_goods[$i]['transport_type'] = $_POST['buynow_transport_type'];
                    $promotion_sum += $promotion_value;
                    $i++;

                } elseif (!empty($goods_info['bl_goods_list']) && is_array($goods_info['bl_goods_list'])) {

                    //优惠套装
                    foreach ($goods_info['bl_goods_list'] as $bl_goods_info) {
                        $order_goods[$i]['order_id'] = $order_id;
                        $order_goods[$i]['goods_id'] = $bl_goods_info['goods_id'];
                        $order_goods[$i]['store_id'] = $store_id;
                        $order_goods[$i]['goods_name'] = $bl_goods_info['goods_name'];
                        $order_goods[$i]['goods_price'] = $bl_goods_info['bl_goods_price'];
                        $order_goods[$i]['goods_num'] = $goods_info['goods_num'];
                        $order_goods[$i]['goods_image'] = $bl_goods_info['goods_image'];
                        $order_goods[$i]['buyer_id'] = $member_id;
                        $order_goods[$i]['goods_type'] = 4;
                        $order_goods[$i]['promotions_id'] = $bl_goods_info['bl_id'];
                        $order_goods[$i]['commis_rate'] = floatval($store_gc_id_commis_rate_list[$store_id][$goods_info['gc_id']]);

                        //计算商品实际支付金额(goods_price减去分摊优惠金额后的值)
                        $goods_total = $bl_goods_info['bl_goods_price'] * $goods_info['goods_num'];
                        //计算本件商品优惠金额
                        $promotion_value = floor($goods_total*($promotion_rate));
                        $order_goods[$i]['goods_pay_price'] = $goods_total - $promotion_value;
                        //传入运送方式
                        $order_goods[$i]['transport_type'] = $_POST['buynow_transport_type'];
                        $promotion_sum += $promotion_value;
                        $i++;
                    }
                }
            }

            //将因舍出小数部分出现的差值补到最后一个商品的实际成交价中(商品goods_price=0时不给补，可能是赠品)
            if ($promotion_total > $promotion_sum) {
                $i--;
                for($i;$i>=0;$i--) {
                    if (floatval($order_goods[$i]['goods_price']) > 0) {
                        $order_goods[$i]['goods_pay_price'] -= $promotion_total - $promotion_sum;
                        break;
                    }
                }
            }
            $insert = $model_order->addOrderGoods($order_goods);
            if (!$insert) {
                exit(json_encode(array('code' => 0, 'resultText' => array('message'=>"订单保存失败"))));
                //throw new Exception('订单保存失败');
            }
        }
        return array($pay_sn,$order_list);
    }

    public function makesn_for_easypay($key){
        $order_model = D('Order');
        $condition = array();
        if ($key == 'GXHT'){
            $field = 'gxht_code';
        }elseif ($key == 'FBXY'){
            $field = 'fbxy_code';
        }elseif ($key == 'JKXY'){
            $field = 'jkxy_code';
        }
        $condition[$field] = array('like', '%' . $key.date('ymd') . '%');
        $today_record = $order_model->getOrderInfo($condition, array(), $field, $field.' desc');
        if (!empty($today_record)){
            $num = sprintf("%04d", substr($today_record[$field], -4, 4) + 1);
        }else{
            $num = '0001';
        }

        return $key.date("ymd").$num;
    }

    /*
    *重新封装参数，添加索引和
    * 手续费
     */
    private function restore_easypay_parameter($easypay_store_list){
        if(is_null($easypay_store_list))
            return array();

        $tmp_arr = array();
        $pay_by_period = H('setting');
        $pay_by_period = $pay_by_period['pay_by_period'];
        $pay_by_period = unserialize($pay_by_period);
        foreach ($easypay_store_list as  $value) {
            $tmp_arr[$value['store_id']] = $value;
            $tmp_arr[$value['store_id']]['interest_rate'] = $pay_by_period[$value['period']];
        }
        return $tmp_arr;
    }
    /**
     * 记录订单日志
     * @param array $order_list
     */
    public function addOrderLog($order_list = array()) {
        if (empty($order_list) || !is_array($order_list)) return;
        $model_order = D('Order');
        foreach ($order_list as $order_id => $order) {
            $data = array();
            $data['order_id'] = $order_id;
            $data['log_role'] = 'buyer';
            $data['log_msg'] = L('order_log_new');
            $data['log_orderstate'] = $order['payment_code'] == 'offline' ? ORDER_STATE_PAY : ORDER_STATE_NEW;
            $model_order->addOrderLog($data);
        }
    }

    /**
     * 店铺购买列表
     * @param array $goods_buy_quantity 商品ID与购买数量数组
     * @throws Exception
     */
    public function updateGoodsStorageNum($goods_buy_quantity) {
        if (empty($goods_buy_quantity) || !is_array($goods_buy_quantity)) return;
        $model_goods = D('Goods');
        foreach ($goods_buy_quantity as $goods_id => $quantity) {
            $data = array();
            $data['goods_storage'] = array('exp','goods_storage-'.$quantity);
            $data['goods_salenum'] = array('exp','goods_salenum+'.$quantity);
            $result = $model_goods->editGoods($data,array('goods_id'=>$goods_id));
            if (!$result) throw new Exception('更新库存失败');
        }
    }

    /**
     * 更新使用的代金券状态
     * @param $input_voucher_list
     * @throws Exception
     */
    public function updateVoucher($voucher_list) {
        if (empty($voucher_list) || !is_array($voucher_list)) return;
        $model_voucher = Model('voucher');
        foreach ($voucher_list as $store_id => $voucher_info) {
            $update = $model_voucher->editVoucher(array('voucher_state'=>2),array('voucher_id'=>$voucher_info['voucher_id']));
            if (!$update) throw new Exception('代金券更新失败');
        }
    }

    /**
     * 更新团购信息
     * @param unknown $groupbuy_info
     * @throws Exception
     */
    public function updateGroupbuy($groupbuy_info) {
        if (empty($groupbuy_info) || !is_array($groupbuy_info)) return;
        $model_groupbuy = Model('groupbuy');
        $data = array();
        $data['buyer_count'] = array('exp','buyer_count+1');
        $data['buy_quantity'] = array('exp','buy_quantity+'.$groupbuy_info['quantity']);
        $update = $model_groupbuy->editGroupbuy($data,array('groupbuy_id'=>$groupbuy_info['groupbuy_id']));
        if (!$update) throw new Exception('团购信息更新失败');
    }

    /**
     * 预存款支付,依次循环每个订单
     * 如果预存款足够就单独支付了该订单，如果不足就暂时冻结，等API支付成功了再彻底扣除
     */
    public function pdPay($order_list, $input, $member_id, $member_name) {
        if (empty($input['pd_pay']) || empty($input['password'])) return;

        $model_payment = Model('payment');
        $bill_history = Model('bill_history');
        $pd_payment_info = $model_payment->getPaymentOpenInfo(array('payment_code'=>'predeposit'));
        if (empty($pd_payment_info)) return;

        $buyer_info	= Model('member')->infoMember(array('member_id' => $member_id));

        if ($buyer_info['member_paypwd'] != md5($input['password'])) return ;
        $available_pd_amount = floatval($buyer_info['available_predeposit']);
        if ($available_pd_amount <= 0) return;

        $model_order = Model('order');
        $model_pd = Model('predeposit');
        foreach ($order_list as $order_info) {

            //货到付款的订单跳过
            if ($order_info['payment_code'] == 'offline') continue;

            $order_amount = floatval($order_info['order_amount']);
            $order_amount_delta = floatval($order_info['order_amount_delta']);
            //兼容分期购，如果是分期购订单，通过余额支付或者在线支付的金额 = order_amount - order_amount_delta
            if($order_info['order_type'] == ORDER_TYPE_EASYPAY){
                $order_amount -= $order_amount_delta;
            }
            $data_pd = array();
            $data_pd['member_id'] = $member_id;
            $data_pd['member_name'] = $member_name;
            $data_pd['amount'] = $order_amount;
            $data_pd['order_sn'] = $order_info['order_sn'];
            $data_pd['cishan'] = $_POST['cishan'];

            if ($available_pd_amount >= $order_amount) {
                //预存款立即支付，订单支付完成
                $model_pd->changePd('order_pay',$data_pd);
                $available_pd_amount -= $order_amount;

                //记录订单日志(已付款)
                $data = array();
                $data['order_id'] = $order_info['order_id'];
                $data['log_role'] = 'buyer';
                $data['log_msg'] = L('order_log_pay');
                $data['log_orderstate'] = ORDER_STATE_PAY;
                $insert = $model_order->addOrderLog($data);
                if (!$insert) {
                    throw new Exception('记录订单日志出现错误');
                }

                //订单状态 置为已支付
                $data_order = array();
                $data_order['order_state'] = ORDER_STATE_PAY;
                $data_order['payment_time'] = TIMESTAMP;
                $data_order['payment_code'] = 'predeposit';
                $data_order['pd_amount'] = $order_amount;
                $result = $model_order->editOrder($data_order,array('order_id'=>$order_info['order_id']));
                if (!$result) {
                    throw new Exception('订单更新失败');
                }else{
                    //更新预订单状态
                    Model('offline_provisional_order')->editOfflineOrder(array('is_deal'=>1),array('provisional_order_id'=>array('in',$input['order_id_str'])));
                    //更新预订单下商品状态
                    Model('offline_provisional_order_goods')->editOfflineOrderGoods(array('status'=>1),array('provisional_order_id'=>array('in',$input['order_id_str']),
                        'goods_id'=>array('in',$input['goods_id_str'])));
                    //wch 2016-05-10 添加账户明细记录
                    $con['order_id'] = $order_info['order_id'];
                    $field = 'goods_id,goods_name,goods_pay_price';
                    $order_goods_info = $model_order->getOrderGoodsList($con, $field);

                    if (!empty($order_goods_info)){
                        foreach ($order_goods_info as $goodsinfo) {
                            $addData = array();
                            $addData['member_id'] = $_SESSION['member_id'];
                            $addData['order_id'] = $order_info['order_id'];
                            $addData['goods_id'] = $goodsinfo['goods_id'];
                            $addData['add_time'] = TIMESTAMP;
                            $addData['amount'] = $goodsinfo['goods_pay_price'];
                            $addData['bill_state'] = 2;
                            $addData['remark'] = '商品名称：'.$goodsinfo['goods_name'];

                            $bill_history->addHistory($addData);
                        }
                    }
                    //设置分销信息
                    $goodsshow= Factory::ccfaxFactory("GoodsShow");
                    $goodsshow->setOrder(array("order.order_id"=>$order_info['order_id']));
                }

            } else {
                //暂冻结预存款,后面还需要 API彻底完成支付
                if ($available_pd_amount > 0) {
                    $data_pd['amount'] = $available_pd_amount;
                    $model_pd->changePd('order_freeze',$data_pd);
                    //预存款支付金额保存到订单
                    $data_order = array();
                    $data_order['pd_amount'] = $available_pd_amount;
                    $result = $model_order->editOrder($data_order,array('order_id'=>$order_info['order_id']));
                    $available_pd_amount = 0;
                    if (!$result) {
                        throw new Exception('订单更新失败');
                    }
                }
            }
        }
    }

    /**
     * 整理发票信息
     * @param array $invoice_info 发票信息数组
     * @return string
     */
    private function _createInvoiceData($invoice_info){
        //发票信息
        $inv = array();
        if ($invoice_info['inv_state'] == 1) {
            $inv['类型'] = '普通发票 ';
            $inv['抬头'] = $invoice_info['inv_title_select'] == 'person' ? '个人' : $invoice_info['inv_title'];
            $inv['内容'] = $invoice_info['inv_content'];
        } elseif (!empty($invoice_info)) {
            $inv['单位名称'] = $invoice_info['inv_company'];
            $inv['纳税人识别号'] = $invoice_info['inv_code'];
            $inv['注册地址'] = $invoice_info['inv_reg_addr'];
            $inv['注册电话'] = $invoice_info['inv_reg_phone'];
            $inv['开户银行'] = $invoice_info['inv_reg_bname'];
            $inv['银行帐户'] = $invoice_info['inv_reg_baccount'];
            $inv['收票人姓名'] = $invoice_info['inv_rec_name'];
            $inv['收票人手机号'] = $invoice_info['inv_rec_mobphone'];
            $inv['收票人省份'] = $invoice_info['inv_rec_province'];
            $inv['送票地址'] = $invoice_info['inv_goto_addr'];
        }
        return !empty($inv) ? serialize($inv) : serialize(array());        
    }

    /**
     * 计算本次下单中每个店铺订单是货到付款还是线上支付,店铺ID=>付款方式[online在线支付offline货到付款]
     * @param array $store_id_array 店铺ID数组
     * @param boolean $if_offpay 是否支持货到付款 true/false
     * @param string $pay_name 付款方式 online/offline
     * @return array
     */
    private function _getStorePayTypeList($store_id_array, $if_offpay, $pay_name) {
        $store_pay_type_list = array();
        if($_POST['pay_name'] == 'easypay'){
            foreach ($store_id_array as $store_id) {
                $store_pay_type_list[$store_id] = 'easypay';
            }
        }else if ($_POST['pay_name'] == 'online') {
            foreach ($store_id_array as $store_id) {
                $store_pay_type_list[$store_id] = 'online';
            }
        } else {
            $offline_pay = Model('payment')->getPaymentOpenInfo(array('payment_code'=>'offline'));
            if ($offline_pay) {
                //下单里包括平台自营商品并且平台已开启货到付款
                $offline_store_id_array = array(DEFAULT_PLATFORM_STORE_ID);
                foreach ($store_id_array as $store_id) {
                    // if (in_array($store_id,$offline_store_id_array)) {
                        $store_pay_type_list[$store_id] = 'offline';
                    // } else {
                        // $store_pay_type_list[$store_id] = 'online';
                    // }
                }
            }
        }
        return $store_pay_type_list;
    }

	/**
	 * 生成支付单编号(两位随机 + 从2000-01-01 00:00:00 到现在的秒数+微秒+会员ID%1000)，该值会传给第三方支付接口
	 * 长度 =2位 + 10位 + 3位 + 3位  = 18位
	 * 1000个会员同一微秒提订单，重复机率为1/100
	 * @return string
	 */
	public function makePaySn($member_id) {
		return mt_rand(10,99)
		      . sprintf('%010d',time() - 946656000)
		      . sprintf('%03d', (float) microtime() * 1000)
		      . sprintf('%03d', (int) $member_id % 1000);
	}

	/**
	 * 订单编号生成规则，n(n>=1)个订单表对应一个支付表，
	 * 生成订单编号(年取1位 + $pay_id取13位 + 第N个子订单取2位)
	 * 1000个会员同一微秒提订单，重复机率为1/100
	 * @param $pay_id 支付表自增ID
	 * @return string
	 */
	public function makeOrderSn($pay_id) {
	    //记录生成子订单的个数，如果生成多个子订单，该值会累加
	    static $num;

	    if (empty($num)) {
	        $num = 1;
	    } else {
	        $num ++;
	    }
        $ran = rand(1000,9999);
        return date('ymdhis',time()).$ran;
		//return (date('y',time()) % 9+1) . sprintf('%013d', $pay_id) . sprintf('%02d', $num);
	}

	/**
	 * 更新库存与销量
	 *
	 * @param array $buy_items 商品ID => 购买数量
	 */
	public function editGoodsNum($buy_items) {
        $model = Model()->table('goods');
        foreach ($buy_items as $goods_id => $buy_num) {
        	$data = array('goods_storage'=>array('exp','goods_storage-'.$buy_num),'goods_salenum'=>array('exp','goods_salenum+'.$buy_num));
        	$result = $model->where(array('goods_id'=>$goods_id))->update($data);
        	if (!$result) throw new Exception(L('cart_step2_submit_fail'));
        }
	}

    /**
     * 购买第一步
     *
     * @param array $cart_id 购物车
     * @param int $ifcart 是否为购物车
     * @param int $invalid_cart
     * @param int $member_id 会员编号
     * @param int $store_id 店铺编号
     */
    public function buyStep1_xunjia($cart_id, $ifcart, $member_id, $store_id){
        $model_cart = D('Cart');
	    $cart_id = explode(',', $cart_id[0]);
        $buy_items = $this->_parseItems($cart_id);

        if ($ifcart){
	        //更新购物车商品数量
	        foreach ($buy_items as $key => $value) {
		        $model_cart->editCart(array('goods_num' => $value), array('cart_id' => $key, 'buyer_id' => $member_id));
	        }
            //取购物车列表
            $condition = array('cart_id' => array('in', array_keys($buy_items)), 'buyer_id' => $member_id);
            $cart_list	= $model_cart->listCart('db', $condition);

            //取商品最新的在售信息
            $cart_list = $model_cart->getOnlineCartList($cart_list);

            //得到限时折扣信息
            //$cart_list = $model_cart->getXianshiCartList($cart_list);

            //得到优惠套装状态,并取得组合套装商品列表
            //$cart_list = $model_cart->getBundlingCartList($cart_list);

            //到得商品列表
            $goods_list = $model_cart->getGoodsList($cart_list);

            //购物车列表以店铺ID分组显示
            $store_cart_list = $model_cart->getStoreCartList($cart_list);

            //标识来源于购物车
            $result['ifcart'] = 1;
        }else{
            //取得购买的商品ID和购买数量,只有一个下标 ，只会循环一次
            foreach ($buy_items as $goods_id => $quantity) {break;}
            //验证商品id和数量
            if(!isset($goods_id) || empty($goods_id)){
                return array('error' => '商品ID不能为空!');
            }
            //查询该商品
            $goods_model = D('Goods');
            $condition['goods_id'] = $goods_id;
            $goods_list = $goods_model->where($condition)->select();
            if(empty($goods_list)){
                return array('error' => '商品不存在!');
            }

            //取得商品最新在售信息
            $goods_info = $model_cart->getGoodsOnlineInfo($goods_id, intval($quantity));
            if(empty($goods_info)) {
                return array('error' => '商品已下架!');
            }

            //不能购买自己店铺的商品
            if ($goods_info['store_id'] == $store_id) {
                return array('error' => '不能购买自己店铺的商品' );
            }

            //转成多维数组，方便纺一使用购物车方法与模板
            $store_cart_list = array();
            $store_cart_list[$goods_info['store_id']][0] = $goods_info;
        }

         \Think\Log::ext_log('xxxx '.json_encode($condition), 'api');

        //输出收货地址
        $field = 'address_id,true_name,area_info,address,mob_phone,tel_phone,is_default';
        $result['address_info'] = D('Address')->getAddressList(array('member_id' => $member_id, 'tesu_deleted' => 0), 'is_default desc,address_id desc', $field);

        //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
        //ps:store_cart_list 添加goodsimage 成员变量
        list($store_cart_list, $store_goods_total) = $model_cart->calcCartList($store_cart_list);

        //排序
        foreach ($store_cart_list as $store_id => $goodslist) {
            //$store_cart_list[$store_id] = array_sort($store_cart_list[$store_id], 'transporttype', SORT_DESC);
            if(!is_array($goodslist)||empty($goodslist)){
                unset($store_cart_list[$store_id]);
                continue;
            }
        }

        $result['store_cart_list'] = $store_cart_list;
        return $result;
    }

    public function buy_step1_to_fact($cart_id, $store_id){
        $buy_items = explode('|', $cart_id);
        if (empty($buy_items)){
            return array('error' => '商品ID不能为空!');
        }
        $goods_id = $buy_items[0];
        $quantity = $buy_items[1];
        //验证商品id和数量
        if(!isset($goods_id) || empty($goods_id)){
            return array('error' => '商品ID不能为空!');
        }
        //取得商品最新在售信息
        $model_cart = D('cart');
        $goods_info = $model_cart->getGoodsOnlineInfo($goods_id, intval($quantity));
        if(empty($goods_info)) {
            return array('error' => '商品已下架!');
        }
        //不能购买自己店铺的商品
        if ($goods_info['store_id'] == $store_id) {
            return array('error' => '不能购买自己店铺的商品' );
        }

        return $goods_info;
    }

    /**
     * 购买第一步
     *
	 * @param array $cart_id 购物车
	 * @param int $ifcart 是否为购物车
     * @param int $invalid_cart
     * @param int $member_id 会员编号
     * @param int $store_id 店铺编号
     */
    public function buyStep1($cart_id, $ifcart, $invalid_cart, $member_id, $store_id, $investqishu="") {
        $model_cart = Model('cart');

        $result = array();

        //取得POST ID和购买数量
        //取得商品 和商品数量
        //ifcart == true   $cart_id   cart_id|num
        //ifcart == false  $cart_id   product | 1
        $buy_items = $this->_parseItems($cart_id);

        if ($ifcart) {

            //来源于购物车

            //取购物车列表
            $condition = array('cart_id'=>array('in',array_keys($buy_items)), 'buyer_id'=>$member_id);
            $cart_list	= $model_cart->listCart('db', $condition);

            //取商品最新的在售信息
            $cart_list = $model_cart->getOnlineCartList($cart_list);

            //得到限时折扣信息
            $cart_list = $model_cart->getXianshiCartList($cart_list);

            //得到优惠套装状态,并取得组合套装商品列表
            $cart_list = $model_cart->getBundlingCartList($cart_list);

            //到得商品列表
            $goods_list = $model_cart->getGoodsList($cart_list);

            //购物车列表以店铺ID分组显示
            $store_cart_list = $model_cart->getStoreCartList($cart_list);

            //标识来源于购物车
            $result['ifcart'] = 1;

        } else {

            //来源于直接购买

            //取得购买的商品ID和购买数量,只有一个下标 ，只会循环一次
            foreach ($buy_items as $goods_id => $quantity) {break;}
            //验证商品id和数量
            if(!isset($goods_id) || empty($goods_id)){
                return array('error' => '商品ID不能为空!');
            }
            //查询该商品
            $goods_model = Model('goods');
            $condition['goods_id'] = $goods_id;
            $goods_list = $goods_model->where($condition)->select();
            if(empty($goods_list)){
                return array('error' => '商品不存在!');
            }

            //取得商品最新在售信息
            $goods_info = $model_cart->getGoodsOnlineInfo($goods_id,intval($quantity));
            if(empty($goods_info)) {
                return array('error' => '商品已下架!');
            }

            //不能购买自己店铺的商品
            if ($goods_info['store_id'] == $store_id) {
                return array('error' => '不能购买自己店铺的商品' );
            }

            //判断是不是正在团购中，如果是则按团购价格计算，购买数量若超过团购规定的上限，则按团购上限计算
            $goods_info = $model_cart->getGroupbuyInfo($goods_info);

            //如果未进行团购，则再判断是否限时折扣中
            if (!$goods_info['ifgroupbuy']) {
                $goods_info = $model_cart->getXianshiInfo($goods_info,$quantity);
            }

            //转成多维数组，方便纺一使用购物车方法与模板
            $store_cart_list = array();
            $goods_list = array();
            $goods_list[0] = $store_cart_list[$goods_info['store_id']][0] = $goods_info;
        }

        //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
        //ps:store_cart_list 添加goodsimage 成员变量
        list($store_cart_list,$store_goods_total) = $model_cart->calcCartList($store_cart_list, $_POST['investment_only'], $_POST['investqishu'], $_POST['easypay_only'], $_POST['qishu']);

        //兼容大宗商品
        //输出每个商品的物流选择数目
        //根据选择数目排序
        //todo..
        // $result['store_cart_list'] = $store_cart_list;
        $result['store_goods_total'] = $store_goods_total;


        //取得店铺优惠 - 满即送(赠品列表，店铺满送规则列表)
        list($store_premiums_list,$store_mansong_rule_list) = $model_cart->getMansongRuleCartListByTotal($store_goods_total);
        $result['store_premiums_list'] = $store_premiums_list;
        $result['store_mansong_rule_list'] = $store_mansong_rule_list;

        //重新计算优惠后(满即送)的店铺实际商品总金额
        $store_goods_total = $model_cart->reCalcGoodsTotal($store_goods_total,$store_mansong_rule_list,'mansong');

        //返回店铺可用的代金券
        $store_voucher_list = $model_cart->getStoreAvailableVoucherList($store_goods_total, $member_id);
        $result['store_voucher_list'] = $store_voucher_list;

       
        list($need_calc_sid_list,$cancel_calc_sid_list) = $this->getStoreFreightDescList($store_goods_total);
        $result['need_calc_sid_list'] = $need_calc_sid_list;
        //need calc = ["12","15"]
        $result['cancel_calc_sid_list'] = $cancel_calc_sid_list;
        // {"2":{"free_price":"1","desc":"满1免运费"}}
        // 
        //输出用户默认收货地址
        $result['address_info'] = Model('address')->getDefaultAddressInfo(array('member_id'=>$member_id));

        $freight_list = $this->getStoreFreightList($goods_list,array_keys($cancel_calc_sid_list), $result['address_info']['city_id']);
        $result['transport_info'] = $freight_list['tmpgoods'];

        //遍历商品数据，插入物流模板信息
        //在店铺中，对商品按照模板类型排序
        foreach ($store_cart_list as $store_id => $goodslist) {
            if(!is_array($goodslist)||empty($goodslist)){
                continue;
            }
            foreach ($goodslist as $key => $goodsinfo) {
                //融合物流模板信息
                if($freight_list['tmpgoods'][$goodsinfo['goods_id']]['transporttype'] == 3){
                    //2中模板都存在
                    $store_cart_list[$store_id][$key]['transporttype']    = $freight_list['tmpgoods'][$goodsinfo['goods_id']]['transporttype'];
                    $store_cart_list[$store_id][$key]['1']                = $freight_list['tmpgoods'][$goodsinfo['goods_id']]['1'];
                    $store_cart_list[$store_id][$key]['2']                = $freight_list['tmpgoods'][$goodsinfo['goods_id']]['2'];
                    $store_cart_list[$store_id][$key]['default_template'] = $freight_list['tmpgoods'][$goodsinfo['goods_id']]['default_template'];
                }else{
                    //只存在一个模板或者免运费或者固定运费
                    $new = $freight_list['tmpgoods'][$goodsinfo['goods_id']] + $store_cart_list[$store_id][$key];
                    $store_cart_list[$store_id][$key] = $new;
                }

                //$store_cart_list[$store_id][$key]['transporttype'] = 
            }
        }

        //排序
        foreach ($store_cart_list as $store_id1 => $goodslist1) {
            $store_cart_list[$key] = array_sort($store_cart_list[$key], 'transporttype', SORT_DESC);
        }

        foreach ($store_cart_list as $store_id => $goodslist) {
            if(!is_array($goodslist)||empty($goodslist)){
                unset($store_cart_list[$store_id]);
                continue;
            }
        }

        //消除空
        foreach ($tmparr as $store_id2 => $goodslist2) {
            if(!is_array($goodslist2)||empty($goodslist2)){
                unset($tmparr[$store_id2]);
                continue;
            }
            foreach ($goodslist2 as $key2 => $goodsinfo2) {
                if($goodslist2['num'] == 0){
                    unset($tmparr[$store_id2][$key2]);    
                }
            }
        }        

        //合并
        $tmparr = $store_cart_list;
        // foreach ($tmparr as $store_id2 => $goodslist2) {
        //     if(!is_array($goodslist2)||empty($goodslist2)){
        //         unset($tmparr[$store_id2]);
        //         continue;
        //     }
        //     foreach ($goodslist2 as $key2 => $goodsinfo2) {
        //         if($goodsinfo2['transporttype'] == 3){
        //             $tmparr[$store_id2][3]['num'] += 1;
        //             $tmparr[$store_id2][3]['1'] += $freight_list['tmpgoods'][$goodsinfo['goods_id']]['1'];
        //             $tmparr[$store_id2][3]['2'] += $freight_list['tmpgoods'][$goodsinfo['goods_id']]['2'];
        //         }else if($goodsinfo2['transporttype'] == 2){
        //             $tmparr[$store_id2][2]['num'] += 1;
        //             $tmparr[$store_id2][3]['2'] += $freight_list['tmpgoods'][$goodsinfo['goods_id']]['freight'];
        //         }else if($goodsinfo2['transporttype'] == 1){
        //             $tmparr[$store_id2][1]['num'] += 1;
        //             $tmparr[$store_id2][3]['1'] += $freight_list['tmpgoods'][$goodsinfo['goods_id']]['freight'];
        //         }else if($goodsinfo2['transporttype'] == 0){
        //             $tmparr[$store_id2][0]['num'] += 1;
        //             $tmparr[$store_id2][3]['2'] += $freight_list['tmpgoods'][$goodsinfo['goods_id']]['freight'];
        //         }else if($goodsinfo2['transporttype'] == -1){
        //             $tmparr[$store_id2][-1]['num'] += 1;
        //             $tmparr[$store_id2][3]['2'] += 0;
        //         }
        //         unset($tmparr[$store_id2][$key2]);
        //     }
        // }
        
        $result['store_cart_list'] = $store_cart_list;
        $result['freight_list'] = $this->buyEncrypt($freight_list, $member_id);

        // //输出用户默认收货地址
        // $result['address_info'] = Model('address')->getDefaultAddressInfo(array('member_id'=>$member_id));

        //输出有货到付款时，在线支付和货到付款及每种支付下商品数量和详细列表
        $pay_goods_list = $this->getOfflineGoodsPay($goods_list);
        if (!empty($pay_goods_list['offline'])) {
            $result['pay_goods_list'] = $pay_goods_list;
            $result['ifshow_offpay'] = true;
        } else {
            //如果所购商品只支持线上支付，支付方式不允许修改
            $result['deny_edit_payment'] = true;
        }

        //发票 :只有所有商品都支持增值税发票才提供增值税发票
        foreach ($goods_list as $goods) {
        	if (!intval($goods['goods_vat'])) {
        	    $vat_deny = true;break;
        	}
        }
        //不提供增值税发票时抛出true(模板使用)
        $result['vat_deny'] = $vat_deny;
        $result['vat_hash'] = $this->buyEncrypt($result['vat_deny'] ? 'deny_vat' : 'allow_vat', $member_id);

        //输出默认使用的发票信息
        $inv_info = Model('invoice')->getDefaultInvInfo(array('member_id'=>$member_id));
        if ($inv_info['inv_state'] == '2' && !$vat_deny) {
            $inv_info['content'] = '增值税发票 '.$inv_info['inv_company'].' '.$inv_info['inv_code'].' '.$inv_info['inv_reg_addr'];
        } elseif ($inv_info['inv_state'] == '2' && $vat_deny) {
            $inv_info = array();
            $inv_info['content'] = '不需要发票';
        } elseif (!empty($inv_info)) {
            $inv_info['content'] = '普通发票 '.$inv_info['inv_title'].' '.$inv_info['inv_content'];
        } else {
            $inv_info = array();
            $inv_info['content'] = '不需要发票';
        }
        $result['inv_info'] = $inv_info;

        //删除购物车中无效商品
        if ($ifcart) {
            if (is_array($invalid_cart)) {
                $cart_id_str = implode(',',$invalid_cart);
                if (preg_match_all('/^[\d,]+$/',$cart_id_str,$matches)) {
                    $model_cart->delCart('db',array('buyer_id'=>$member_id,'cart_id'=>array('in',$cart_id_str)));
                }
            }
        }

        //显示使用预存款支付及会员预存款
        $model_payment = Model('payment');
        $pd_payment_info = $model_payment->getPaymentOpenInfo(array('payment_code'=>'predeposit'));
        if (!empty($pd_payment_info)) {
            $buyer_info	= Model('member')->infoMember(array('member_id' => $member_id));
            if (floatval($buyer_info['available_predeposit']) > 0) {
                $result['available_predeposit'] = $buyer_info['available_predeposit'];
            }
        }

        return $result;
    }

    //预订单
    public function buyPreStep1($member_id, $store_id) {
        $model_cart = Model('cart');
        $result = array();
        //输出用户默认收货地址
        $result['address_info'] = Model('address')->getDefaultAddressInfo(array('member_id'=>$member_id));

        //显示使用预存款支付及会员预存款
        $model_payment = Model('payment');
        $pd_payment_info = $model_payment->getPaymentOpenInfo(array('payment_code'=>'predeposit'));
        if (!empty($pd_payment_info)) {
            $buyer_info	= Model('member')->infoMember(array('member_id' => $member_id));
            if (floatval($buyer_info['available_predeposit']) > 0) {
                $result['available_predeposit'] = $buyer_info['available_predeposit'];
            }
        }

        return $result;
    }

    public function createOrderNew($input, $store_id, $store_name){
        extract($input);
        $model_order = D('order');
        //存储生成的订单,函数会返回该数组
        $order_list = array();

        $pay_sn = $this->makePaySn($store_id);
        $order_pay = array();
        $order_pay['pay_sn'] = $pay_sn;
        $order_pay['buyer_id'] = $store_id;
        $order_pay_id = $model_order->addOrderPay($order_pay);
        if (!$order_pay_id) {
            throw new Exception('订单保存失败');
        }

        //收货人信息
        $reciver_info = array();
        $reciver_info['address'] = $address_info['area_info'].'&nbsp;'.$address_info['address'];
        $reciver_info['phone'] = $address_info['mob_phone'].($address_info['tel_phone'] ? ','.$address_info['tel_phone'] : null);
        $reciver_info = serialize($reciver_info);
        $reciver_name = $address_info['true_name'];

        $order = array();
        $order['order_sn'] = $this->makeOrderSn($order_pay_id);
        $order['pay_sn'] = $pay_sn;
        $order['store_id'] = $goods_info['store_id'];
        $order['store_name'] = $goods_info['store_name'];
        $order['buyer_id'] = $store_id;
        $order['buyer_name'] = $store_name;
        $order['buyer_email'] = '';
        $order['add_time'] = $_SERVER['REQUEST_TIME'];
        $order['payment_code'] = $payment_code;
        $order['order_state'] = ORDER_STATE_NEW;
        $order['goods_amount'] = $goods_info['goods_num'] * $goods_info['goods_price'];
        $order['order_amount'] = $order['goods_amount'] + $goods_info['goods_freight'];
        $order['shipping_fee'] = $goods_info['goods_freight'];
        $order['order_from'] = 1;
        $order['order_charity'] = "0";
        //标记是否是投资购订单
        $order['is_investpay'] = $_POST['investpay'] ? 1 : 0;
        $order['order_type'] = ORDER_TYPE_FACTORY;

        $order_id = $model_order->addOrder($order);
        if (!$order_id) {
            throw new Exception('订单保存失败');
        }
        $order['order_id'] = $order_id;
        $order_list[$order_id] = $order;

        $order_common = array();
        $order_common['order_id'] = $order_id;
        $order_common['store_id'] = $goods_info['store_id'];
        $order_common['order_message'] = $pay_message;
        $order_common['reciver_info']= $reciver_info;
        $order_common['reciver_name'] = $reciver_name;
        $order_common['evalseller_time'] = 0;
        $order_common['reciver_address_id'] = $reciver_address_id;
        //取得省ID
        require_once(BASE_DATA_PATH.'/area/area.php');
        $order_common['reciver_province_id'] = intval($area_array[$input_city_id]['area_parent_id']);
        $order_id = $model_order->addOrderCommon($order_common);
        if (!$order_id) {
            throw new Exception('订单保存失败');
        }

        $order_goods = array();
        $order_goods['order_id'] = $order_id;
        $order_goods['goods_id'] = $goods_info['goods_id'];
        $order_goods['store_id'] = $goods_info['store_id'];
        $order_goods['goods_name'] = $goods_info['goods_name'];
        $order_goods['goods_price'] = $goods_info['goods_price'];
        $order_goods['goods_num'] = $goods_info['goods_num'];
        $order_goods['goods_image'] = $goods_info['goods_image'];
        $order_goods['buyer_id'] = $store_id;
        $order_goods['promotions_id'] = 0;
        $order_goods['goods_pay_price'] = $goods_info['goods_price'] * $goods_info['goods_num'];
        $insert = $model_order->addOrderGoods2($order_goods);
        if (!$insert) {
            throw new Exception('订单保存失败');
        }

        return array($pay_sn, $order_list);
    }
    
    /**
     * 购物车、直接购买第二步:保存订单入库，产生订单号，开始选择支付方式
     *
     */
    public function buyStep2($post, $member_id, $member_name, $member_email) {
        $model_cart = D('Cart');
        $model_member = D('Member');

        //分期购标识
        if($post['pay_name'] =='easypay')
            $easypay = true;


        //取得商品ID和购买数量
        $input_buy_items = $this->_parseItems($post['cart_id']);

        //验证收货地址
        $input_address_id = intval($post['address_id']);
        if ($input_address_id <= 0) {
            $tmp_return['code'] = 0;
            $tmp_return['resultText']['message'] = "请选择收货地址";
            exit(json_encode($tmp_return));
        } else {
            $input_address_info = D('Address')->getAddressInfo(array('address_id' => $input_address_id));
            if ($input_address_info['member_id'] != $member_id) {
                $tmp_return['code'] = 0;
                $tmp_return['resultText']['message'] = "请选择收货地址";
                exit(json_encode($tmp_return));
            }
        }
        //收货地址城市编号
        $input_city_id = intval($input_address_info['city_id']);

        //$input_if_vat = ($input_if_vat == 'allow_vat') ? true : false;

        //是否支持货到付款
        
        // $input_if_offpay = $this->buyDecrypt($post['offpay_hash'], $member_id);
        // if (!in_array($input_if_offpay,array('allow_offpay','deny_offpay'))) {
        //     $tmp_return = jsonReturn();
        //     $tmp_return['code'] = 0;
        //     $tmp_return['resultText']['message'] = "订单保存出现异常，请重试";
        //     exit(json_encode($tmp_return));
        //     return array('error' => '订单保存出现异常，请重试');
        // }
        // $input_if_offpay = ($input_if_offpay == 'allow_offpay') ? true : false;
        $input_if_offpay = false;
        //添加easypay支持
//        if($easypay)
//            $input_if_offpay = false;

        if ($post['ifcart']) {
            //取购物车列表
            $condition = array('cart_id'=>array('in',array_keys($input_buy_items)),'buyer_id'=>$member_id);
            $cart_list	= $model_cart->listCart('db',$condition);

            //取商品最新的在售信息
            $cart_list = $model_cart->getOnlineCartList($cart_list);

            //到得商品列表
            $goods_list = $model_cart->getGoodsList($cart_list);

            //购物车列表以店铺ID分组显示
            $store_cart_list = $model_cart->getStoreCartList($cart_list);
        } else {
            //来源于直接购买
            //取得购买的商品ID和购买数量,只有有一个下标 ，只会循环一次
            foreach ($input_buy_items as $goods_id => $quantity) {break;}

            //取得商品最新在售信息
            $goods_info = $model_cart->getGoodsOnlineInfo($goods_id,$quantity);
            if(empty($goods_info)) {
                $tmp_return['code'] = 0;
                $tmp_return['resultText']['message'] = "商品不存在";
                exit(json_encode($tmp_return));
            }

            //转成多维数组，方便纺一使用购物车方法与模板
            $store_cart_list = array();
            $goods_list = array();
            $goods_list[0] = $store_cart_list[$goods_info['store_id']][0] = $goods_info;
        }

        //商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
        list($store_cart_list,$store_goods_total) = $model_cart->calcCartList($store_cart_list, $post['investpay'], $post['investqishu'], $post['easypay_only'], $post['qishu']);

	    //更新购买数量
	    $new_cart_list = $this->appendPremiumsToCartList($store_cart_list);
	    if(!empty($new_cart_list['error'])) {
		    $tmp_return['code'] = 0;
		    $tmp_return['resultText']['message'] = $new_cart_list['error'];
		    exit(json_encode($tmp_return));
	    } else {
		    list($store_cart_list,$goods_buy_quantity) = $new_cart_list;
	    }

        //整理已经得出的固定数据，准备下单

        $input = array();

	    $input['pay_message']                  = $post['pay_message'];
        $input['address_info']                 = $input_address_info;
        $input['store_goods_total']            = $store_goods_total;
        $input['store_final_order_total']      = $store_goods_total;
        $input['store_cart_list']              = $store_cart_list;
        $input['input_city_id']                = $input_city_id;
        $input['reciver_address_id']           = $input_address_id;

        try {
            //开始事务
            $model_cart->startTrans();

            //普通订单
            list($pay_sn,$order_list) = $this->createOrder($input, $member_id, $member_name, $member_email);

            //记录订单日志
            $this->addOrderLog($order_list);

            //变更库存和销量
            $this->updateGoodsStorageNum($goods_buy_quantity);

            //提交事务
            $model_cart->commit();

        }catch (Exception $e){

            //回滚事务
            $model_cart->rollback();
            exit(json_encode(array('code' => 0, 'resultText' => array('message'=>"订单保存失败"))));
            //return array('error' => $e->getMessage());
        }

        //删除购物车中的商品
        if ($post['ifcart']) {
            $model_cart->delCart('db',array('buyer_id'=>$member_id,'cart_id'=>array('in',array_keys($input_buy_items))));
        }

        //下单完成后，需要更新销量统计
        $this->_complateOrder($goods_list);
        $tmp_return['code'] = 1;
        $tmp_return['resultText']['pay_sn'] = $pay_sn;

        return array('pay_sn' => $pay_sn);
    }
    
    function createPreOrder($input,$pre_order_list,$member_id,$member_name,$member_email){
        $model_member = Model('member');
        //验证支付密码
        if ($input['pd_pay'] == 1){
            $member_paypwd = $model_member->getfby_member_id($_SESSION['member_id'], 'member_paypwd');
            if (!$member_paypwd || $member_paypwd != md5($input['password'])){
                return array('error' => '支付密码不正确');
            }
        }        
        $input_address_id = intval($input['address_id']);
        //验证收货地址
        if ($input_address_id <= 0) {
            return array('error' => '请选择收货地址');
        } else {
            $address_info = Model('address')->getAddressInfo(array('address_id'=>$input_address_id));
            if ($address_info['member_id'] != $member_id) {
                return array('error' => '请选择收货地址');
            }
        } 
        //收货人信息
        $reciver_info = array();
        $reciver_info['address'] = $address_info['area_info'].'&nbsp;'.$address_info['address'];
        $reciver_info['phone'] = $address_info['mob_phone'].($address_info['tel_phone'] ? ','.$address_info['tel_phone'] : null);
        $reciver_info = serialize($reciver_info);
        $reciver_name = $address_info['true_name'];        
        $pay_sn = $this->makePaySn($member_id);
        $order_pay = array();
        $order_pay['pay_sn'] = $pay_sn;
        $order_pay['buyer_id'] = $member_id;//print_r($param);exit;
        $model_order = Model('order');
        $order_pay_id = $model_order->addOrderPay($order_pay);
        if (!$order_pay_id) {
            throw new Exception('订单保存失败');
        }        
        foreach($pre_order_list as $k1=>$v1){
            $order['order_sn']           = $this->makeOrderSn($order_pay_id);
            $order['pay_sn']             = $pay_sn;
            $order['store_id']           = $v1['store_id'];
            $order['store_name']         = $v1['store_name'];
            $order['buyer_id']           = $member_id;
            $order['buyer_name']         = $member_name;
            $order['buyer_email']        = $member_email;
            $order['add_time']           = TIMESTAMP;
            $order['payment_code']       = 'online';

            $order['order_amount']       = $v1['goods_amount']+$v1['shipping_fee'];
            $order['shipping_fee']       = $v1['shipping_fee'];
            $order['goods_amount']       = $v1['goods_amount'];
            $order['order_from']         = 1;
            $order['order_charity']      = "0";
            //标记是否是投资购订单
            $order['is_investpay']       = 0;  
            $order['order_state']       =10;//未付款订单 
            $order['is_panel']          =1;//0 web端，1平板
            $order_id = $model_order->addOrder($order);
            if (!$order_id) {
                throw new Exception('订单保存失败');
            }
            //更改预订单表中的订单id
            Model('offline_provisional_order')->editOfflineOrder(array('allwood_order_id'=>$order_id),array('provisional_order_id'=>$k1));
            $orderInfo=$order;
            $orderInfo['order_id']=$order_id;
            $orderList[$order_id] = $orderInfo;
            $order_common['order_id'] = $order_id;
            $order_common['store_id'] = $v1['store_id'];
            $order_common['order_message'] = $input['pay_message'][$v1['store_id']];
            $order_common['reciver_info']= $reciver_info;
            $order_common['reciver_name'] = $reciver_name;  
            //取得省ID
            require_once(BASE_DATA_PATH.'/area/area.php');
            $order_common['reciver_province_id'] = intval($area_array[intval($address_info['city_id'])]['area_parent_id']);
            $order_id = $model_order->addOrderCommon($order_common);                      
            foreach($v1['goods_list'] as $k2=>$v2){
                $goods['order_id']=$order_id;
                $goods['goods_id']=$v2['goods_id'];
                $goods['goods_name']=$v2['goods_name'];
                $goods['goods_price']=$v2['goods_price'];
                $goods['goods_num']=$v2['goods_num'];
                $goods['goods_image']=$v2['goods_image'];
                $goods['goods_pay_price']=$v2['goods_price'];
                $goods['store_id']=$v2['store_id'];
                $goods['buyer_id']=$member_id;//$v2['customer_id'];
                $model_order->addOrderGoods2($goods);
                
            }
        }   
        $result=array('pay_sn'=>$pay_sn,'order_sn'=>$order['order_sn'],'orderList'=>$orderList);
        return $result;
    }

    function buyPreStep2($input, $pre_order_list, $member_id, $member_name, $member_email){
        $result=$this->createPreOrder($input, $pre_order_list, $member_id, $member_name, $member_email);
//        try {
//
//            //开始事务
//            $model_cart->beginTransaction();  
//            //提交事务
//            $model_cart->commit();
//
//        }catch (Exception $e){
//
//            //回滚事务
//            $model_cart->rollback();
//            exit(json_encode(array('code' => 0, 'resultText' => array('message'=>"订单保存失败"))));
//            //return array('error' => $e->getMessage());
//        }            
        //使用预存款支付
        if($input['pd_pay'] == 1&&$result['orderList']){
           $this->pdPay($result['orderList'], $input, $member_id, $member_name);
            //更新预订单商品库存
            if($input['goods_id_str']){
                $goods_arr=explode(',',$input['goods_id_str']);
                foreach($goods_arr as $gid){
                        $new_goods_arr[$gid]=Model()->table('offline_provisional_order_goods')->where(array('goods_id'=>$gid))->get_field('goods_num');                         
                }
                $this->editGoodsNum($new_goods_arr);
            }           
        }            
        return $result;
    }

    /**
     * 加密
     * @param array/string $string
     * @param int $member_id
     * @return mixed arrray/string
     */
    public function buyEncrypt($string, $member_id) {
        $buy_key = sha1(md5($member_id.'&'.MD5_KEY));
	    if (is_array($string)) {
	       $string = serialize($string);
	    } else {
	        $string = strval($string);
	    }
	    return encrypt(base64_encode($string), $buy_key);
    }

	/**
	 * 解密
	 * @param string $string
     * @param int $member_id
	 * @param number $ttl
     */
    public function buyDecrypt($string, $member_id, $ttl = 0) {
        $buy_key = sha1(md5($member_id.'&'.MD5_KEY));
	    if (empty($string)) return;
	    $string = base64_decode(decrypt(strval($string), $buy_key, $ttl));
	    return ($tmp = @unserialize($string)) ? $tmp : $string;
    }

    /**
     * 得到所购买的id和数量
     *
     */
    private function _parseItems($cart_id) {
        //存放所购商品ID和数量组成的键值对
    
        $buy_items = array();
        if (is_array($cart_id)) {
            foreach ($cart_id as $value) {
                if (preg_match_all('/^(\d{1,10})\|(\d{1,6})$/', $value, $match)) {
                    $buy_items[$match[1][0]] = $match[2][0];
                }
            }
        }

        return $buy_items;
    }

    /**
     * 下单完成后，更新销量统计
     *
     */
    private function _complateOrder($goods_list = array()) {
        if (empty($goods_list) || !is_array($goods_list)) return;
        foreach ($goods_list as $goods_info) {
            //更新销量统计
            $date = date('Ymd',time());

            $sale_date_array = M('salenum')->where(array('date'=>$date,'goods_id'=>$goods_info['goods_id']))->find();
            if(is_array($sale_date_array) && !empty($sale_date_array)){
                $update_param = array();
                $update_param['table'] = 'allwood_salenum';
                $update_param['data'] = array('salenum' => $goods_info['goods_num']);
                $update_param['where'] = array('date' => $date, 'goods_id' => $goods_info['goods_id']);
	            M('salenum')->where($update_param['where'])->save($update_param['data']);
            }else{
                M('salenum')->add(array('date'=>$date,'salenum'=>$goods_info['goods_num'],'store_id'=>$goods_info['store_id'],'goods_id'=>$goods_info['goods_id']));
            }            
        }
    }

    /**
     * 选择不同地区时，异步处理并返回每个店铺总运费以及本地区是否能使用货到付款
     * 如果店铺统一设置了满免运费规则，则运费模板无效
     * 如果店铺未设置满免规则，且使用运费模板，按运费模板计算，如果其中有商品使用相同的运费模板，则两种商品数量相加后再应用该运费模板计算（即作为一种商品算运费）
     * 如果未找到运费模板，按免运费处理
     * 如果没有使用运费模板，商品运费按快递价格计算，运费不随购买数量增加
     *
     * $is_buynow 是否是立即购买进入  1 是  0 不是
     */
    public function changeAddr($freight_hash, $city_id, $area_id, $member_id, $is_buynow = false) {
    	//$city_id计算运费模板,$area_id计算货到付款
        $city_id = intval($city_id);
        $area_id = intval($area_id);
        if ($city_id <= 0 || $area_id <= 0) return null;
    	//将hash解密，得到运费信息(店铺ID，运费,运费模板ID,购买数量),hash内容有效期为1小时
    	$freight_list = $this->buyDecrypt($freight_hash, $member_id);
        //算运费
        $store_freight_list = $this->calcStoreFreightNew($freight_list, $city_id, $is_buynow);    
        
    	
        
        //$store_freight_list = $this->calcStoreFreight($freight_list, $city_id);
        
    	$data = array();
    	$data['state'] = empty($store_freight_list) ? 'fail' : 'success';
    	$data['content'] = $store_freight_list;

    	//是否能使用货到付款(只有包含平台店铺的商品才会判断)
    	// $if_include_platform_store = array_key_exists(DEFAULT_PLATFORM_STORE_ID,$freight_list['iscalced']) || array_key_exists(DEFAULT_PLATFORM_STORE_ID,$freight_list['nocalced']);
    	// if ($if_include_platform_store) {
    	    //$allow_offpay = Model('offpay_area')->checkSupportOffpay($area_id,DEFAULT_PLATFORM_STORE_ID);
    	// }
    	//JS验证使用
    	//$data['allow_offpay'] = $allow_offpay ? '1' : '0';
        //PHP验证使用
        //$data['offpay_hash'] = $this->buyEncrypt($allow_offpay ? 'allow_offpay' : 'deny_offpay', $member_id);

        return $data;
    }
}