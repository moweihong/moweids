<?php
namespace Shop\Model;
use Think\Model;

class OrderModel extends Model{
    const ORDER_STATE_PAY = 20;     //已支付
    
    /**
     * 插入账单明细表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addHistory($data) {
        return $this->table('allwood_bill_history')->insert($data);
    }
    /**
     * 取单条订单信息
     *
     * @param unknown_type $condition
     * @param array $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return unknown
     */
    public function getOrderInfo($condition = array(), $extend = array(), $fields = '*', $order = '',$group = '') {
        $query = M('Order')->field($fields)->where($condition);
	    $group && $query->group($group);
	    $order && $query->order($order);
	    $order_info = $query->find();
		//echo M('Order')->getLastSql();
		//var_dump($order_info);exit;
        if (empty($order_info)) {
            return array();
        }
        $order_info['state_desc'] = orderState($order_info);
        $order_info['payment_name'] = orderPaymentName($order_info['payment_code']);
		
        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_info['extend_order_common'] = $this->getOrderCommonInfo(array('order_id'=>$order_info['order_id']));
            $order_info['extend_order_common']['reciver_info'] = unserialize($order_info['extend_order_common']['reciver_info']);
            $order_info['extend_order_common']['invoice_info'] = unserialize($order_info['extend_order_common']['invoice_info']);
        }

        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $order_info['extend_store'] = D('Store')->getStoreInfo(array('store_id'=>$order_info['store_id']));
        }

        //返回买家信息
        if (in_array('member',$extend)) {
            $order_info['extend_member'] = D('Member')->getMemberInfo(array('member_id'=>$order_info['buyer_id']));
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>$order_info['order_id']));
            foreach ($order_goods_list as $value) {
            	$order_info['extend_order_goods'][] = $value;
            }
        }
		//print_r($order_info);exit;
        return $order_info;
    }

    public function getOrderCommonInfo($condition = array(), $field = '*') {
        return M('order_common')->where($condition)->field($field)->find();
    }

    public function getOrderJoinCommonInfo($condition = array(), $field = '*'){
        return $this->table('order')->join('order_common on order.order_id=order_common.order_id')->where($condition)->field($field)->select();
    }

    public function getOrderPayInfo($condition = array()) {
        return M('order_pay')->where($condition)->find();
    }

    /**
     * 取得支付单列表
     *
     * @param unknown_type $condition
     * @param unknown_type $pagesize
     * @param unknown_type $filed
     * @param unknown_type $order
     * @param string $key 以哪个字段作为下标,这里一般指pay_id
     * @return unknown
     */
    public function getOrderPayList($condition, $pagesize = '', $filed = '*', $order = '', $key = '') {
        if(isset($condition['pay_sn'])&&empty($condition['pay_sn'][1])) return array();
        //print_r($condition);exit;
        return $this->table('allwood_order_pay')->field($filed)->where($condition)->order($order)->page($pagesize)->select();
    }

    /**
     * 获取账户明细信息
     *
     * @param      <type>  $condition  查询条件  store_id
     * @param      string  $pagesize   页面记录数量
     * @param      string  $field      字段
     * @param      string  $order      排序
     * @param      string  $key        The key
     */
    public function getAccountInfo($condition,$page_number, $record_per_page=PHP_INT_MAX){
        if(!$condition['store_id'])
            return false;
        
        //根据时间 store_id 和 订单状态进行查询 
        $condition_tmp['order.store_id'] = $_SESSION['store_id'];
        //$condition_tmp['order.order_state'] = ORDER_STATE_SUCCESS;//40
        //0(已取消)10(默认):未付款;20:已付款;30:已发货;40:已收货;50卖家处理中（分期购专用状态）',这个只要是支付就存在收支平衡
        $condition_tmp['order.order_state'] =array("in",array("20,30,40,50"));
        if(isset($condition['start_time'])){
            $condition_tmp['order.finnshed_time'] =array('egt',$condition['start_time']);//订单完成时间
        }
        if(isset($condition['end_time'])){
            $condition_tmp['order.finnshed_time'] =array('elt',$condition['end_time']);//订单完成时间
        }
        
        //此处为订单，进项金额
        $field_tmp = "order_goods.goods_name ,order_goods.goods_pay_price,order_goods.goods_num,order.finnshed_time,order.shipping_fee";
        $order_list = $this->table('order_goods')->join('order on order.order_id = order_goods.order_id')->where($condition_tmp)->field($field_tmp)->select();
       

        unset($condition_tmp);
        unset($field_tmp);
        $condition_tmp['store_id'] = $_SESSION['store_id'];
        $condition_tmp['goods_id'] = array('gt', "0");//订单商品ID,全部退款是0，此处是退一件商品
        
        if(isset($condition['start_time'])){
            $condition_tmp['add_time'] =array('egt',$condition['start_time']);
        }
        if(isset($condition['end_time'])){
            $condition_tmp['add_time'] =array('elt',$condition['end_time']);
        }
        /**
        if(isset($condition['start_time']) && isset($condition['end_time'])){
            $condition_tmp['add_time'] = array(array('egt',$condition['start_time']),array('elt',$condition['end_time']),'and');;
        }
        **/
        $field_tmp = 'goods_name,refund_amount,add_time';
        //同意的退款/退款退货订单，此处为部分退货，退一件
        $condition_tmp['seller_state'] = SELLER_STATE_AGREE;
        $refund_list = $this->table('refund_return')->where($condition_tmp)->field($field_tmp)->select();


        unset($condition_tmp);
        unset($field_tmp);

        //此处为全部退货，退货订单可能不止一件商品
        $condition_tmp['refund_return.store_id'] = $_SESSION['store_id'];
        $condition_tmp['refund_return.seller_state'] = SELLER_STATE_AGREE; //卖家处理状态:1为待审核,2为同意,3为不同意,默认为1此处为2
        $condition_tmp['refund_return.goods_id'] = 0;//此处是退全部的商品

        if(isset($condition['start_time']) && isset($condition['end_time'])){
            $condition_tmp['refund_return.add_time'] = array(array('egt',$condition['start_time']),array('elt',$condition['end_time']),'and');;
        }

        if(isset($condition['start_time'])){
            $condition_tmp['refund_return.add_time'] =array('egt',$condition['start_time']);
        }
        if(isset($condition['end_time'])){
            $condition_tmp['refund_return.add_time'] =array('elt',$condition['end_time']);
        }
        $field_tmp = 'order_goods.goods_name, order_goods.goods_pay_price,refund_return.add_time';

        $refund_list_2 = $this->table('order_goods')->join('refund_return on order_goods.order_id = refund_return.order_id')->field($field_tmp)->where($condition_tmp)->select();



        //数据处理
        $output = array();
        //遍历订单列表
        foreach ($order_list as $key => $value) {
            $arr['time_stamp'] = $value['finnshed_time'];
            $arr['good_name'] = $value['goods_name'];
            $arr['amount'] = ncPriceFormat($value['goods_num']*$value['goods_pay_price']);
            $arr['type'] = 1;
            $arr['desc'] = "下单：".$value['goods_name'];
            $arr['date'] = date('Y-m-d H:i:s', $value['finnshed_time']);
            $output[] =$arr;
        }
        foreach ($refund_list as $key => $value) {
            $arr['time_stamp'] = $value['add_time'];
            $arr['date'] = date('Y-m-d H:i:s', $value['add_time']);   
            $arr['good_name'] = $value['goods_name'];
            $arr['amount'] = $value['refund_amount'];
            $arr['type'] = 0;
            $arr['desc'] = "退款：".$value['goods_name'];
            $output[] = $arr;
        }
        foreach ($refund_list_2 as $key => $value) {
            $arr['time_stamp'] = $value['add_time'];
            $arr['date'] = date('Y-m-d H:i:s', $value['add_time']);
            $arr['good_name'] = $value['goods_name'];
            $arr['amount'] = $value['refund_amount'];
            $arr['type'] = 0;
            $arr['desc'] = "退款：".$value['goods_name'];
            $output[] = $arr;
        }

        usort($output, "_sort_by_timestamp_desc");
        $count = sizeof($output);
        $mod = $count%$record_per_page;
        $div = intval($count/$record_per_page);
        $page_total = $mod == 0 ?$div:$div+1;
        $result['code'] = 1;
        $result['num'] = $count;
        $result['page_total'] = $page_total;
        $result['data'] = $output;
        return json_encode($result);
    }

  
    /**
     * 取得订单列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderList($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = 0, $extend = array()){
       	//print_r($condition);	
       	$oder_model=M('Order');	
       	$limit .= ','.$pagesize;
	    $condition['tesu_deleted'] = 0;
        $query = $oder_model->field($field)->where($condition)->order($order);
	    $limit && $query->limit($limit);
	    $list = $query->select();
		//print_r($list);exit;
		//echo $oder_model->getLastSql();
        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as $order) {
        	$order['state_desc'] = orderState($order);
        	$order['payment_name'] = orderPaymentName($order['payment_code']);
        	if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;

        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
		
        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
            	if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = D('Store')->getStoreList(array('store_id'=>array('in',$store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
            	$store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member',$extend)) {
            $member_id_array = array();
            foreach ($order_list as $value) {
            	if (!in_array($value['buyer_id'],$member_id_array)) $member_id_array[] = $value['buyer_id'];
            }
            $member_result = M('member')->where(array('member_id'=>array('in',$member_id_array)))->limit($pagesize)->key('member_id')->select();
	        $member_list = array();
	        foreach($member_result as $member){
				$member_list[$member['member_id']] = $member;
	        }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = $member_list[$order['buyer_id']];
            }
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))));
            
            foreach ($order_goods_list as $value) {
                $value['goods_image_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
            	$order_list[$value['order_id']]['extend_order_goods'][] = $value;
            }
        }

        return $order_list;
    }

    /**
     * 取得订单列表 新版 附带extend表的同步分页
     * @param unknown $condition
     * @param string $pagesize
     * @param string $field
     * @param string $order
     * @param string $limit
     * @param unknown $extend 追加返回那些表的信息,如array('order_common','order_goods','store')
     * @return Ambigous <multitype:boolean Ambigous <string, mixed> , unknown>
     */
    public function getOrderListNew($condition, $pagesize = '', $field = '*', $order = 'order_id desc', $limit = '', $extend = array(),$jointable){
        $model = M('Order');
        $jointable_arr = explode(',', $jointable);
		//print_r($condition);exit;
        $on = array("left join allwood_order_goods on  allwood_order.order_id=allwood_order_goods.order_id");
        $list = $model->field($field)->join($on)->where($condition)->order($order)->limit($limit)->select();
        //echo $model->getLastSql();exit;
        if (empty($list)) return array();
        $order_list = array();
        foreach ($list as $order) {
            $order['state_desc'] = orderState($order);
            $order['payment_name'] = orderPaymentName($order['payment_code']);
            if (!empty($extend)) $order_list[$order['order_id']] = $order;
        }
        if (empty($order_list)) $order_list = $list;
        //追加返回订单扩展表信息
        if (in_array('order_common',$extend)) {
            $order_common_list = $this->getOrderCommonList(array('order_id'=>array('in',array_keys($order_list))));
            foreach ($order_common_list as $value) {
                $order_list[$value['order_id']]['extend_order_common'] = $value;
                $order_list[$value['order_id']]['extend_order_common']['reciver_info'] = @unserialize($value['reciver_info']);
                $order_list[$value['order_id']]['extend_order_common']['invoice_info'] = @unserialize($value['invoice_info']);
            }
        }
        //追加返回店铺信息
        if (in_array('store',$extend)) {
            $store_id_array = array();
            foreach ($order_list as $value) {
                if (!in_array($value['store_id'],$store_id_array)) $store_id_array[] = $value['store_id'];
            }
            $store_list = Model('store')->getStoreList(array('store_id'=>array('in',$store_id_array)));
            $store_new_list = array();
            foreach ($store_list as $store) {
                $store_new_list[$store['store_id']] = $store;
            }
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_store'] = $store_new_list[$order['store_id']];
            }
        }

        //追加返回买家信息
        if (in_array('member',$extend)) {
            $member_id_array = array();
            foreach ($order_list as $value) {
                if (!in_array($value['buyer_id'],$member_id_array)) $member_id_array[] = $value['buyer_id'];
            }
            $member_list = Model()->table('member')->where(array('member_id'=>array('in',$member_id_array)))->limit($pagesize)->key('member_id')->select();
            foreach ($order_list as $order_id => $order) {
                $order_list[$order_id]['extend_member'] = $member_list[$order['buyer_id']];
            }
        }

        //追加返回商品信息
        if (in_array('order_goods',$extend)) {
            //取商品列表
            $order_goods_list = $this->getOrderGoodsList(array('order_id'=>array('in',array_keys($order_list))));
            foreach ($order_goods_list as $value) {
                $value['goods_image_url'] = cthumb($value['goods_image'], 240, $value['store_id']);
                $order_list[$value['order_id']]['extend_order_goods'][] = $value;
            }
        }
		//print_r($order_list);exit;
        return $order_list;
    }

    /**
     * 待付款订单数量
     * @param unknown $condition
     */
    public function getOrderStateNewCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_NEW;
        return $this->getOrderCount($condition);
    }

    /**
     * 待发货订单数量
     * @param unknown $condition
     */
    public function getOrderStatePayCount($condition = array()) {
        $condition['order_state'] = self::ORDER_STATE_PAY;
        return $this->getOrderCount($condition);
    }

    /**
     * 待收货订单数量
     * @param unknown $condition
     */
    public function getOrderStateSendCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SEND;
        return $this->getOrderCount($condition);
    }

    /**
     * 待评价订单数量
     * @param unknown $condition
     */
    public function getOrderStateEvalCount($condition = array()) {
        $condition['order_state'] = ORDER_STATE_SUCCESS;
        $condition['evaluation_state'] = 0;
        $condition['finnshed_time'] = array('gt',TIMESTAMP - ORDER_EVALUATE_TIME);
        return $this->getOrderCount($condition);
    }

    /**
     * 取得订单数量
     * @param unknown $condition
     */
    public function getOrderCount($condition) {
        return $this->where($condition)->count();
    }

    /**
     * 取得订单商品表详细信息
     * @param unknown $condition
     * @param string $fields
     * @param string $order
     */
    public function getOrderGoodsInfo($condition = array(), $fields = '*', $order = '') {
        return $this->table('order_goods')->where($condition)->field($fields)->order($order)->find();
    }

    /**
     * 取得订单商品表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     * @param string $page
     * @param string $order
     * @param string $group
     * @param string $key
     */
    public function getOrderGoodsList($condition = array(), $fields = '*', $limit = null, $page = null, $order = 'rec_id desc', $group = null) {
        $query = $this->table('allwood_order_goods')->field($fields)->where($condition);
	    $limit && $query->limit($limit);
	    $page  && $query->page($page);
	    $order && $query->order($order);
	    $group && $query->group($group);

	    return $query->select();

    }

    /**
     * 取得订单扩展表列表
     * @param unknown $condition
     * @param string $fields
     * @param string $limit
     */
    public function getOrderCommonList($condition = array(), $fields = '*', $limit = null) {
        $query = $this->table('allwood_order_common')->field($fields)->where($condition);
	    $limit && $query->limit($limit);
	    return $query->select();
    }

    /**
     * 插入订单支付表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderPay($data) {
        return M('order_pay')->add($data);
    }

    /**
     * 插入订单表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrder($data) {
        return $this->add($data);
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderCommon($data) {
        return M('order_common')->add($data);
    }

    /**
     * 插入订单扩展表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addOrderGoods($data) {
        return M('order_goods')->addAll($data);
    }

    public function addOrderGoods2($data) {
        return M('order_goods')->add($data);
    }    

	/**
	 * 添加订单日志
	 */
	public function addOrderLog($data) {
	    $data['log_role'] = str_replace(array('buyer','seller','system','factory'),array('买家','商家','系统','工厂'), $data['log_role']);
	    $data['log_time'] = $_SERVER['REQUEST_TIME'];
        return M('order_log')->add($data);
	}

    /**
     * 添加订单协商记录
     */
    public function addOrderDealLog($data) {
        $data['log_role'] = str_replace(array('buyer','seller','system'),array('买家','商家','系统'), $data['log_role']);
        $data['log_time'] = TIMESTAMP;
        return $this->table('deal_log')->insert($data);
    }

    /**
     * 获取订单协商记录
     * @param unknown 
     * @return Ambigous <multitype:, unknown>
     */
    public function getOrderDealLogList($condition) {
        return $this->table('deal_log')->where($condition)->order('log_time desc')->select();
    }

	/**
	 * 更改订单信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrder($data,$condition) {
		return $this->where($condition)->save($data);
	}

	/**
	 * 更改订单信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrderCommon($data,$condition) {
	    return M('order_common')->where($condition)->save($data);
	}

	/**
	 * 更改订单支付信息
	 *
	 * @param unknown_type $data
	 * @param unknown_type $condition
	 */
	public function editOrderPay($data,$condition) {
		return M('order_pay')->where($condition)->save($data);
	}

    /**
     * 更改订单商品信息
     *
     * @param unknown_type $data
     * @param unknown_type $condition
     */
    public function editOrderGoods($data,$condition) {
    	//print_r($condition);print_r($data);exit;
        return M('order_goods')->where($condition)->save($data);
    }

	/**
	 * 订单操作历史列表
	 * @param unknown $order_id
	 * @return Ambigous <multitype:, unknown>
	 */
    public function getOrderLogList($condition) {
         $result=M('order_log')->where($condition)->select();
		 return $result;
		 echo M('order_log')->getLastSql();exit;
    }

    /**
     * 返回是否允许某些操作
     * @param unknown $operate
     * @param unknown $order_info
     */
    public function getOrderOperateState($operate,$order_info){

        if (!is_array($order_info) || empty($order_info)) return false;

        switch ($operate) {

            //买家取消订单
        	case 'buyer_cancel':
        	   $state = ($order_info['order_state'] <= ORDER_STATE_NEW) ||
        	       ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
        	   break;

    	   //买家取消订单
    	   case 'refund_cancel':
    	       $state = $order_info['refund'] == 1 && !intval($order_info['lock_state']);
    	       break;

    	   //商家取消订单
    	   case 'store_cancel':
               $state = in_array($order_info['order_state'], array(ORDER_STATE_MAKEPRICE, ORDER_STATE_NEW, ORDER_STATE_PAY)) ||
    	       ($order_info['payment_code'] == 'offline' &&
    	       in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND)));
    	       break;

           //平台取消订单
           case 'system_cancel':
               $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
               ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
               break;

           //平台收款
           case 'system_receive_pay':
               $state = $order_info['order_state'] == ORDER_STATE_NEW && $order_info['payment_code'] == 'online';
               break;

	       //买家投诉
	       case 'complain':
               //已支付
               //已付款
               //确认收货后30天内可以投诉
	           $state = in_array($order_info['order_state'],array(ORDER_STATE_PAY,ORDER_STATE_SEND)) ||
	               intval($order_info['finnshed_time']) > (TIMESTAMP - C('complain_time_limit'));
	           break;

            //调整运费
        	case 'modify_price':
        	    $state = ($order_info['order_state'] == ORDER_STATE_NEW) ||
        	       ($order_info['payment_code'] == 'offline' && $order_info['order_state'] == ORDER_STATE_PAY);
        	    $state = floatval($order_info['shipping_fee']) > 0 && $state;
        	   break;

        	//发货
        	case 'send':
        	    $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_PAY;
        	    break;

        	//收货
    	    case 'receive':
    	        $state = !$order_info['lock_state'] && $order_info['order_state'] == ORDER_STATE_SEND;
    	        break;

    	    //评价
    	    case 'evaluation':
    	        $state = !$order_info['lock_state'] && !intval($order_info['evaluation_state']) && $order_info['order_state'] == ORDER_STATE_SUCCESS &&
    	         TIMESTAMP - intval($order_info['finnshed_time']) < ORDER_EVALUATE_TIME;
    	        break;

        	//锁定
        	case 'lock':
        	    $state = intval($order_info['lock_state']) ? true : false;
        	    break;

        	//快递跟踪
        	case 'deliver':
        	    $state = !empty($order_info['shipping_code']) && in_array($order_info['order_state'],array(ORDER_STATE_SEND,ORDER_STATE_SUCCESS));
        	    break;

        	//分享
        	case 'share':
        	    $state = $order_info['order_state'] == ORDER_STATE_SUCCESS;
        	    break;
        }

        return $state;
    }
    
    /**
     * 联查订单表订单商品表
     *
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     * @return array
     */
    public function getOrderAndOrderGoodsList($condition, $field = '*', $page = 0, $order = 'rec_id desc') {
        return $this->table('order_goods,order')->join('inner')->on('order_goods.order_id=order.order_id')->where($condition)->field($field)->page($page)->order($order)->select();
    }
    
    /**
     * 订单销售记录 订单状态为20、30、40时
     * @param unknown $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
    public function getOrderAndOrderGoodsSalesRecordList($condition, $field="*", $page = 0, $order = 'rec_id desc') {
        $condition['order_state'] = array('in', array(ORDER_STATE_PAY, ORDER_STATE_SEND, ORDER_STATE_SUCCESS));
        return $this->getOrderAndOrderGoodsList($condition, $field, $page, $order);
    }

	/**
	 * 买家订单状态操作
	 *
	 */
	public function memberChangeState($state_type, $order_info, $member_id, $member_name, $extend_msg, $sys = 0) {
		try {

		    $this->startTrans();
		    if ($state_type == 'order_cancel') {
		        $this->_memberChangeStateOrderCancel($order_info, $member_id, $member_name, $extend_msg, $sys);
		        $message = '成功取消了订单';
		    } elseif ($state_type == 'order_receive') {
		        $this->_memberChangeStateOrderReceive($order_info, $member_id, $member_name, $extend_msg, $sys);
		        $message = '订单交易成功！';
		    }

		    $this->commit();
            return array('success' => $message);

		} catch (\Exception $e) {
		    $this->rollback();
            return array('error' => $e->getMessage());
		}
	}

	/**
	 * 取消订单操作
	 * @param unknown $order_info
	 */
	private function _memberChangeStateOrderCancel($order_info, $member_id, $member_name, $extend_msg, $sys) {
        $order_id = $order_info['order_id'];
        $if_allow = $this->getOrderOperateState('buyer_cancel',$order_info);
        if ($sys == 0 && !$if_allow) {
            throw new \Exception('非法访问');
        }

        $goods_list = $this->getOrderGoodsList(array('order_id'=>$order_id));
        $model_goods= D('Goods');
        if(is_array($goods_list) && !empty($goods_list)) {
            $data = array();
            foreach ($goods_list as $goods) {
            	$goodsstorage=$goods['goods_num'];
            	$goodssalenum=M('Goods')->field('goods_salenum')->find($goods['goods_id']);
				if($goodssalenum['goods_salenum']<$goods['goods_num'])
				{
					$goods['goods_num']=$goodssalenum['goods_salenum'];
				}
                $data['goods_storage'] = array('exp','goods_storage+'.$goodsstorage);
                $data['goods_salenum'] = array('exp','goods_salenum-'.$goods['goods_num']);
                $update = $model_goods->editGoods($data,array('goods_id'=>$goods['goods_id']));
                if (!$update) {
                    throw new \Exception('保存失败');
                }
            }
        }
        
        //解冻预存款
        /*$pd_amount = floatval($order_info['pd_amount']);
        if ($pd_amount > 0) {
            $model_pd = Model('predeposit');
            $data_pd = array();
            $data_pd['member_id'] = $member_id;
            $data_pd['member_name'] = $member_name;
            $data_pd['amount'] = $pd_amount;
            $data_pd['order_sn'] = $order_info['order_sn'];
            $model_pd->changePd('order_cancel',$data_pd);
        }*/

        //更新订单信息
        $update_order = array('order_state' => ORDER_STATE_CANCEL, 'pd_amount' => 0);
        $update = $this->editOrder($update_order,array('order_id'=>$order_id));
        if (!$update) {
            throw new \Exception('保存失败');
        }

        //添加订单日志
        $data = array();
        $data['order_id'] = $order_id;
        $data['log_role'] = $sys == 1 ? 'system' : 'buyer';
        $data['log_msg'] = '取消了订单';
        if ($extend_msg) {
            $data['log_msg'] .= ' ( '.$extend_msg.' )';
        }
        $data['log_orderstate'] = ORDER_STATE_CANCEL;
        $this->addOrderLog($data);
	}

	/**
	 * 收货操作
	 * @param unknown $order_info
	 */
	private function _memberChangeStateOrderReceive($order_info, $member_id, $member_name, $extend_msg, $sys) {
        throw new \Exception('方法被禁用');
	    $order_id = $order_info['order_id'];

	    //更新订单状态
        $update_order = array();
        $update_order['finnshed_time'] = time();
	    $update_order['order_state'] = ORDER_STATE_SUCCESS;
	    $update = $this->editOrder($update_order,array('order_id'=>$order_id));
	    if (!$update) {
	        return 0;//更新失败
	    }

	    //添加订单日志
	    $data = array();
	    $data['order_id'] = $order_id;
	    $data['log_role'] = $sys == 1 ? 'system' : 'buyer';
	    $data['log_msg'] = '签收了货物';
	    if ($extend_msg) {
	        $data['log_msg'] .= ' ( '.$extend_msg.' )';
	    }
	    $data['log_orderstate'] = ORDER_STATE_SUCCESS;
	    $this->addOrderLog($data);

	    //确认收货时添加会员积分
	    if (C('points_isuse') == 1){
	        $points_model = D('Points');
	        $points_model->savePointsLog('order',array('pl_memberid'=>$member_id,'pl_membername'=>$member_name,'orderprice'=>$order_info['order_amount'],'order_sn'=>$order_info['order_sn'],'order_id'=>$order_info['order_id']),true);
	    }
	}

    /****
     * 获取所有store_id根据buyer_id
     */
    public function getAllStoreIdWithBuyerId($condition,$group_by='',$field='*'){

        return $this->table('order')->field($field)->where($condition)->group($group_by)->select();

    }

        
    /**根据商品ID获取昨天下单总数**/
    public  function  getOrderYesterdayCount($store_id){
        $s1=strtotime(date("Y-m-d",strtotime("-1 day")));
        $s2=strtotime(date("Y-m-d",time()));
        $condition['payment_time']=array('between',$s1.','.$s2);
        $condition['store_id']=$store_id;
        return $this->where($condition)->count();
    }
    /**根据商品ID获取昨天交易总数**/
    public  function  getOrderYesterdayMoney($store_id){

        $s1=strtotime(date("Y-m-d",strtotime("-1 day")));
        $s2=strtotime(date("Y-m-d",time()));
        $condition['payment_time']=array('between',$s1.','.$s2);
        $condition['store_id']=$store_id;
        return $this->where($condition)->sum('order_amount');
    }
}

