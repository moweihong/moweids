<?php
namespace Shop\Controller;
use Shop\Controller\MemberController;
class CartController extends MemberController {

	public function __construct(){
		parent::__construct();

		if(empty($_SESSION['member_id'])){
			//未登录
			if(IS_AJAX){
				$this->ajaxReturn(array('code' => 0, 'resultText' => array('message' => '未登录')));
			}

			redirect('/shop/login/index');
		}
	}

	/*
	 * 购物车首页
	 */
	public function index()
	{
		$this->display();
	}

	/**
	 * 加入购物车，登录后存入购物车表
	 * 未登录不能添加购物车
	 *
	 */
	public function addCart() {
		$model_goods = D('Goods');
		if (is_numeric($_GET['goods_id'])||is_numeric($_POST['goods_id'])) {

			//商品加入购物车(默认)
			$goods_id = intval($_GET['goods_id'])?intval($_GET['goods_id']):intval($_POST['goods_id']);
			$quantity = intval($_GET['quantity'])?intval($_GET['quantity']):intval($_POST['quantity']);;
			if ($goods_id <= 0) return ;
			$goods_info	= $model_goods->getGoodsOnlineInfo(array('goods_id'=>$goods_id));

			$this->_check_goods($goods_info, $quantity);

		}else{
			$this->jsonFail('没有找到商品');
		}

		//已登录状态，存入数据库
		$save_type = 'db';
		$goods_info['buyer_id'] = $_SESSION['member_id'];

		$model_cart	= D('Cart');
		$insert = $model_cart->addCart($goods_info,$save_type,$quantity);
		if ($insert) {
			//购物车商品种数记入cookie
			cookie('cart_goods_num',$model_cart->cart_goods_num,2*3600);
			$data = array('code'=>1, 'resultText' => array('message' => 'success', 'num' => $model_cart->cart_goods_num, 'amount' => ncPriceFormat($model_cart->cart_all_price)));
		} elseif($insert=='limit_max') {
			$data = array('code'=>0, 'resultText' => array('message' => 'limit_max'));
		}else{
			$data = array('code'=>0, 'resultText' => array('message' => 'failed'));
		}

		$this->ajaxReturn($data);
	}

	/**
	 * 检查商品是否符合加入购物车条件
	 * @param unknown $goods
	 * @param number $quantity
	 */
	private function _check_goods($goods_info, $quantity) {
		if(empty($quantity)) {
			$this->ajaxReturn(array('code' => 0, 'resultText' => array('message' => '参数错误')));
		}
		if(empty($goods_info)) {
			$this->ajaxReturn(array('code' => 0, 'resultText' => array('message' => '商品不存在')));
		}
		if ($goods_info['store_id'] == $_SESSION['store_id']) {
			$this->ajaxReturn(array('code' => 0, 'resultText' => array('message' => '不能购买自己店铺的商品')));
		}
		if(intval($goods_info['goods_storage']) < 1) {
			$this->ajaxReturn(array('code' => 0, 'resultText' => array('message' => '商品库存不足，提醒店家补货')));
		}
		if(intval($goods_info['goods_storage']) < $quantity) {
			$this->ajaxReturn(array('code' => 0, 'resultText' => array('message' => '库存不足')));
		}
	}

	/**
	 * 购物车更新商品数量
	 */
	public function updateCart() {
		$cart_id	= intval(abs($_GET['cart_id']));
		$quantity	= intval(abs($_GET['quantity']));

		if(empty($cart_id) || empty($quantity)) {
			$this->ajaxReturn(array('code' => 0, 'resultText' => array('message' => '修改失败')));
		}

		$model_cart = D('Cart');
		$model_goods= D('Goods');

		//存放返回信息
		$return = array();

		$cart_info = $model_cart->getCartInfo(array('cart_id'=>$cart_id,'buyer_id'=>$_SESSION['member_id']));

		//普通商品
		$goods_id = intval($cart_info['goods_id']);
		$goods_info	= $model_goods->getGoodsOnlineInfo(array('goods_id'=>$goods_id));
		if(empty($goods_info)) {
			$return['code'] = 0;
			$return['resultText']['message'] = '商品已被下架';
			$model_cart->delCart('db',array('cart_id'=>$cart_id,'buyer_id'=>$_SESSION['member_id']));
			$this->ajaxReturn($return);
		}

		if(intval($goods_info['goods_storage']) < $quantity) {
			$return['code'] = 0;
			$return['resultText']['message'] = '库存不足';
			$return['resultText']['goods_num'] = $goods_info['goods_storage'];
			$return['resultText']['goods_price'] = $cart_info['goods_price'];
			$return['resultText']['subtotal'] = $cart_info['goods_price'] * $quantity;
			$model_cart->editCart(array('goods_num'=>$goods_info['goods_storage']),array('cart_id'=>$cart_id,'buyer_id'=>$_SESSION['member_id']));
			$this->ajaxReturn($return);
		}

		$data = array();
		$data['goods_num'] = $quantity;
		$data['goods_price'] = $cart_info['goods_price'];
		$update = $model_cart->editCart($data,array('cart_id'=>$cart_id,'buyer_id'=>$_SESSION['member_id']));
		if ($update !== false) {
			$return = array();
			$return['code'] = 1;
			$return['resultText']['message'] = '修改成功';
			$return['resultText']['subtotal'] = $cart_info['goods_price'] * $quantity;
			$return['resultText']['goods_price'] = $cart_info['goods_price'];
			$return['resultText']['goods_num'] = $quantity;
		} else {
			$return['code'] = 0;
			$return['resultText']['message'] = '修改失败';
		}
		$this->ajaxReturn($return);
	}

	/**
	 * 购物车删除单个商品，未登录前使用goods_id，此时cart_id可能为0，登录后使用cart_id
	 */
	public function del() {
		$cart_id = intval($_GET['cart_id'])?intval(I('get.cart_id')):intval(I('post.cart_id'));
		$goods_id = intval($_GET['goods_id'])?intval(I('get.goods_id')):intval(I('post.goods_id'));
		$goods_id_list = $_GET['goods_id_list']?intval(I('get.goods_id_list')):intval(I('post.goods_id_list'));
		if(strpos( $goods_id_list, ',') === false){
			//
		}else{
			$goods_id_list = explode(',', $goods_id_list);
		}
		if($cart_id < 0 || $goods_id < 0) return ;
		$model_cart	= D('Cart');
		$data = array();

		//登录状态下删除数据库内容
		$delete	= $model_cart->del('db',array('cart_id'=>$cart_id,'buyer_id'=>$_SESSION['member_id']));
		if($delete) {
			$return['code'] = 1;
			$return['resultText']['message'] = '删除成功';
			$return['resultText']['quantity'] = $model_cart->cart_goods_num;
			$return['resultText']['amount'] = $model_cart->cart_all_price;
		} else {
			$return['code'] = 0;
			$return['resultText']['message'] = '删除失败';
		}

		cookie('cart_goods_num',$model_cart->cart_goods_num,2*3600);
		$this->ajaxReturn($return);
	}
}