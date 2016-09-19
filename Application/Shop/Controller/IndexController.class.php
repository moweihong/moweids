<?php
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;
use Shop\Model\AdvModel;
class IndexController extends ShopCommonController {
    public function index(){

		//免息分期
		$this->freeInterest=$freeInterest=D('adv')->getAdvList(array('ap_id'=>463));
		//热门推荐
		$this->hotRecommend=$hotRecommend=D('adv')->getAdvList(array('ap_id'=>461));
		//品牌专区
		$this->brandAdv=$brandAdv=D('adv')->getAdvList(array('ap_id'=>462));
		//精品家居
		$this->furnitureAdv=D('adv')->getAdvList(array('ap_id'=>470));
		//装修设计 
		$this->homeDesign=D('adv')->getAdvList(array('ap_id'=>436));//print_r($this->homeDesign);exit;
		$this->homedecoratio=$homedecoration=D('adv')->getAdvList(array('ap_id'=>464));
		//装修材料
		$this->material1=D('adv')->getAdvInfo(array('ap_id'=>405));
		$this->material2=D('adv')->getAdvInfo(array('ap_id'=>406));
		$this->material3=D('adv')->getAdvInfo(array('ap_id'=>407));
		$this->material4=D('adv')->getAdvInfo(array('ap_id'=>408));
		$this->material5=D('adv')->getAdvInfo(array('ap_id'=>409));
        $this->display();
    }

    /*
     * 参数输出页面
     */
    public function debug(){
        $this->display();
    }

    public function test(){
        echo "##debug=".APP_DEBUG.'##';
        echo "<br/>";
        echo "##mode=".C('MODE').'##';
        echo "<br/>";
    }

}