<?php
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;
use Shop\Model\GoodsClassModel;
class TAllCategoryController extends ShopCommonController {

	/**
     * 方法功能介绍
     * @access public
     * @param mixed $db_config 
     * @return string
     */
	public function index()
	{
		$model=D("GoodsClass");
		$this->furniture=$furniture=$model->getGoodsCate(1309);//精品家具
		$this->homedecoration=$homedecoration = $model->getGoodsCate(1465);//家居饰品
		$this->homedesign=$homedesign = $model->getGoodsCate(1469);//装修设计
		$this->material=$material = $model->getGoodsCate(1470);//装修材料
		//print_r($furniture);exit;
		$this->display();
	}
	
	public function add()
	{
		//这是一个示例方法，你可以模仿这样的格式来编写其他方法
		echo 'Hello,world!';
	}
	
	public function edit()
	{
		//这是一个示例方法，你可以模仿这样的格式来编写其他方法
		echo 'Hello,world!';
	}
	
	public function delete()
	{
		//这是一个示例方法，你可以模仿这样的格式来编写其他方法
		echo 'Hello,world!';
	}


}