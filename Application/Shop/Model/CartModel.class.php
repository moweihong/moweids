<?php
namespace Shop\Model;
use Think\Model;
class CartModel extends Model {

	protected $tableName = 'cart';  
    /**
     * 购物车商品总金额
     */
    private $cart_all_price = 0;

    /**
     * 购物车商品总数
     */
    private $cart_goods_num = 0;

    public function __construct() {
        parent::__construct();
    }

    /**
     * 取属性值魔术方法
     *
     * @param string $name
     */
    public function __get($name) {
        return $this->$name;
    }

	/**
	 * 检查购物车内商品是否存在
	 *
	 * @param
	 */
	public function checkCart($condition = array()) {
	    return $this->where($condition)->find();
	}
	
	/**
	 * 取得 单条购物车信息
	 * @param unknown $condition
	 * @param string $field
	 */
	public function getCartInfo($condition = array(), $field = '*') {
	   return $this->field($field)->where($condition)->find();    
	}

	/**
	 * 将商品添加到购物车中
	 *
	 * @param array	$data	商品数据信息
	 * @param string $save_type 保存类型，可选值 db,cookie,cache
	 * @param int $quantity 购物数量
	 */	
	public function addCart($data = array(), $save_type = '', $quantity = null) {
        $method = '_addCart'.ucfirst($save_type);
	    $insert = $this->$method($data,$quantity);
	    //更改购物车总商品数和总金额，传递数组参数只是给DB使用
	    $this->getCartNum($save_type,array('buyer_id'=>$data['buyer_id']));
	    return $insert;
	}

	/**
	 * 添加数据库购物车
	 *
	 * @param unknown_type $goods_info
	 * @param unknown_type $quantity
	 * @return unknown
	 */
	private function _addCartDb($goods_info = array(),$quantity) {
	    //验证购物车商品是否已经存在
	    $condition = array();
	    $condition['goods_id'] = $goods_info['goods_id'];
	    $condition['buyer_id'] = $goods_info['buyer_id'];
	    if (isset($goods_info['bl_id'])) {
	        $condition['bl_id'] = $goods_info['bl_id'];   
	    } else {
	        $condition['bl_id'] = 0;
	    }
    	$check_cart	= $this->checkCart($condition);
        
    	if (!empty($check_cart)){

			//更新商品数量
			$cart_id=$check_cart['cart_id'];
			$old_num=$check_cart['goods_num'];
			$new_num=$old_num+$quantity;
                
			$data['goods_num']=$new_num;
			$condition['cart_id']=$cart_id;
			//计算是否超过库存,如果超过库存则终止
			//获取商品库存数
			$goods_modle=D('Goods');

			$goods_res=$goods_modle->getGoodsInfoByID($check_cart['goods_id'],'goods_storage');
            
			$goods_storage=$goods_res['goods_storage'];

			if($new_num>$goods_storage){
				return 'limit_max';
			}
			$res=$this->editCart($data,$condition);

			if($res){
                return true;
            }else{
                return false;    
            }
		}

		$array    = array();
		$array['buyer_id']	= $goods_info['buyer_id'];
		$array['store_id']	= $goods_info['store_id'];
		$array['goods_id']	= $goods_info['goods_id'];
		$array['goods_name'] = $goods_info['goods_name'];
		$array['goods_price'] = $goods_info['goods_price'];
		$array['goods_num']   = $quantity;
		$array['goods_image'] = $goods_info['goods_image'];
		$array['store_name'] = $goods_info['store_name'];
		$array['bl_id'] = isset($goods_info['bl_id']) ? $goods_info['bl_id'] : 0;
        
		return $this->add($array);
	}

	/**
	 * 添加到缓存购物车
	 *
	 * @param unknown_type $goods_info
	 * @param unknown_type $quantity
	 * @return unknown
	 */
//	private function _addCartCache($goods_info = array(), $quantity = null) {
//        $obj_cache = Cache::getInstance(C('cache.type'));
//        $cart_array = $obj_cache->get($_COOKIE['PHPSESSID'],'cart_');
//        $cart_array = @unserialize($cart_array);
//    	$cart_array = !is_array($cart_array) ? array() : $cart_array;
//    	if (count($cart_array) >= 5) return true;
//        if (in_array($goods_info['goods_id'],array_keys($cart_array))) return true;
//		$cart_array[$goods_info['goods_id']] = array(
//		  'store_id' => $goods_info['store_id'],
//		  'goods_id' => $goods_info['goods_id'],
//		  'goods_name' => $goods_info['goods_name'],
//		  'goods_price' => $goods_info['goods_price'],
//		  'goods_image' => $goods_info['goods_image'],
//		  'goods_num' => $quantity
//		);
//        $obj_cache->set($_COOKIE['PHPSESSID'], serialize($cart_array), 'cart_', 24*3600);
//        return true;
//	}

	/**
	 * 添加到cookie购物车,最多保存5个商品
	 *
	 * @param unknown_type $goods_info
	 * @param unknown_type $quantity
	 * @return unknown
	 */
//	private function _addCartCookie($goods_info = array(), $quantity = null) {
//    	//去除斜杠
//    	$cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('cart')) : cookie('cart');
//    	$cart_str = base64_decode(decrypt($cart_str));
//    	$cart_array = @unserialize($cart_str);
//    	$cart_array = !is_array($cart_array) ? array() : $cart_array;
//    	if (count($cart_array) >= 5) return false;
//
//    	if (in_array($goods_info['goods_id'],array_keys($cart_array))){
//			//更新商品数量
//			$old_num=$cart_array[$goods_info['goods_id']]['goods_num'];
//			$new_num=$old_num+$quantity;
//			$data['goods_num']=$new_num;
//			//计算是否超过库存,如果超过库存则终止
//			//获取商品库存数
//			$goods_modle=D('Goods');
//			$goods_res=$goods_modle->getGoodsInfoByID($cart_array[$goods_info['goods_id']]['goods_id'],'goods_storage');
//			$goods_storage=$goods_res['goods_storage'];
//			if($new_num>$goods_storage){
//				return false;
//			}
//			//更新cookie中的数量
//			$cart_array[$goods_info['goods_id']]['goods_num']=$new_num;
//			cookie('cart',encrypt(base64_encode(serialize($cart_array))),24*3600);
//			return true;
//
//		}
//		$cart_array[$goods_info['goods_id']] = array(
//		  'store_id' => $goods_info['store_id'],
//		  'goods_id' => $goods_info['goods_id'],
//		  'goods_name' => $goods_info['goods_name'],
//		  'goods_price' => $goods_info['goods_price'],
//		  'goods_image' => $goods_info['goods_image'],
//		  'goods_num' => $quantity
//		);
//		cookie('cart',encrypt(base64_encode(serialize($cart_array))),24*3600);
//		return true;
//	}

	/**
	 * 更新购物车 
	 *
	 * @param	array	$param 商品信息
	 */	
	public function editCart($data,$condition) {
		$result	= $this->where($condition)->save($data);
		if ($result !== false) {
		    $this->getCartNum('db',array('buyer_id'=>$condition['buyer_id']));
		}
		return $result;
	}

	/**
	 * 购物车列表 
	 *
	 * @param string $type 存储类型 db,cache,cookie
	 * @param unknown_type $condition
	 */	
	public function listCart($type, $condition = array()) {
        //print_r($this);exit;	
       
		//$model=M('Cart');
        if ($type == 'db') {
    		$cart_list = $this->where($condition)->select();
        }
//        elseif ($type == 'cache') {
//            $obj_cache = Cache::getInstance(C('cache.type'));
//            $cart_list = $obj_cache->get($_COOKIE['PHPSESSID'],'cart_');
//            $cart_list = @unserialize($cart_list);
//        } elseif ($type == 'cookie') {
//        	//去除斜杠
//        	$cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('cart')) : cookie('cart');
//        	$cart_str = base64_decode(decrypt($cart_str));
//        	$cart_list = @unserialize($cart_str);
//        }
        $cart_list = is_array($cart_list) ? $cart_list : array();
        //顺便设置购物车商品数和总金额
		$this->cart_goods_num =  count($cart_list);
	    $cart_all_price = 0;

		if(is_array($cart_list)) {
			foreach ($cart_list as $key => $val) {
				$cart_all_price	+= $val['goods_price'] * $val['goods_num'];

				//cookie 下补充完整商家名称
				$store_model = M('Store');
				$record = $store_model->where(array('store_id' => $val['store_id']))->find();
				if(is_null($cart_list[$key]['store_name'])){
					$cart_list[$key]['store_name'] = $record['store_name'];
                    //加入是否贴息字段
                    $cart_list[$key]['tiexi'] = $record['is_discount'];
				}
                if(is_null($cart_list[$key]['tiexi'])){
                    $cart_list[$key]['tiexi'] = $record['is_discount'];
                }
			}
		}
        $this->cart_all_price =ncPriceFormat($cart_all_price);
		return !is_array($cart_list) ? array() : $cart_list;
	}

	
	/**
	 * 删除购物车商品
	 * 
	 * @param string $type 存储类型 db,cache,cookie
	 * @param unknown_type $condition
	 */
	public function del($type, $condition = array(), $goods_id_list=null) {
	    if ($type == 'db') {
    		$result =  $this->where($condition)->delete();
	    }

	    //重新计算购物车商品数和总金额
		if ($result) {
		    $this->getCartNum($type,array('buyer_id'=>$condition['buyer_id']));
		}
		return $result;
	}

	/**
	 * 清空购物车
	 *
	 * @param string $type 存储类型 db,cache,cookie
	 * @param unknown_type $condition
	 */
	public function clearCart($type, $condition = array()) {
	    if ($type == 'cache') {
            $obj_cache = Cache::getInstance(C('cache.type'));
            $obj_cache->rm($_COOKIE['PHPSESSID'],'cart_');
	    } elseif ($type == 'cookie') {
            cookie('cart','',-3600);
	    } else if ($type == 'db') {
	        //数据库暂无浅清空操作
	    }
	}

	/**
	 * 计算购物车总商品数和总金额 
	 * @param string $type 购物车信息保存类型 db,cookie,cache
	 * @param array $condition 只有登录后操作购物车表时才会用到该参数
	 */		
	public function getCartNum($type, $condition = array()) {
	    if ($type == 'db') {
    	    $cart_all_price = 0;
    		$cart_goods	= $this->listCart('db',$condition);
    		$this->cart_goods_num = count($cart_goods);
    		if(!empty($cart_goods) && is_array($cart_goods)) {
    			foreach ($cart_goods as $val) {
    				$cart_all_price	+= $val['goods_price'] * $val['goods_num'];
    			}
    		}
		  $this->cart_all_price = ncPriceFormat($cart_all_price);
	        
	    }
//	    elseif ($type == 'cache') {
//            $obj_cache = Cache::getInstance(C('cache.type'));
//            $cart_array = $obj_cache->get($_COOKIE['PHPSESSID'],'cart_');
//            $cart_array = @unserialize($cart_array);
//        	$cart_array = !is_array($cart_array) ? array() : $cart_array;
//    		$this->cart_goods_num = count($cart_array);
//    		$cart_all_price = 0;
//    		if (!empty($cart_array)){
//    			foreach ($cart_array as $v){
//    				$cart_all_price += floatval($v['goods_price'])*intval($v['goods_num']);
//    			}
//    		}
//    		$this->cart_all_price = $cart_all_price;
//
//	    } elseif ($type == 'cookie') {
//        	$cart_str = get_magic_quotes_gpc() ? stripslashes(cookie('cart')) : cookie('cart');
//        	$cart_str = base64_decode(decrypt($cart_str));
//        	$cart_array = @unserialize($cart_str);
//        	$cart_array = !is_array($cart_array) ? array() : $cart_array;
//    		$this->cart_goods_num = count($cart_array);
//    		$cart_all_price = 0;
//    		foreach ($cart_array as $v){
//    			$cart_all_price += floatval($v['goods_price'])*intval($v['goods_num']);
//    		}
//    		$this->cart_all_price = $cart_all_price;
//	    }
	    cookie('cart_goods_num',$this->cart_goods_num,2*3600);
	    return $this->cart_goods_num;
	}

	/**
	 * 直接购买时返回最新的在售商品信息（需要在售）
	 *
	 * @param int $goods_id 所购商品ID
	 * @param int $quantity 购买数量
	 * @return array
	 */
	public function getGoodsOnlineInfo($goods_id,$quantity) {
	    //取目前在售商品
	    $goods_info = D('Goods')->getGoodsOnlineInfo(array('goods_id'=>$goods_id));
	    if(empty($goods_info)){
            return null;
	    }
	    $new_array = array();
	    $new_array['goods_num'] = $quantity;
	    $new_array['goods_id'] = $goods_id;
	    $new_array['goods_commonid'] = $goods_info['goods_commonid'];
	    $new_array['gc_id'] = $goods_info['gc_id'];
	    $new_array['store_id'] = $goods_info['store_id'];
	    $new_array['goods_name'] = $goods_info['goods_name'];
	    $new_array['goods_price'] = $goods_info['goods_price'];
	    $new_array['store_name'] = $goods_info['store_name'];
	    $new_array['goods_image'] = $goods_info['goods_image'];
	    $new_array['transport_id'] = $goods_info['transport_id'];
	    $new_array['goods_freight'] = $goods_info['goods_freight'];
	    $new_array['goods_vat'] = $goods_info['goods_vat'];
	    $new_array['goods_storage'] = $goods_info['goods_storage'];
	    $new_array['state'] = true;
	    $new_array['storage_state'] = intval($goods_info['goods_storage']) < intval($quantity) ? false : true;

	    //填充必要下标，方便后面统一使用购物车方法与模板
	    //cart_id=goods_id,优惠套装目前只能进购物车,不能立即购买
	    $new_array['cart_id'] = $goods_id;
	    $new_array['bl_id'] = 0;
	    return $new_array;
	}

	/**
	 * 取商品最新的在售信息
	 * @param unknown $cart_list
	 * @return array
	 */
	public function getOnlineCartList($cart_list) {
	    if (empty($cart_list) || !is_array($cart_list)) return $cart_list;
	    //验证商品是否有效
	    $goods_id_array = array();
	    foreach ($cart_list as $key => $cart_info) {
	        if (!intval($cart_info['bl_id'])) {
	            $goods_id_array[] = $cart_info['goods_id'];
	        }
	    }

	    $model_goods = D('Goods');
	    $goods_online_list = $model_goods->getGoodsList(array('goods_id'=>array(in,$goods_id_array)), '*', "", "", 0, "1,1000");
	    $goods_online_array = array();
	    foreach ($goods_online_list as $goods) {
	        $goods_online_array[$goods['goods_id']] = $goods;
	    }
	    foreach ((array)$cart_list as $key => $cart_info) {
	        $cart_list[$key]['state'] = true;
	        $cart_list[$key]['storage_state'] = true;
	        if (in_array($cart_info['goods_id'],array_keys($goods_online_array))) {
                $goods_online_info = $goods_online_array[$cart_info['goods_id']];
                $cart_list[$key]['goods_name'] = $goods_online_info['goods_name'];
                $cart_list[$key]['gc_id'] = $goods_online_info['gc_id'];
                $cart_list[$key]['goods_image'] = $goods_online_info['goods_image'];
                $cart_list[$key]['goods_price'] = $goods_online_info['goods_price'];
                $cart_list[$key]['transport_id'] = $goods_online_info['transport_id'];
                $cart_list[$key]['goods_freight'] = $goods_online_info['goods_freight'];
                $cart_list[$key]['goods_vat'] = $goods_online_info['goods_vat'];
                $cart_list[$key]['goods_storage'] = $goods_online_info['goods_storage'];
                if ($cart_info['goods_num'] > $goods_online_info['goods_storage']) {
                    $cart_list[$key]['storage_state'] = false;
                }
	        } else {
	            //如果商品下架
	            $cart_list[$key]['state'] = false;
	            $cart_list[$key]['storage_state'] = false;
	        }
	    }
	    return $cart_list;
	}

	/**
	 * 从购物车数组中得到商品列表
	 * @param unknown $cart_list
	 */
	public function getGoodsList($cart_list) {
	    if (empty($cart_list) || !is_array($cart_list)) return $cart_list;
	    $goods_list = array();
	    $i = 0;
	    foreach ($cart_list as $key => $cart) {
	        if (!$cart['state'] || !$cart['storage_state']) continue;
	        //购买数量
	        $quantity = $cart['goods_num'];

            //如果是普通商品
            $goods_list[$i]['goods_num'] = $quantity;
            $goods_list[$i]['goods_id'] = $cart['goods_id'];
            $goods_list[$i]['store_id'] = $cart['store_id'];
            $goods_list[$i]['gc_id'] = $cart['gc_id'];
            $goods_list[$i]['goods_name'] = $cart['goods_name'];
            $goods_list[$i]['goods_price'] = $cart['goods_price'];
            $goods_list[$i]['store_name'] = $cart['store_name'];
            $goods_list[$i]['goods_image'] = $cart['goods_image'];
            $goods_list[$i]['transport_id'] = $cart['transport_id'];
            $goods_list[$i]['goods_freight'] = $cart['goods_freight'];
            $goods_list[$i]['goods_vat'] = $cart['goods_vat'];
            $goods_list[$i]['bl_id'] = 0;
            $i++;
	    }
	    return $goods_list;
	}

	/**
	 * 将下单商品列表转换为以店铺ID为下标的数组
	 *
	 * @param array $cart_list
	 * @return array
	 */
	public function getStoreCartList($cart_list) {
	    if (empty($cart_list) || !is_array($cart_list)) return $cart_list;
	    $new_array = array();
	    foreach ($cart_list as $cart) {
	        $new_array[$cart['store_id']][] = $cart;
	    }
	    return $new_array;
	}

    /**
     * 获取商品对应的分期购信息
     * @param  [type] $goods_commonid [description]
     * @return [type]                 [description]
     */
    private function getEasypayInfo($where, $goods_price){
        $financialEasypayModel = D("financial_easypay");
        $easypayRecord = $financialEasypayModel->getFinancialEasypayInfo($where);
        if(!$easypayRecord){
            //找不到记录
            $easypay = array();
        }else{
            $easypay = unserialize($easypayRecord['note']);
            //计算本息和已经平摊到每一期的金额
            foreach ($easypay as $key => $value) {
                $interest = $goods_price * $value['rate'] * $value['duration'] / 1200;
                $easypay[$key]['total'] = number_format((float)($value['colligate'] + $goods_price + $interest), 2, '.', '');
                $tmp = $easypay[$key]['total'] *1.0/$value['duration'];
                $easypay[$key]['slice'] = number_format((float)$tmp, 2, '.', '');
            }
        }
        return $easypay;
    }


	/**
	 * 商品金额计算(分别对每个商品/优惠套装小计、每个店铺小计)
     *
     *输入购物车列表，
     *对购物车中的商品计算总费用
     *查询商品图片 
    
     *:ps:封装image到store_cartlist 中，同时，计算商品总价
	 * @param unknown $store_cart_list 以店铺ID分组的购物车商品信息
	 * @return array
	 */
	public function calcCartList($store_cart_list, $investpay = false, $investqishu="", $easypay = false, $easypayqishu="") {
        $rate = 1.0;

	    if (empty($store_cart_list) || !is_array($store_cart_list)) return array($store_cart_list,array(),0);
	
	    //存放每个店铺的商品总金额
	    $store_goods_total = array();
	    //存放本次下单所有店铺商品总金额
	    $order_goods_total = 0;
	
        //遍历店铺
	    foreach ($store_cart_list as $store_id => $store_cart) {
	        $tmp_amount = 0;
            //遍历商品
	        foreach ($store_cart as $key => $cart_info) {
              
                    $store_cart[$key]['goods_total'] = ncPriceFormat($cart_info['goods_price'] * $cart_info['goods_num']*$rate);    
              
                
                //获得商品图片
	            $store_cart[$key]['goods_image_url'] = cthumb($store_cart[$key]['goods_image']);
                //店铺商品总价相加
	            $tmp_amount += $store_cart[$key]['goods_total'];
	        }
            //更新购物车列表增加总价列表
            //商品图片
	        $store_cart_list[$store_id] = $store_cart;
	        $store_goods_total[$store_id] = ncPriceFormat($tmp_amount);

            //添加物流模板
            //排序
	    }
	    return array($store_cart_list,$store_goods_total);
	}

    /**
     * 添加物流模板信息
     */
    private function _add_transport_information($store_cart_list){
        if(!isset($store_cart_list)||empty($store_cart_list))
            return array();
        
        foreach ($store_cart_list as $store_id => $store_goods_info) {
            //遍历store
            foreach ($store_goods_info as $key => $goods_info) {
                
            }
        }
    }

	/**
	 * 删除购物车商品
	 *
	 * @param string $type 存储类型 db,cache,cookie
	 * @param unknown_type $condition
	 */
	public function delCart($type, $condition = array(), $goods_id_list=null) {
		if ($type == 'db') {
			$result = $this->where($condition)->delete();
		}
		//重新计算购物车商品数和总金额
		if ($result) {
			$this->getCartNum($type,array('buyer_id'=>$condition['buyer_id']));
		}
		return $result;
	}
}
