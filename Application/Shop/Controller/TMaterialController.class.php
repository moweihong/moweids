<?php
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;
class TMaterialController extends ShopCommonController {

	/**
     * 方法功能介绍
     * @access public
     * @param mixed $db_config 
     * @return string
     */
	public function index()
	{
		$this->wood_door=D('adv')->getAdvList(array('ap_id'=>466));
        $this->wood_floor=D('adv')->getAdvList(array('ap_id'=>467));
		$this->wood_panel=D('adv')->getAdvList(array('ap_id'=>471));
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