<?php
/*
 * 会员中心
 */
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;

class MemberController extends ShopCommonController {
    /*
     * 检查用户是否登录,如果没有，跳转前台登录页面
     */
    function  __construct() {
        parent::__construct();
		$this->User=D('User');
		$this->uid=$_SESSION['member_id'];
//		$this->uid=511;
        //没有登录，跳转到登录界面
         if(is_null($_SESSION['member_id'])){
             redirect(U('shop/login/index'));
         }
		$member_info = M('member')->find($this->uid);
		$data['member_info']=$member_info;
		$this->assign('output2',$data);
   }

   public function debug(){

    
    $this->display();
   }
   
   // 买家首页
   public function index(){
   	//print_r($_SESSION);exit;
   	//猜你喜欢数据
	    $guess_you_like = $this->User->getViewedGoodsList();//从浏览记录拿数据
	    if (empty($guess_you_like)) {
	        $guess_you_like = $this->User->getGoodsList(array('goods_state'=>1), 'goods_id,goods_name,goods_image,goods_state,goods_price','','goods_addtime desc','8');
	    }
		$remain=$this->User->tongJiLzMoney($_SESSION['mid']);//乐装余额
		$this->assign('remain',$remain);
		
		//print_r($guess_you_like);exit;
        $easypay_api = API('easypay');
        $result = json_decode($easypay_api->get_credit_status(array('usrid' =>$_SESSION['mid'])), true);
		//print_r($result);exit;
        if($result['code'] == 0){
            if(!C('debug')){
                //写入数据库
                $application_model = D('Easypayapplication');
                $data = $result['return_param'];
				//print_r($data);exit;
                //总额度
                $arr['credit_total']     = $data['loan_limit'];
                //可用额度
                $arr['credit_available'] = $data['loan_useble'];
                //授信状态
                $arr['credit_status']    = $data['check_flag'];
                //print_r($result);exit;
                $arr['is_active']        = $data['is_activate'];
                $application_model->insert_update($this->uid, $arr);
			
                $_SESSION['easypay_credit_status'] = $data['check_flag'];
                $_SESSION['easypay_credit_total'] = $data['loan_limit'];
                $_SESSION['easypay_credit_available'] = $data['loan_useble'];
                $_SESSION['is_activate']              = $data['is_activate'];
                $_SESSION['easypay_freeze'] = $_SESSION['easypay_credit_status'] === -1 ? 1:0;;
                $_SESSION['easypay_status_zh'] = str_replace(
                    array(0, 1, 3, 2, 5, 4, 6),
                    array('未开启', '审批中','审批中', '审批未通过', '已开通', '审批未通过','冻结' ),
                    intval($_SESSION['easypay_credit_status']));
            }
        }
		
	    $member_info = M('member')->find($_SESSION['member_id']);
		//待付款
		$member_info['order_nopay']=M('Order')->where('order_state=10 and buyer_id='.$_SESSION['member_id'])->count();
		//待发货
		$member_info['order_nosend']=M('Order')->where('order_state=20 and buyer_id='.$_SESSION['member_id'])->count();
		//待定价
		$member_info['order_dinjia']=M('Order')->where('order_state=1 and buyer_id='.$_SESSION['member_id'])->count();
		//已收货
		$member_info['order_noreceiving']=M('Order')->where('order_state=40 and buyer_id='.$_SESSION['member_id'])->count();
		//print_r($member_info);exit;
	    $this->assign('member_info', $member_info);
		$this->assign('guess_you_like', $guess_you_like = !empty($guess_you_like) ? $guess_you_like : array());
    	$this->display();
   }
   
   //我的订单
   public function myOrder()
   {
   		
   		$sortby=!isset($_GET['sortby']) ? 'desc':$_GET['sortby'];
		$orderby =!isset($_GET['sortby']) ? 'allwood_order.add_time desc' : 'allwood_order.add_time '.$_GET['sortby'];
   		if(isset($_GET['p'])){$offset=($_GET['p']-1)*10;}else{$offset=0;}
   		$this->assign('sortby', $_GET['sortby']);
		$model_order = D('Order');
		//根据订单状态控制tab菜单
		$curr = -1;
        //搜索
        $condition = array();
        $condition['buyer_id'] = $this->uid;
        if ($_GET['order_sn'] != '') {
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if(isset($_GET['order_state']) && !empty($_GET['order_state'])){
        	$curr = $_GET['order_state'];
        	if($_GET['order_state'] == 1000){
        		showMessage('功能正在开发中!');
        	}
        	$condition['order_state'] = $_GET['order_state'];
        }
        
		//存在店铺搜索和商品名称搜索
		if( isset($_GET['kword']) && !empty($_GET['kword'])){
			$this->assign('kword', $_GET['kword']);
			$condition_c['allwood_order.order_type']=array('neq',3);
			foreach ($condition as $k=>$v){
				$condition_c['allwood_order.'.$k]=$v;
			}
			$condition_c['allwood_order.store_name|allwood_order_goods.goods_name']=array('like','%'.$_GET['kword'].'%');

			$order_list = $model_order->getOrderListNew($condition_c, 50, '*', $orderby,$offset, array('order_common','order_goods','store'),'order_goods,refund_return');
		
		}else{
			//print_r($condition);
			$count=$model_order->where($condition)->count();
			$show= getPage($count,$pagesize=10);// 分页显示输出
			$this->assign('page',$show);
			$condition['allwood_order.order_type']=array('neq',3);
        	$order_list = $model_order->getOrderList($condition, 10, '*', $orderby,$offset, array('order_common','order_goods','store'));
			//echo $model_order->getLastSql();exit;
		}
		//print_r($order_list);exit;
		$model_goods=D('Goods');
		foreach ($order_list as $k=>$v){
			foreach($v['extend_order_goods'] as $k2=>$v2){
				//获取规格列表
				$goods_condition['goods_id']=$v2['goods_id'];
				//echo $v2['goods_id'];exit;
				$spec=$model_goods->getGoodsInfo($goods_condition,'goods_spec');
				$spec = unserialize($spec['goods_spec']) ?: [];
				$spec_str = '无';
				if (is_array($spec) && !empty($spec)) {
					$spec_str='';
					foreach ($spec as $k3 => $v3) {
					    $spec_str.=$v3;
					}
					if(count($spec)>1){
						$spec_str = substr($spec_str, 0, -1);
					}
				}
				//print_r($spec_str);exit;
				$order_list[$k]['extend_order_goods'][$k2]['goods_spec'] = $spec_str;
			}
		}
		//print_r($order_list);exit;
		$data['curr']=$curr;
		$data['sortby']=$sortby;
		$data['order_list']=$order_list;
		$this->assign('output', $data);
		$this->display();
		
   }

   public function delPreOrderGoods(){
    $this->jsonFail('这家伙很懒，什么都没留下');
   }

	/**
	 * 订单详情页
	 * 传入参数  order_id
	 * @return [type] [description]
	 */
	public function order_detail(){
		$order_id = intval(I('get.order_id'))?intval(I('get.order_id')):intval(I('post.order_id'));
        $isSeller = intval(I('get.is_seller'))?intval(I('get.is_seller')):intval(I('post.is_seller'));
		$order_info = $this->get_order_info($order_id);
		//print_r($order_info);exit;
		$this->assign('order_info', $order_info);

        $data['is_seller'] = $isSeller;
		$data['order_info']=$order_info;

		$express = ($express = H('express'))? $express :H('express',true);
		$data['e_name']=$express[$order_info['extend_order_common']['shipping_express_id']]['e_name'];
		
        switch ($order_info['order_state']) {
            //标准订单状态
            case ORDER_STATE_CANCEL:
				$model_order = D('Order');
				$condition = array();
				$condition['log_orderstate'] = '0';
				$condition['order_id'] = $order_id;
				$getOrderLogList = $model_order->getOrderLogList($condition);
				$order_log = $getOrderLogList[0];
				$data['order_log']=$order_log;
				//var_dump($getOrderLogList);exit;

				$refund_model = D('Refund_return');
				$condition = array();
				$condition['order_id'] = $order_id;
				$condition['refund_type'] = 1;
				$condition['goods_id'] = $condition['order_goods_id'] = 0;
				$refund_info = $refund_model->getRefundList($condition, '', 1);
				$data['refund_info']=$refund_info[0];
				
                break;
            case ORDER_STATE_NEW:
                $countdown = ceil(($order_info['add_time'] + C('close_unPay_order_limittime') - time()) / (24 * 3600));
                $countdown = $countdown > 0 ? $countdown : 0;
				$data['countdown']=$countdown;
                break;
            case ORDER_STATE_PAY:
				$refund_model = D('Refund_return');
				$condition = array();
				$condition['order_id'] = $order_id;
				$refund_info = $refund_model->getRefundList($condition, '', 1);
				$data['refund_info']=$refund_info[0];

				if (!empty($order_info['extend_order_goods'])){
					$goods_name_arr = array();
					foreach ($order_info['extend_order_goods'] as $goodsinfo){
						$goods_name_arr[] = $goodsinfo['goods_name'];
					}
					$data['goods_name_str']=implode(',', $goods_name_arr);
					
				}

				if ($order_info['lock_state']) {
				    //if ($order_info['order_type'] == 2){
                    //    $countdown = ceil(($refund_info[0]['add_time'] + C('easypay_auto_agree_refund_payorders_limittime') - time()) / (24 * 3600));
                    //}else{
                        $countdown = ceil(($refund_info[0]['add_time'] + C('auto_agree_refund_payorders_limittime') - time()) / (24 * 3600));
                    //}
				}else{
					$countdown = ceil(($order_info['payment_time'] + C('close_unsend_order_limittime') - time()) / (24 * 3600));
				}
				$countdown = $countdown > 0 ? $countdown : 0;
				$data['countdown']=$countdown;
                break;
			case ORDER_STATE_SEND:
				$model_refund = D('RefundReturn');
				$condition = array();
				$condition['buyer_id'] = $order_info['buyer_id'];
				$condition['order_id'] = $order_info['order_id'];
				$condition['refund_state'] = 1;
				$refund_list = $model_refund->getRefundReturnList($condition);

                if ($order_info['lock_state'] == 0){
                    $countdown = ceil(($order_info['extend_order_common']['shipping_time'] + C('auto_receive_order_limittime') - time()) / (24 * 3600));
                    $countdown = $countdown > 0 ? $countdown : 0;
                    $data['countdown']=$countdown;
                }

				$refund_type_arr = array();
				if (!empty($refund_list)){
					foreach ($refund_list as $key => $refund) {
						$refund_type_arr[] = str_replace(array(1,2),array('退款中','退款退货中'), $refund['refund_type']);

						if ($refund['seller_state'] == 1){//待审核
							$starttime = $refund['add_time'];
							$limittime = $refund['refund_type'] == 1 ? C('auto_agree_refund_sendorders_limittime') : C('auto_agree_goodsmoneyback_limittime');
						}elseif($refund['seller_state'] == 2){//同意
							if ($refund['return_type'] == 2 && $refund['refund_state'] == 1 && $refund['goods_state'] == 1){//2为需要退货 1为处理中
								$starttime = $refund['seller_time'];
								$limittime = C('cancel_sendbackgoods_limittime');//买家发货给卖家,超时自动取消
							}elseif ($refund['goods_state'] == 2){//已发货给卖家
								$starttime = $refund['ship_time'];
								$limittime = C('auto_receive_sendbackgoods_limittime');//买家发货给卖家,超时自动退款退货
							}elseif ($refund['goods_state'] == 3) {//卖家拒绝确认收货
								$starttime = $refund['admin_time'];
								$limittime = C('cancel_seller_refund_receive_order_limittime');
							}
						}

						$countdown = ceil(($starttime + $limittime - time()) / (24 * 3600));
						$refund_list[$key]['countdown'] = $countdown > 0 ? $countdown : 0;

					}
					$data['refund_type_str']=implode(',', array_unique($refund_type_arr));
					
				}
				$data['refund_list']=$refund_list;
				break;
            case ORDER_STATE_SUCCESS:
				
                break;
            case ORDER_STATE_HANDLING:
                $refund_model = D('RefundReturn');
                $condition = array();
                $condition['order_id'] = $order_id;
                $condition['refund_type'] = 1;
                $condition['goods_id'] = $condition['order_goods_id'] = 0;
                $refund_info = $refund_model->getRefundList($condition, '', 1);
				$data['refund_info']=$refund_info[0];
                //Tpl::output('refund_info',$refund_info[0]);
                break;
            default:
                showDialog('404');
                break;
        }
		//print_r($data['order_info']);exit;
		$this->assign('output',$data);
		$this->display('orderdetails');
	}
   
    /**
     * 买家的左侧上部的头像和订单数量
     *
     */
    public function get_member_info(){
        //生成缓存的键值
        $hash_key = $_SESSION['member_id'];
        //写入缓存的数据
        $cachekey_arr = array('member_name', 'store_id', 'member_avatar', 'member_qq', 'member_email', 'member_ww', 'member_goldnum', 'member_points',
            'available_predeposit', 'member_snsvisitnum', 'credit_arr', 'order_nopay', 'order_noreceiving', 'order_noeval', 'fan_count');
        if (false) {
            foreach ($_cache as $k => $v) {
                $member_info[$k] = $v;
            }
        } else {
            $model_order = D('Order');
            $model_member = D('Member');
            $model_refund = D('Refund_return');
            $member_info = $model_member->getMemberInfo(array('member_id' => $_SESSION['member_id']));
            $member_info['order_nopay'] = $model_order->getOrderStateNewCount(array('buyer_id' => $_SESSION['member_id']));
            $member_info['order_noreceiving'] = $model_order->getOrderStateSendCount(array('buyer_id' => $_SESSION['member_id']));
            $member_info['order_noeval'] = $model_order->getOrderStateEvalCount(array('buyer_id' => $_SESSION['member_id']));
            $member_info['order_nosend'] = $model_order->getOrderStatePayCount(array('buyer_id' => $_SESSION['member_id']));
            $member_info['order_refunding'] = $model_refund->getRefundReturnCount(array('refund_state' => array('in', '2,3'), 'buyer_id' => $_SESSION['member_id']));
        }
		$dataArr['member_info']=$member_info;
		$dataArr['header_menu_sign']='snsindex';//默认选中顶部“买家首页”菜单
        $this->assign('output', $dataArr);
    }

	//获取订单信息
	public function get_order_info($order_id, $extend = array('order_goods','order_common','store')){
		$model_order = D('order');
		$condition = array();
		$condition['order_id'] = $order_id;
		$condition['buyer_id'] = $_SESSION['member_id'];
		$order_info = $model_order->getOrderInfo($condition, $extend);

		return !empty($order_info) ? $order_info : array();
	}
	
	  /**
     * 买家确认收货
     */
    public function order_receive(){
        $order_id = intval($_POST['order_id']);
		 //$order_id = 1148;
        if ($order_id <= 0){
            $result = array('resultText' => array('message' => '没有此订单id'), 'code' => 0);
        }else{
            $model_order = D('Order');

            $condition = array();
            $condition['order_id'] = $order_id;
            $condition['buyer_id'] = $_SESSION['member_id'];
            $order_info	= $model_order->getOrderInfo($condition);
			//print_r($order_info);exit;
            if (!empty($order_info)){
                $result = $model_order->memberChangeState('order_receive', $order_info, $_SESSION['member_id'], $_SESSION['member_name'], '');
                if(empty($result['error'])) {//如果没有失败记录
                    $result = array('resultText' => array('message' => $result['success']), 'code' => 1);
                    $orderlogModel = M();
                    //记录买家操作订单的日志
                    $orderlogModel->table("allwood_order_log")->add(array(
                        "order_id"=>$order_id,
                        "log_msg" =>"确认了收货",
                        "log_time"=>time(),
                        "log_role"=>"买家",
                        "log_user"=>$_SESSION['member_id'],
                        "log_orderstate"=>"40"
                    ));
    
                    $amount = 0;
                    $orderlogModel->startTrans();
                    $list = $orderlogModel->table("allwood_platform_param")->find();
					
                    $flag = $orderlogModel->execute("update allwood_platform_param t set t.money_total=".($list["money_total"] - $amount));
					//echo $orderlogModel->getLastSql();exit;
					//echo $flag;exit;
                    if($flag){//如果全木行扣款成功
                    
                        //$mylist=$orderlogModel->table("allwood_store")->where(array("store_id"=>$order_info["store_id"]))->find();
                        //$flag1=$orderlogModel->table("allwood_store")->where(array("store_id"=>$order_info["store_id"]))->save(array("deposit_avaiable" => $mylist["deposit_avaiable"] + $amount));
						//echo $orderlogModel->getLastSql();exit;	
                        $flag1=true;
                        $flag2=$orderlogModel->table("allwood_collect_money_record")->add(array("money"=>$amount,
                            "is_income"=>0,
                            "store_id"=>$order_info["store_id"],
                            "store_name"=>"官方店铺",
                            "remark"=>"全木行转款到商家",
                            "add_time"=>date("Y-m-d H:i:s",time())
                        ));//记录全木行扣款
						
                        if($flag1 && $flag2){
                            $result["code"] = 1;
                            $result["resultText"]["message"] = "操作成功";
                            $orderlogModel->commit();
                        }else{
                            $result["resultText"]["message"] = "操作失败请重试";
                            $result["code"] = 0;
                            $orderlogModel->rollback();
                        }
                    }else{//扣款失败
                        $result["resultText"]["message"] = "操作失败请重试";
                        $result["code"] = 0;
                        $orderlogModel->rollback();
                    }

                } else {
                    $result = array('resultText' => array('message' => $result['error']), 'code' => 0);
                }
               // $goodsshow= Factory::ccfaxFactory("JavaGoods");
                //$goodsshow->write_goods_info($order_id);//发送信息给java那边
            }else{
                $result = array('resultText' => array('message' => '没有此订单id信息'), 'code' => 0);
            }
        }
        echo json_encode($result);
    }
   
   //我的购物车
   public function myShopCart()
   {
   	
		$model_cart	= D('Cart');

        if($this->uid) {
            $type = 'db';
        } else {
            $type = 'cookie';
        }
        //取出购物车信息
        //$cart_list	= $model_cart->listCart('db',array('buyer_id'=>$_SESSION['member_id']));
        $cart_list  = $model_cart->listCart($type, array('buyer_id'=>$this->uid));
	
        //取商品最新的在售信息
        $cart_list = $model_cart->getOnlineCartList($cart_list);

        //购物车商品以店铺ID分组显示,并计算商品小计,店铺小计与总价由JS计算得出
        $store_cart_list = array();
        foreach ($cart_list as $cart) {
            $cart['goods_total'] = ncPriceFormat($cart['goods_price'] * $cart['goods_num']);
            $store_cart_list[$cart['store_id']][] = $cart;
        }

        $this->assign('goods_num', sizeof($cart_list));

        //兼容登录和非登陆情况，当cart_id 为空时，默认赋值为goods_id
        $store_cart_list = $this->_goods_id_copy($store_cart_list);
		$model_goods=D('Goods');
		foreach ($store_cart_list as $k=>$v2)
		{
			//print_r($v2);exit;
			foreach($v2 as $k2=>$v){
				$goods_condition['goods_id']=$v['goods_id'];
				$spec=$model_goods->getGoodsInfo($goods_condition,'goods_spec');
				//print_r($spec);exit;
				$spec = unserialize($spec['goods_spec']) ?: [];
				$spec_str = '无';
				if (is_array($spec) && !empty($spec)) {
					$spec_str='';
					foreach ($spec as $k3 => $v3) {
					    $spec_str.=$v3;
					}
					if(count($spec)>1){
						$spec_str = substr($spec_str, 0, -1);
					}
				}
				$store_cart_list[$k][$k2]['goods_spec'] = $spec_str;
			}
		}
        //print_r($store_cart_list);exit;
        //取得分期购提示信息
        $store_cart_list = $this->easypay_cart_info($store_cart_list);

        $this->assign('store_cart_list',$store_cart_list);

        //店铺信息
        $store_list = D('store')->getStoreMemberIDList(array_keys($store_cart_list));
        $this->assign('store_list',$store_list);
	
        //标识 购买流程执行第几步
	    $this->assign('buy_step','step1');
		$this->display('myshopcar');

   	
   }

	/**
	 * 非登陆情况下复制goods_id 到 cart_id
	 * @param  [type] $cart_list [description]
	 * @return [type]            [description]
	 */
	private function _goods_id_copy($cart_list){
		if(!isset($cart_list) || empty($cart_list))
			return array();

		foreach ($cart_list as $store_id => $goods_array) {
			if(!isset($goods_array) || empty($goods_array))
				continue;
			foreach ($goods_array as $key => $value) {
				if(is_null($cart_list[$store_id][$key]['cart_id'] ))
					$cart_list[$store_id][$key]['cart_id'] = $cart_list[$store_id][$key]['goods_id'];
			}
		}
		return $cart_list;
	}
	
	 /**
     * 购物车根据商家显示提示信息
     * @return [type] [description]
     */
    public function easypay_cart_info($cart_list){
    	
        foreach ($cart_list as $store_id => $goods_list) {
            $cart_list[$store_id][0]['easypay_tip'] = $this->_getEasypayTip($goods_list, $store_id);
            $cart_list[$store_id][0]['is_tiexi'] = $this->_is_tiexi($store_id);
        }
		
        return $cart_list;
    }
	
	 private function _getEasypayTip($goods_list, $store_id){
   
		 if ($_SESSION['is_login'] !== '1') {
            //未登录
            return "";
         }
		
         if($_SESSION['easypay_credit_status'] == APPLYSTATUS_NONE){
            //未申请额度，去申请开通额度
            return "满1000元可免息分期  <a href=".urlShop('easypay', 'apply_for_credit_step1')." > 立即开通</a>";
         }

         if($_SESSION['easypay_credit_status'] == APPLYSTATUS_FREEZE){
            //额度冻结
            
            return "";
         }

         if($_SESSION['easypay_credit_available'] < 1000){
            //可用额度不满1000
            return "";
         }

         //获取店铺下商品的总价
         if(!is_array($goods_list) || empty($goods_list))
            return null;

        $total = 0;
        foreach ($goods_list as $key => $goods_info) {
            $total += $goods_info['goods_total'];
        }

        if($total < 1000){
            //购物商品不满1000元，去凑单
            return "满1000元可免息分期 <a href=".urlShop('show_store', 'index', array('store_id' => $store_id)).">去凑单>></a>";
        }

        //判断商家免息还是不免息
        $store_bind_class = Model('store_bind_class');
        $avg_commit_rate = $store_bind_class->field('avg(`commis_rate`)')->where(array('store_id' => $store_id))->select();
        
        if(!is_array($avg_commit_rate) || empty($avg_commit_rate)){
            return null;
        }else{
            $avg_commit = $avg_commit[0]['commis_rate'];
            if($avg_commit > C('commit_watershed')){
                //贴息
                return "可免息分期支付";
            }else{
                //不贴息
                return "可分期支付";
            }
        }
        
        return null;



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
        $store_model = Model('store');
        $storeinfo = $store_model->getStoreInfoByID($store_id);
         return $storeinfo['is_discount'] == 1 ? 1 : 0;
    }
	
   
   //我的资料
	public function profile()
	{
		if ($_POST){
	        $member_model = D('Member');
	        $param['member_name'] = $_POST['member_name'];
	        $param['member_avatar'] = $_POST['member_avatar'];
	        $param['member_sex'] = $_POST['member_sex'];
	        $param['member_qq']	= $_POST['member_qq'];
	        $param['qianming']	= $_POST['qianming'];
	        $update = $member_model->updateMember($param, $_SESSION['member_id']);
	        if ($update != false) {
				echo json_encode(array('code' => 1, 'resultText' => array('message' => '修改成功')));
	        }else{
				echo json_encode(array('code' => 0, 'resultText' => array('message' => '修改失败')));
			}
			exit;
	    }
	    
	    $model_member = M('member');
	    $member_info = $model_member->find($this->uid);
		//print_r($member_info);exit;
		$data['member_info']=$member_info;
		$this->assign('output',$data);
	    $this->display('profile');
		
	}
	
	//收货地址
	public function addressSetup()
	{
		$res=$this->User->myAddress($this->uid);
		foreach($res as $key=>$val)
		{
			$res[$key]['address']=str_replace("+", '', $val['address']);
		}
		$data['address_list']=$res;
		
		//print_r($res);exit;
		$this->assign('output', $data);
		//print_r($data);exit;
		$this->display();
	}
	
	
	/**
	 *	接口，保存收货人信息
	 *	
	 * @return [type] [description]
	 */
	public function save_address(){
		
		$return = get_standard_return();

		$address_class = D('Address');
		//验证地址信息
//		$error = $this->_validate_address();
//		if ($error != ''){
//			$return['code'] = 0;
//			$return['resultText']['message'] = $error;
//			//返回json,数据验证错误
//			echo json_encode($return);
//			exit;
//		}

        $data = array();
		$data['member_id']  = $_SESSION['member_id'];
		$data['true_name']  = $_POST['true_name'];
		$data['area_id']    = intval($_POST['area_id']);
		$data['city_id']    = intval($_POST['city_id']);
		$data['province_id'] = intval($_POST["province_id"]);
		$data['zip_code'] = $_POST['zip_code'];
		$data['area_info']  = $_POST['area_info'];
		$data['address']    = htmlentities($_POST['address']);
		$data['tel_phone']  = $_POST['tel_phone'];
		$data['mob_phone']  = $_POST['mob_phone'];
		$data['is_default'] = $_POST['is_default'];
		if($_POST['is_default']){
			//设置当前地址为默认地址，需要重置其它选项
			$address_class->undefaultAll();
		}
		
//		$data['member_id'] = 429;
//		$data['true_name'] = 'fdasf';
//		$data['area_id'] = 1126; 
//		$data['city_id'] = 73; 
//		$data['province_id'] = 3 ;
//		$data['zip_code'] = 1213123 ;
//		$data['area_info'] = '河北省石家庄市井陉县 ';
//		$data['address'] = 'sadasd'; 
//		$data['tel_phone'] = '0758-1234567'; 
//		$data['mob_phone'] = 13580130226 ;
//		$data['is_default'] = 0;
		//echo  111;exit;
		$result = $address_class->addAddress($data);
		//var_dump($result);exit;
		if(!$result){
			//失败，数据库写入失败
			$return['code'] = 0;
			$return['resultText']['message'] = "数据库写入失败";
		}else{
			$return['resultText']['address_id'] = $result;
		}

		echo  json_encode($return);
	}

/**
	 * 验证地址信息
	 * @return [type] [description]
	 */
	private function _validate_address(){
		$obj_validate = new Validate();
		$obj_validate->validateparam = array(
			array("input"=>$_POST["true_name"],"require"=>"true","message"=>$lang['member_address_receiver_null']),
			array("input"=>$_POST["area_id"],"require"=>"true","validator"=>"Number","message"=>$lang['member_address_wrong_area']),
			array("input"=>$_POST["city_id"],"require"=>"true","validator"=>"Number","message"=>$lang['member_address_wrong_area']),
			array("input"=>$_POST["area_info"],"require"=>"true","message"=>$lang['member_address_area_null']),
			array("input"=>$_POST["address"],"require"=>"true","message"=>$lang['member_address_address_null']),
			array("input"=>$_POST['tel_phone'],'require'=>'true','message'=>$lang['member_address_phone_and_mobile']),
			array("input"=>$_POST['mob_phone'],'require'=>'true','message'=>$lang['member_address_phone_and_mobile'])
		);
		$error = $obj_validate->validate();
		return $error;
	}



	/**
	 * 接口，删除数据
	 * 传入数据 address_id
	 * @return [type] [description]
	 */
	public function delete_address(){
		$_POST['address_id'] = $_POST['id'];
		
		//初始化返回数据
		$return = get_standard_return();

		//post不为空
		if(!isset($_POST) && empty($_POST)){
			$return['code'] = 0;
			$return['resultText']['message'] = "参数无效";
			echo  json_encode($return);
			exit;
		}

		//address_id 和 member_id 匹配
		$address_model = D('Address');
		$condition['address_id'] = $_POST['address_id'];
		$condition['member_id'] = $_SESSION['member_id'];
		//echo json_encode($return);
		$record = $address_model->delAddress($condition);
		if(!$record){
			//数据不存在
			$return['code'] = 0 ;
			$return['resultText']['message'] = "删除失败".mysqli_error();
			echo json_encode($return);
			exit;
		}else{
			$return['resultText']['address_id'] = $_POST['address_id'];
		}
		
		//默认删除操作
		echo json_encode($return);
	}
	
	/**
	 * 接口，设置默认的收货地址
	 * @return [type] [description]
	 */
	public function default_address(){
		$return = get_standard_return();

		if(!isset($_POST['address_id']) || empty($_POST['address_id'])){
			$return['code'] = 0;
			$return['resultText']['message'] = "请传入地址id";
			echo  json_encode($return);
			exit;
		}

		$address_model = D('Address');
		$condition['address_id'] = $_POST['address_id'];
		$record = $address_model->where($condition)->find();
		if(!$record){
			$return['code'] = 0;
			$return['resultText']['message'] = "找不到地址信息";
			echo  encode_json($return);
			exit;
		}

		$update['is_default'] = 0;
		$update_result = $address_model->where(array('member_id' => $_SESSION['member_id']))->save($update);

		$update_result2 = $address_model->where($condition)->save(array('is_default' => "1"));
		if($update_result2 === false){
			$return['code'] = 0;
			$return['resultText']['message'] = "更新数据库失败";
			echo  json_encode($return);	
			exit;
		}

		echo  json_encode($return);
		exit;
	}
	
	//收藏商品
	public function favGoods()
	{
		//echo $_GET['p'];
		if(isset($_GET['p'])){$pageget=$_GET['p'];}else{$pageget=0;}
		//print_r($_GET);exit;
		//$res=$this->User->myCollectGoods($this->uid);
		//print_r($res);
		//分类参数，筛选结果列表
		$cat_id = $_GET['cat_id'];
		$show_type = 'favorites_goods_picshowlist';//默认为图片横向显示
		$show = $_GET['curpage'];
		$store_array = array('list'=>'favorites_goods_index','pic'=>'favorites_goods_picshowlist','store'=>'favorites_goods_shoplist');
		if (array_key_exists($show,$store_array)) $show_type = $store_array[$show];

		
		$tmp = $favorites_list = $this->User->getGoodsFavoritesList(array('member_id'=>$this->uid, 'tesu_deleted'=>0), 'fav_id');
		//print_r($tmp);exit;
		foreach ($tmp as $key => $value) {
			$id[] = $value['fav_id'];
		}
		
		$cat_array =$this->User->goodsidToGoodsClass($id);
		//print_r($cat_array);exit;

		//标记当前选中的分类
		if(!$cat_id){
			$cat_array['is_all_category'] = true;
		}else{
			$cat_array['is_all_category'] = false;
		}

		foreach ($cat_array['data'] as $key => $val1) {
			if($val1['gc_id'] == $cat_id) {
				$cat_array['data'][$key]['current'] = true;
			}else{
				$cat_array['data'][$key]['current'] = false;
			}
		}
		//查询收藏商品
		if(!isset($cat_id) || empty($cat_id)){
			$favorites_list = $this->User->getGoodsFavoritesList(array('member_id'=>$this->uid,'tesu_deleted'=>0), '*', $pageget);
		}else{
			$favorites_list = $this->User->getGoodsFavoritesList(array('member_id'=>$this->uid,'tesu_deleted'=>0),'fav_time desc','*', $pageget,$cat_id);
		}
		$count=M('Favorites')->where("fav_type='goods' and member_id=".$this->uid)->count();
		//echo $count;exit;
		$show= getPage($count,$pagesize=8);// 分页显示输出
		$this->assign('show',$show);// 赋值分页输出
		$this->assign('page',$show);
	
		if (!empty($favorites_list) && is_array($favorites_list)){
			$favorites_id = array();//收藏的商品编号
			foreach ($favorites_list as $key=>$favorites){
				$fav_id = $favorites['fav_id'];
				$favorites_id[] = $favorites['fav_id'];
				$favorites_key[$fav_id] = $key;
			}
			
			$field = 'goods.goods_id,goods.goods_name,goods.goods_state,goods.store_id,goods.goods_image,goods.goods_price,goods.evaluation_count,goods.goods_salenum,goods.goods_collect,store.store_name,store.member_id,store.member_name,store.store_qq,store.store_ww,store.store_domain';
			$goods_list = $this->User->getGoodsStoreList(array('goods_id' => array('in', $favorites_id)), $field);
			//print_r($goods_list);exit;
			$store_array = array();//店铺编号

			if (!empty($goods_list) && is_array($goods_list)){
				$store_goods_list = array();//店铺为分组的商品
				foreach ($goods_list as $key=>$fav){
					$fav_id = $fav['goods_id'];
					$fav['goods_member_id'] = $fav['member_id'];
					$key = $favorites_key[$fav_id];
					$favorites_list[$key]['goods'] = $fav;
					$store_id = $fav['store_id'];
					if (!in_array($store_id,$store_array)) $store_array[] = $store_id;
					$store_goods_list[$store_id][] = $favorites_list[$key];
				}
			}
			$store_favorites = array();//店铺收藏信息
		
		}
		
		$this->assign('favorites_list',$favorites_list);
		$this->assign('store_favorites',$store_favorites);
		$this->assign('store_goods_list',$store_goods_list);
		$this->assign('menu_sign','collect_list');
		$this->assign('cat_array',$cat_array);
		$this->display(); // 输出模板
		
	}

	/**
	 * 取消/收藏店铺
	 * @return [type] [description]
	 */
	public function favor_store(){

	    $favorites_model = D('Favorites');
//		$_POST['type']='goods';
//		$_POST['fav_id']='839';
	    if ($_POST) {
	        if (!$_POST['fav_id'] || !$_POST['type']){
				echo json_encode(array('resultText' => array('message' => '参数错误'), 'code' => 0));
	            die();
	        }
	        if (!preg_match_all('/^[0-9,]+$/',$_POST['fav_id'], $matches)) {
				echo json_encode(array('resultText' => array('message' => '参数错误'), 'code' => 0));
	            die();
	        }
	        $fav_id = trim($_POST['fav_id'],',');
	        if (!in_array($_POST['type'], array('goods', 'store'))) {
				echo json_encode(array('resultText' => array('message' => '参数错误'), 'code' => 0));
	            die();
	        }
	        $type = $_POST['type'];
	        $fav_arr = explode(',', $fav_id);
	        
	        if (!empty($fav_arr)){
	            $fav_str = "'".implode("','",$fav_arr)."'";
                $condition['where'] = " fav_id in (".$fav_id.')';
                $result = $favorites_model->delete($condition);

				//print_r($result);exit;
				if ($result){
	                //剔除删除失败的记录
	                $favorites_list = $favorites_model->getFavoritesList(array('fav_id'=>array('in', $fav_arr),'fav_type'=>"$type",'member_id'=>$_SESSION['member_id']));
	                if (!empty($favorites_list)){
	                    foreach ($favorites_list as $k=>$v){
	                        unset($fav_arr[array_search($v['fav_id'],$fav_arr)]);
	                    }
	                }
					//print_r($fav_arr);exit;
					if ($type=='goods'){
						//更新收藏数量
						$goods_model = D('Goods');
						if(!empty($fav_arr)){
						@$goods_model->editGoods(array('goods_collect'=>array('exp', 'goods_collect - 1')), array('goods_id' => array('in', $fav_arr)));
						}
						}else {
						$fav_str = "'".implode("','",$fav_arr)."'";
						//更新收藏数量
						$store_model = D('Store');
						if(!empty($fav_arr)){
							$store_model->editStore(array('store_collect'=>array('exp', 'store_collect - 1')),array('store_id'=>array('in', $fav_arr)));//$fav_str
							}
						}
					echo json_encode(array('resultText' => array('message' => '删除成功'), 'code' => 1));
					die();

	            }else {
					echo json_encode(array('resultText' => array('message' => '删除失败'), 'code' => 0));
	                die();
	            }
	        
	        }else {
				echo json_encode(array('resultText' => array('message' => '参数错误'), 'code' => 0));
                die();
	        }
	        
	    }else{
	        $condition['member_id'] = $_SESSION['member_id'];
	        if (isset($_GET['sc_id'])) {
	            $condition['sc_id'] = $_GET['sc_id'];
	        }
	        
	        //分类信息
	        $sc_list = D('Store_class')->getClassList(array('sc_parent_id' => 0));
	        $this->assign('sc_list', $sc_list);
	         
	        $sc_id = array();
	        if (!empty($sc_list)) {
	            foreach ($sc_list as $sc){
	                $sc_id[] = $sc['sc_id'];
	            }
	        }
	        
	        $field = 'store.store_id,store.store_name,store.store_collect,store.store_collect,store.praise_rate,store.store_label,store.sc_id,favorites.fav_id';
	        $favorites_list = $favorites_model->getStoreFavoritesByScidList($condition, $field, C('record_per_page'));
	         
	        //Tpl::output('show_page', $favorites_model->showpage(8));

			$sc_count = array();
	        if (!empty($favorites_list)){
	            $store_model = Model('store');
	            $model_goods = Model('goods');
	            $gc_model = Model('store_class');
	            $fieldstr = 'goods_id,goods_name,goods_image,goods_price';
	            foreach ($favorites_list as $key => $val){
	                /*$favorites_list[$key]['store_hot'] = $store_model->getHotSalesList($val['fav_id'], 4);
					$favorites_list[$key]['store_hot_total'] = $store_model->getHotSalesList($val['fav_id'], 100000, 1);
	                $favorites_list[$key]['store_new'] = $model_goods->getGoodsListByColorDistinct(array('store_id' => $val['fav_id']), $fieldstr, 'goods_id desc', 4);
					$favorites_list[$key]['store_new_total'] = $model_goods->getGoodsListByColorDistinct(array('store_id' => $val['fav_id']), $fieldstr, 'goods_id desc', 100000, 1);*/

					$hot_sales_list = $store_model->getHotSalesList($val['fav_id']);
					$favorites_list[$key]['store_hot'] = array_slice($hot_sales_list, 0, 4);
					$favorites_list[$key]['store_hot_total'] = count($hot_sales_list);
					$store_new_list = $model_goods->getGoodsListByColorDistinct(array('store_id' => $val['fav_id']), $fieldstr, 'goods_id desc');
					$favorites_list[$key]['store_new'] = array_slice($store_new_list, 0, 4);
					$favorites_list[$key]['store_new_total'] = count($store_new_list);

	                if (!empty($sc_id)) {
	                    if (!in_array($val['sc_id'], $sc_id)) {
	                        $sc_info = $gc_model->getOneClass($val['sc_id']);
	                        if ($sc_info != false) {
	                            $sc_count[$sc_info['sc_parent_id']] += 1;
	                        }
	                    }else {
	                        $sc_count[$val['sc_id']] += 1;
	                    }
	                }
	            }
	        }

	        if (!empty($sc_count)) {
	            $this->assign('sc_count', $sc_count);
	        }
			$this->assign('sc_sum_count', count($favorites_list));
	        $this->assign('favorites_list', $favorites_list);
	        $this->display('member.favor_store');
	    }
	}
	
	//收藏店铺
	public function favStore()
	{
		
	    if ($_POST) {
	        if (!$_POST['fav_id'] || !$_POST['type']){
				echo json_encode(array('resultText' => array('message' => '参数错误'), 'code' => 0));
	            die();
	        }
	        if (!preg_match_all('/^[0-9,]+$/',$_POST['fav_id'], $matches)) {
				echo json_encode(array('resultText' => array('message' => '参数错误'), 'code' => 0));
	            die();
	        }
	        $fav_id = trim($_POST['fav_id'],',');
	        if (!in_array($_POST['type'], array('goods', 'store'))) {
				echo json_encode(array('resultText' => array('message' => '参数错误'), 'code' => 0));
	            die();
	        }
	        $type = $_POST['type'];
	        $fav_arr = explode(',', $fav_id);
	        
	        if (!empty($fav_arr)){
	            $fav_str = "'".implode("','",$fav_arr)."'";
				$result = $this->User->updateFavoritesNum('favorites', array('tesu_deleted' => 1), array('fav_id_in'=>$fav_str,'fav_type'=>$type,'member_id'=>$this->uid));

				if ($result){
	                //剔除删除失败的记录
	                $favorites_list = $this->User->getFavoritesList(array('fav_id'=>array('in', $fav_arr),'fav_type'=>$type,'member_id'=>$this->uid));
	                if (!empty($favorites_list)){
	                    foreach ($favorites_list as $k=>$v){
	                        unset($fav_arr[array_search($v['fav_id'],$fav_arr)]);
	                    }
	                }

					if ($type=='goods'){
						//更新收藏数量
						
						$this->User->editGoods(array('goods_collect'=>array('exp', 'goods_collect - 1')), array('goods_id' => array('in', $fav_arr)));
					}else {
						$fav_str = "'".implode("','",$fav_arr)."'";
						//更新收藏数量
						$store_model = Model('store');
						$this->User->editStore(array('store_collect'=>array('exp', 'store_collect - 1')),array('store_id'=>array('in', $fav_arr)));//$fav_str
					}
					echo json_encode(array('resultText' => array('message' => '删除成功'), 'code' => 1));
					die();

	            }else {
					echo json_encode(array('resultText' => array('message' => '删除失败'), 'code' => 0));
	                die();
	            }
	        
	        }else {
				echo json_encode(array('resultText' => array('message' => '参数错误'), 'code' => 0));
                die();
	        }
	        
	    }else{
	        $condition['member_id'] = $this->uid;
	        if (isset($_GET['sc_id'])) {
	            $condition['sc_id'] = $_GET['sc_id'];
	        }
	        
	        //分类信息
	        $sc_list = $this->User->getClassList(array('sc_parent_id' => 0));
	        $this->assign('sc_list', $sc_list);
	        //print_r($sc_list);exit;
	        $sc_id = array();
	        if (!empty($sc_list)) {
	            foreach ($sc_list as $sc){
	                $sc_id[] = $sc['sc_id'];
	            }
	        }
	        
	        $field = 'store.store_id,store.store_name,store.store_collect,store.store_collect,store.praise_rate,store.store_label,store.sc_id,favorites.fav_id';
	        $favorites_list = $this->User->getStoreFavoritesByScidList($condition, $field, C('record_per_page'));
	         
	        //Tpl::output('show_page', $favorites_model->showpage(8));
			
			$sc_count = array();
	        if (!empty($favorites_list)){
	            $fieldstr = 'goods_id,goods_name,goods_image,goods_price';
	            foreach ($favorites_list as $key => $val){
	                /*$favorites_list[$key]['store_hot'] = $store_model->getHotSalesList($val['fav_id'], 4);
					$favorites_list[$key]['store_hot_total'] = $store_model->getHotSalesList($val['fav_id'], 100000, 1);
	                $favorites_list[$key]['store_new'] = $model_goods->getGoodsListByColorDistinct(array('store_id' => $val['fav_id']), $fieldstr, 'goods_id desc', 4);
					$favorites_list[$key]['store_new_total'] = $model_goods->getGoodsListByColorDistinct(array('store_id' => $val['fav_id']), $fieldstr, 'goods_id desc', 100000, 1);*/

					$hot_sales_list = $this->User->getHotSalesList($val['fav_id']);
					$favorites_list[$key]['store_hot'] = array_slice($hot_sales_list, 0, 4);
					$favorites_list[$key]['store_hot_total'] = count($hot_sales_list);
					$store_new_list = $this->User->getGoodsListByColorDistinct(array('store_id' => $val['fav_id']), $fieldstr, 'goods_id desc');
					$favorites_list[$key]['store_new'] = array_slice($store_new_list, 0, 4);
					$favorites_list[$key]['store_new_total'] = count($store_new_list);

	                if (!empty($sc_id)) {
	                    if (!in_array($val['sc_id'], $sc_id)) {
	                        $sc_info = $this->User->getOneClass($val['sc_id']);
	                        if ($sc_info != false) {
	                            $sc_count[$sc_info['sc_parent_id']] += 1;
	                        }
	                    }else {
	                        $sc_count[$val['sc_id']] += 1;
	                    }
	                }
	            }
	        }
			//print_r($favorites_list);exit;
	        if (!empty($sc_count)) {
				$data['sc_count']=$sc_count;
	        }
			$data['sc_sum_count']=count($favorites_list);
			$data['favorites_list']=$favorites_list;
			$this->assign('output',$data);
			$this->display();
			
	    }
	}
	
	//浏览记录
	public function browseHistory()
	{
		$model_goods = D('Goods');
	    $viewed_goods = $model_goods->getViewedGoodsList();
//		$viewed_goods=array(
//  0 => array
//      (
//          'goods_id' => 839,
//          'goods_name' => '实木餐桌 可伸缩折叠餐桌',
//          'goods_state' => 1,
//          'goods_image' => '34_05169879241377980.png',
//          'goods_price' => 1599.00,
//          'store_id' => 34,
//          'gc_id' => 1354
//      )
//
//);
 		//print_r($viewed_goods);exit;
	    $gc_ids=array();
	    foreach ($viewed_goods as $key=>$val){
	        $gc_ids[]=$val['gc_id'];
	    }
	    //通过一组分类id查询相关分类名称
	    $goods_class=D("Goods_class");
	    $condition_class['gc_id'] = array('in', implode(',', array_unique($gc_ids)));
	    $category = $goods_class->findArrClass($condition_class);
	   
        //存在筛选条件,将商品进行筛选
        $gc_count = array();
        foreach ($viewed_goods as $key => $val){
            $gc_count[$val['gc_id']] += 1; 
            if(isset($_GET['gc_id'])){
                if($val['gc_id'] != $_GET['gc_id']){
                    unset($viewed_goods[$key]);
                }
            }
        }

        //给分类增加超链接
	    foreach ($category as $key1 => $val2) {
	        //$category[$key1]['url'] = urlshop('member', 'view_history', array("gc_id" => $val2['gc_id']));
	        $category[$key1]['url'] = "/index.php?m=shop&c=member&a=browseHistory&gc_id=".$val2['gc_id'];
	        $category[$key1]['count'] = $gc_count[$val2['gc_id']];
	    }
	    $data['gc']=$category;
		$data['menu_sign']='goodsbrowse';
		$data['viewed_goods']=unique_arr($viewed_goods);
	    $this->assign('output',$data);
		$this->display('viewhistory');
	}
	

	/**
     * 增加商品收藏
     */
    public function favoritesgoods(){
        $fav_id = intval($_GET['fid'])?intval($_GET['fid']):intval($_POST['fid']);
        if ($fav_id <= 0){
            $this->jsonFail('找不到商品');
        }
        $favorites_model = Model('favorites');
        //判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array('fav_id'=>"$fav_id",'fav_type'=>'goods','member_id'=>"{$_SESSION['member_id']}"));
        if(!empty($favorites_info)){
            $this->jsonFail('已经收藏过该商品', 'JSON');
        }
        //判断商品是否为当前会员所有
        $goods_model = Model('goods');
        $goods_info = $goods_model->getGoodsInfo(array('goods_id' => $fav_id));
        if ($goods_info['store_id'] == $_SESSION['store_id']){
            $this->jsonFail('不能收藏自己的商品', 'JSON');   
        }
        //添加收藏
        $insert_arr = array();
        $insert_arr['member_id'] = $_SESSION['member_id'];
        $insert_arr['fav_id'] = $fav_id;
        $insert_arr['fav_type'] = 'goods';
        $insert_arr['fav_time'] = time();
        $result = $favorites_model->addFavorites($insert_arr);
        if ($result){
            //增加收藏数量
            $goods_model->editGoods(array('goods_collect' => array('exp', 'goods_collect + 1')), array('goods_id' => $fav_id));
            $this->jsonSucc('收藏成功!', 'JSON');
        }else{
            $this->jsonSucc('收藏失败!', 'JSON');
        }
    }


    /**
     * 增加店铺收藏
     */
                    
    public function favoritesstore(){
        $fav_id = intval($_GET['fid']);
        if ($fav_id <= 0){
            $this->jsonFail('找不到店铺');
        }
        $favorites_model = Model('favorites');
        //判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array('fav_id'=>"$fav_id",'fav_type'=>'store','member_id'=>"{$_SESSION['member_id']}"));
        if(!empty($favorites_info)){
            $this->jsonFail('已经收藏过该店铺');
        }
        //判断店铺是否为当前会员所有
        if ($fav_id == $_SESSION['store_id']){
            $this->jsonFail('不能收藏自己的店铺');
        }
        //添加收藏
        $insert_arr = array();
        $insert_arr['member_id'] = $_SESSION['member_id'];
        $insert_arr['fav_id'] = $fav_id;
        $insert_arr['fav_type'] = 'store';
        $insert_arr['fav_time'] = time();
        $result = $favorites_model->addFavorites($insert_arr);
        if ($result){
            //增加收藏数量
            $store_model = Model('store');
            $store_model->editStore(array('store_collect'=>array('exp', 'store_collect+1')), array('store_id' => $fav_id));
            $this->jsonSucc('收藏成功');
        }else{
            $this->jsonSucc('收藏失败');
        }
    }
	

    /*
     * 修改登录密码弹窗
     */
    public function tplChangePwd(){
        $this->display();
    }
	
	//修改密码
	public function savePwd(){
		
		/**
		 * 填写密码信息验证
		 */
//		$_POST['orig_password']='abc123';
//		$_POST['new_password']='abc123456';
//		$_POST['confirm_password']='abc123456';
		
		if (trim($_POST["new_password"]) != trim($_POST["confirm_password"])){
			echo json_encode(array('resultText' => array('message' => '两次输入密码不一致'), 'code' => 0));
			die();
		}

		$api = API('user');
		$userinfo['user_name'] = $_SESSION['mobile'];
		$find_user_id = json_decode($api->getUserId($userinfo), true);
		
		$user_id = $find_user_id['code'] > 0 ? $find_user_id['code'] : 0;

		if (!$user_id){
			echo json_encode(array('resultText' => array('message' => '用户信息获取失败'), 'code' => 0));
			die();
		}
		$userinfo = $member_info = array();
		$userinfo['usr_id'] = $user_id;
		$java_member_info = json_decode($api->getUserInfo($userinfo), true);
		if ($java_member_info['code'] == 0){
			$member_info = json_decode($java_member_info['resultText'], true);
		}

		if (empty($member_info)){
			echo json_encode(array('resultText' => array('message' => '用户信息获取失败'), 'code' => 0));
			die();
		}

		if ($member_info['user_pass'] != md5($_POST["orig_password"])){
			echo json_encode(array('resultText' => array('message' => '原始密码不正确'), 'code' => 0));
			die();
		}
		
		$userinfo['usr_id'] = $user_id;
		$userinfo['user_name'] = $member_info['user_name'];
		$userinfo['user_pass'] = md5(trim($_POST['orig_password']));//老密码
		$userinfo['user_pass_new'] = md5(trim($_POST['confirm_password']));//新密码
		$userinfo['is_checkoldpass'] = 1;
		$change_pwd = json_decode($api->changePassword($userinfo), true);

		if ($change_pwd['code'] == 0){
			//$save['member_id'] = $_SESSION['member_id'];
			$save['member_passwd'] = md5(trim($_POST['new_password']));
		}
		
		
		
		$update	= M('Member')->where('member_id='.$_SESSION['member_id'])->save($save);
		if($update) {
			echo json_encode(array('resultText' => array('message' => '修改成功'), 'code' => 1));
		} else {
			echo json_encode(array('resultText' => array('message' => '修改失败'), 'code' => 0));
		}
	}

    /*
     * 修改地址弹窗
     */
    public function tplChangeAddress(){
    	//判断是否登录，如果没有登录
		//跳转到登录界面
		
		$address_id = $_GET['address_id'];
		$address_model = D('Address');
		$record = $address_model->where(array('address_id' => $address_id))->find();
		if(!$record){
			showDialog('地址信息不存在!');
		}
		list($city_code, $tel ) = explode('-', $record['tel_phone']);
		$record['city_code'] = $city_code;
		$record['tel'] = $tel;
		$data['address_info']=$record;
		$this->assign('output', $data);
        $this->display();
    }

	/**
	 * 接口，编辑数据
	 * address_id
	 * @return [type] [description]
	 */
	public function update_address(){
//		$_POST['addree_id'] = 163;	
//		$_POST['province_id'] = 3;	
//		$_POST['city_id'] = 74;	
//		$_POST['area_id'] = 1151;	
//		$_POST['address'] = '发送的发生';	
//		$_POST['zip_code'] = 123456;
//		$_POST['true_name'] = '发顺丰';
//		$_POST['mob_phone'] = 123456;
//		$_POST['area_info'] = '河北省唐山市乐亭县';
//		$_POST['tel_phone'] = '111-11111';	
//		$_POST['is_default'] = 0;
		
		
		$_POST['address_id'] = $_POST['addree_id'];
		$_POST['province_id']= $_POST['province_id'];
		//初始化返回数据
		$return = get_standard_return();

		$address_class = D('Address');
		$address_id = $_POST['address_id'];
		$condition['address_id'] = $address_id;
		$condition['member_id'] = $_SESSION['member_id'];
		$record = $address_class->where($condition)->find();
		if(!$record){
			//记录不存在
			$return['code'] = 0;
			$return['resultText']['message'] = "地址信息不存在!";
			echo  json_encode($return);
		}
		
		//验证地址信息
//		$error = $this->_validate_address();
//		if ($error != ''){
//			$return['code'] = 0;
//			$return['resultText']['message'] = $error;
//			//返回json,数据验证错误
//			echo json_encode($return);
//			exit;
//		}

        $data = array();
		// $data['member_id']  = $_SESSION['member_id'];
		$data['true_name']  = $_POST['true_name'];
		$data['area_id']    = intval($_POST['area_id']);
		$data['city_id']    = intval($_POST['city_id']);
		$data['area_info']  = $_POST['area_info'];
		$data['province_id'] = intval($_POST['province_id']);
		$data['zip_code'] = $_POST['zip_code'];
		$data['address']    = $_POST['address'];
		$data['tel_phone']  = $_POST['tel_phone'];
		$data['mob_phone']  = $_POST['mob_phone'];
		$data['is_default'] = $_POST['is_default'];

		
		//如果默认地址为空，重置其它默认地址
		if($_POST['is_default']){
			$address_class->undefaultAll();
		}
		$result = $address_class->editAddress($data, array('address_id' => $_POST['address_id']));
		if($result === false){
			//失败，数据库写入失败
			$return['code'] = 0;
			$return['resultText']['message'] = "数据库写入失败";
		}else{
			$return['resultText']['address_id'] = $_POST['address_id'];
		}

		echo json_encode($return);
	}

    /*
     * 取消订单接口
     */
    public function ajaxCancelOrder(){

	    $order_id = intval($_POST['order_id']);
	    $extend_msg = $_POST['state_info'];

	    if ($order_id < 0 || empty($extend_msg)){
		    $result = array(array('resultText' => array('message' => '没有订单号或没有选择原因'), 'code' => 0));
	    }else{
		    $model_order = D('Order');

		    $condition = array();
		    $condition['order_id'] = $order_id;
		    $condition['buyer_id'] = $_SESSION['member_id'];
		    $order_info	= $model_order->getOrderInfo($condition);

		    $result = $model_order->memberChangeState('order_cancel', $order_info, $_SESSION['member_id'], $_SESSION['member_name'], $extend_msg);
		    if(empty($result['error'])) {
			    $result = array('resultText' => array('message' => $result['success']), 'code' => 1);
		    } else {
			    $result = array('resultText' => array('message' => $result['error']), 'code' => 0);
		    }
	    }
	    $this->ajaxReturn($result);
    }

    /**
     * 特速分期
     */
    public function tesufinance(){
    	$pagenum = I('get.p')==null?0:I('get.p');
    	$model=M("order");
        $list=$model->field("add_time,order_sn,order_amount_delta,period,order_type")
                    ->where(array("order_type"=>array('in','2,3'),"buyer_id"=>$_SESSION["member_id"]))
                    ->order("add_time desc")
                    ->page($pagenum.',10')
                    ->select();//查询出分期购的订单

        $totalrepay=0;//总还款金额;
        if(is_array($list) && count($list)){
            foreach ($list as $value){
                $value["finnshed_time"]=date("Y-m-d",$value["finnshed_time"]);
                $arr[]=$value;
                $totalrepay+=$value["order_amount_delta"];
            }
            unset($list);
            $this->assign("orderlist",$arr);
        }
        $count=$model->where(array("order_type"=>array('in','2,3'),"buyer_id"=>$_SESSION["member_id"]))->count();
        $Page = new \Think\Page($count,10);
		$show = $Page->show();
        $this->assign("totalrepay",$totalrepay);
        $this->assign("show_page",$show);
    	$this->display();
    }


	public function supply_message(){
		$order_id = intval($_POST['order_id']);
		$order_message = trim($_POST['order_message']);
		if ($order_id < 0 || empty($order_message)){
			$result = array('resultText' => array('message' => '没有订单号或留言内容'), 'code' => 0);
		}else{
			$model_order = D('Order');
			$data['order_message'] = $order_message;
			$condition['order_id'] = $order_id;
			$status = $model_order->editOrderCommon($data, $condition);
			if ($status != false){
				$result = array('resultText' => array('message' => '操作成功'), 'code' => 1);
			}else{
				$result = array('resultText' => array('message' => '操作失败'), 'code' => 0);
			}
		}
		echo json_encode($result);
	}
	
   
    
}