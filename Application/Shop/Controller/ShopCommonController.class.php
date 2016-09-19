<?php
namespace Shop\Controller;
use Think\Controller;
class ShopCommonController extends Controller {
	
	public function __construct() {
	 parent::__construct();
	 $model=D("GoodsClass");
	 $this->furniture=$furniture=$model->getGoodsCate(1309);//精品家具
	 //头部轮播;
	switch (CONTROLLER_NAME){
		case 'Index':
			$name='HOME_PAGE';
			break;
		case 'TFurniture':
			$name='FURNITURE_PAGE';
			break;
		case 'TBrand':
			$name='BRAND_PAGE';
			break;
		case 'THomedesign':
			$name='HOMEDESIGN_PAGE';
			break;
		case 'TMaterial':
			$name='MATERIAL_PAGE';
			break;
		default :
			$name='HOME_PAGE';
			break;
	}
	 $TopSlideInfo=D('web')->where(array('web_page'=>$name))->find();
	 $footlist=D('ArticleClass')->getFootList();
	 //头部精品家具广告位
	 $this->topFurniture=D('adv')->getAdvInfo(array('ap_id'=>472));//print_r( $this->topFurniture);exit;
	 $this->assign('TopSlide',json_decode($TopSlideInfo['value'],true));
	 $this->assign('footlist',$footlist);
    }

	/*
     *
     */
	public function index()
	{
	}
	
    /*
    * 输出json 成功信息
     */
    function jsonSucc($msg='成功！'){
        $result['code'] = 1;
        $result['resultText']['message'] = $msg;
        $this->ajaxReturn($result, 'JSON');
    }
    
    /*
    * 输出json 数组信息
     */
    function jsonArr($msg_arr=array()){
        $result['code'] = 1;
        $result['message'] = '成功';
        $result['resultText'] = $msg_arr;
        $this->ajaxReturn($result);
    }
    
    /*
    * 输出json 错误信息
     */
    function jsonFail($msg){
        $result['code'] = 0;
        $result['resultText']['message'] = $msg;
        $this->ajaxReturn($result,'JSON');
    }

    /*
     * 兼容提示页
     */
    function needUpgrade(){
        $this->display();
    }
}