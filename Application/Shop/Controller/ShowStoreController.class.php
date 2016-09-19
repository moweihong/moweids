<?php
/*
 * 前端店铺首页
 */
namespace Shop\Controller;
use Think\Controller;
class ShowStoreController extends Controller {

	/*
	 * 前台店铺的抽象入驻，store_id,根据store_id查询店铺类型，分发到不同的前端店铺
	 * 首页中
     */
	public function index()
	{
		$store_id = I('get.store_id');
		if(intval($store_id) === false){
			$this->error('找不到店铺!');
		}

		$record = D('store')->where(array('store_id' => I('get.store_id')))->find();
		if(is_null($record)){
			$this->error('店铺不存在!');
		}
		switch ($record['com_type']) {
			case '1':
				//经销商
				redirect(U('/shop/ShowVendor/index', array('store_id' => $store_id)));
				break;
			case '2':
				//装修公司
				redirect(U('/shop/THomedesign/storedetail', array('id' => $store_id)));
				break;
			case '3':
				//工厂
				redirect(U('/shop/ShowFactory/index', array('store_id' => $store_id)));
				break;
			default:
				# code...
				break;
		}
	}
	


}