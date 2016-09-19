<?php
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;
class TFurnitureController extends ShopCommonController {
    /*
     * 精品家居专区
     */
    public function index(){
		$this->hotRecommend=D('adv')->getAdvList(array('ap_id'=>465));
        $goodsClassModel  = D('GoodsClass');
        $recList = H('goods_class');
        // echo encode_json($recList);
        // die;
        // 
        $this->display();
    }

    /*
     * 品牌专区搜索页
     */
    public function search(){
        $this->display();
    }
}