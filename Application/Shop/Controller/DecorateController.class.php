<?php
/*
 * 卖家中心（家装设计公司）
 */

namespace Shop\Controller;
use Shop\Controller\StoreCommonController;

class DecorateController extends StoreCommonController {
    
    public function index(){
        $condition['store_id'] = session('store_id');
        $order_model = M('Order');
        //店铺信息
        $storeinfo = D('Store')->getStoreInfo($condition);
        $condition['order_type'] = ORDER_TYPE_DECORATE;
        $condition['order_state'] = array('in', '1,10,50');
        //进行中订单
        $order_start = $order_model->where($condition)->count();
        $condition['order_state'] = array('in', '20,30,40');
        //已完成订单
        $order_over =  $order_model->where($condition)->count();
        
        //成交方案
        $condition_order['order1.store_id'] = session('store_id');
        $condition_order['order1.order_type'] = ORDER_TYPE_DECORATE;
        $condition_order['order1.order_state'] = array('in', '20,30,40,50');
        $fields = 'order1.add_time,order1.store_id,decorate_plan.title,decorate_plan.house_type,decorate_plan.house_address,decorate_plan.cost,decorate_plan.coverpage';
        $order = 'order1.add_time desc';
        $on = 'order.plan_id=decorate_plan.de_plan_id';
        $jion = 'INNER JOIN allwood_decorate_plan as decorate_plan on order1.plan_id=decorate_plan.de_plan_id';
        $succe_plan = M()->table('allwood_order as order1')->join($jion)->where($condition_order)->field($fields)->order($order)->limit(10)->select();
        
        $this->assign('storeinfo',$storeinfo);
        $this->assign('order_start',$order_start);
        $this->assign('order_over',$order_over);
        $this->assign('succe_plan',$succe_plan);
        $this->display();
    }

   /*
    * 我的方案
     */
    public function solutionList(){
        $decorate_model = D('Decorate');

        $conditon = array();
        $conditon['store_id'] = $_SESSION['store_id'];
        if ($_POST['title'] != ''){
            $conditon['title'] = array('like', '%' . trim($_POST['title']) . '%');
            $this->assign('search_title', $_POST['title']);
        }
        if ($_POST['house_type'] != ''){
            $conditon['house_type'] = array('like', '%' . trim($_POST['house_type']) . '%');
            $this->assign('search_house_type', $_POST['house_type']);
        }
        $p = empty($_GET['p'])?1:$_GET['p'];
        $decorate_list = $decorate_model->getDecorateList('decorate_plan', $conditon, '*', 'de_plan_id desc', $p.',10');
        $count = $decorate_model->getDecorateListCount('decorate_plan', $conditon);
        $this->assign('show_page', getPage($count));
        $this->assign('decorate_list', $decorate_list);
        $this->display();
    }
    
    /*
    * 添加/修改（方案）
     */
    public function decorateOperate(){
        $decorate_model = D('Decorate');
        $id = intval($_REQUEST['id']);
        if ($id > 0){
            $decorate_plan_info = $decorate_model->getDecorateInfo('decorate_plan', array('de_plan_id' => $id));
            $this->assign('decorate_plan_info', $decorate_plan_info);
        }

        if ($_POST){
            $data = array();
            $data['store_id'] = $_SESSION['store_id'];
            $data['title'] = trim($_POST['title']);
            $data['house_type'] = trim($_POST['house_type']);
            $data['house_address'] = trim($_POST['house_address']);
            $data['cost'] = $_POST['cost'];
            $data['area'] = $_POST['area'];
            $data['is_discount'] = intval($_POST['is_discount']);
            $data['decorate_type'] = intval($_POST['decorate_type']);
            if(empty($id)){
                $data['visit_pwd'] = $_POST['visit_pwd'];
            }            
            $data['coverpage'] = serialize($_POST['coverpage']);
            $data['contract_pic'] = serialize($_POST['contract_pic']);
            $data['description'] = trim($_POST['description']);
            $this->validateDecorate($data);
            
            if ($id > 0){
                $title_isexist = $decorate_model->getDecorateInfo('decorate_plan', array('title' => $data['title'],'store_id'=>$_SESSION['store_id'],'de_plan_id'=>array('neq',$id)));
                if(!empty($title_isexist)){
                    $this->jsonFail('标题名称,已存在！');
                }
                $rs = $decorate_model->updDecorate('decorate_plan', $data, array('de_plan_id' => $id));
                
            }else{
                $data['visit_pwd'] = md5($_POST['visit_pwd']);
                $rs = $decorate_model->insertToDecorate('decorate_plan', $data);
            }
            $this->jsonSucc('保存成功');
        }

        $decorate_type_model = D('EffectdrawType');
        $condition = array();
        $condition['status'] = 1;
        $decorate_type_list = $decorate_type_model->selectEffectdrawType($condition, 'id,type_name');
        $this->assign('decorate_type_list', $decorate_type_list);
        $this->display();
    }
    
    public function validateDecorate($param)
    {
        $rules = array(
             array('store_id','require','店铺id不能为空！'),
             array('title','require','标题不能为空且必须小于50个字！'),
             array('title','1,50','标题不能为空且必须小于50个字！',0,'length'),
             array('house_type','require','户型不能为空且必须小于50个字！'),
             array('house_type','1,50','户型不能为空且必须小于50个字！',0,'length'),
             array('house_address','require','户型地址不能为空且必须小于50个字！'),
             array('house_address','1,50','户型地址不能为空且必须小于50个字！',0,'length'),
             array('cost','require','造价不能为空！'),
             array('cost','/^\d+|\d+\.{1}\d{0,2}$/','造价不能超过2位小数！',0,'regex'),
             array('area','require','面积不能为空！'),
             array('area','/^\d+|\d+\.{1}\d{0,2}$/','面积必须是不能超过2位小数的数值！',0,'regex'),
             array('visit_pwd','require','密码不能为空且长度必须小于16 ！'),
             array('visit_pwd','1,16','密码不能为空且长度必须小于16 ！',0,'length'),
             array('coverpage','require','封面图不能为空！'),
             array('contract_pic','require','合同图片不能为空！'),
             array('description','require','方案描述不能为空！')
        );
        $decorate_model = D('Decorate');
        if(!$decorate_model->validate($rules)->create($param)){
            //返回验证失败的信息
            $this->jsonFail($decorate_model->getError());
        }        
    }
    
    public function delDecorate(){
        $idstr = $_POST['id'];
        if ($idstr == ''){
            $this->jsonFail('参数错误');
        }else{
            $decorate_model = D('Decorate');
            $res = $decorate_model->updDecorate('decorate_plan',array('tesu_deleted'=>1), array('de_plan_id' => array('in', explode(',', $idstr))));
            if ($res){
                $this->jsonSucc('删除成功');
                $result = array('code' => 1, 'resultText' => array('message' => '删除成功'));
            }else{
                $this->jsonFail('删除失败');
            }
        }
    }
    
    /*
    * 效果图
     */
    public function effectdraw(){
        $decorate_model = D('Decorate');

        $conditon = array();
        $conditon['store_id'] = $_SESSION['store_id'];
        if ($_POST['title'] != ''){
            $conditon['title'] = array('like', '%' . trim($_POST['title']) . '%');
            $this->assign('search_title', $_POST['title']);
        }
        $p = empty($_GET['p'])?1:$_GET['p'];
        $effectdraw_list = $decorate_model->getDecorateList('decorate_effectdraw', $conditon, '*', 'draw_id desc', $p.',10');
        $count = $decorate_model->getDecorateListCount('decorate_effectdraw', $conditon);
        $this->assign('show_page', getPage($count));
        $this->assign('effectdraw_list', $effectdraw_list);
        $this->display();
    }
    
    /*
    * 添加/修改（效果图）
     */
    public function effectdrawOperate(){
        
        if(IS_AJAX){
            $decorate_model = D('Decorate');
            $id = intval($_POST['id']);
            
            $this->validateOperate($_POST);
            
            if ($id > 0) {
                $effectdraw_list =$decorate_model->getDecorateInfo('decorate_effectdraw_list', array('store_id'=>$_SESSION['store_id'],'id'=>$id));
                if(empty($effectdraw_list)){
                    $this->jsonFail('非法操作！');
                }
                $title_isexist = $decorate_model->getDecorateInfo('decorate_effectdraw_list', array('title' => trim($_POST['title']),'store_id'=>$_SESSION['store_id'],'id'=>array('neq',$id)));
                if(!empty($title_isexist)){
                    $this->jsonFail('标题名称,已存在！');
                }
                $update['title'] = trim($_POST['title']);
                $update['type_id'] = intval($_POST['type_id']);                
                $is_update = $decorate_model->updDecorate('decorate_effectdraw_list', $update, array('id' => $effectdraw_list['id']));
                $decorate_model->delDecorate('decorate_effectdraw',array('draw_list_id'=>$effectdraw_list['id']));
                $operate = $effectdraw_list['id'];
            } else {
                $title_isexist = $decorate_model->getDecorateInfo('decorate_effectdraw_list', array('title' => trim($_POST['title']),'store_id'=>$_SESSION['store_id']));
                if(!empty($title_isexist)){
                    $this->jsonFail('标题名称,已存在！');
                }
                
                $data['store_id'] = $_SESSION['store_id'];
                $data['title'] = trim($_POST['title']);
                $data['type_id'] = intval($_POST['type_id']);
                $operate = $decorate_model->insertToDecorate('decorate_effectdraw_list', $data);
            }
            
            foreach ($_POST['pic'] as $item) {
                $data = array();
                $data['draw_list_id'] = $operate;
                $data['store_id'] = $_SESSION['store_id'];
                $data['title'] = trim($_POST['title']);
                $data['type_id'] = intval($_POST['type_id']);
                $data['is_cover'] = $item['fengmian'] == 1 ? 1 : 0;
                unset($item['fengmian']);
                $data['pic'] = serialize($item);
                $rs = $decorate_model->insertToDecorate('decorate_effectdraw', $data);
            }
            $this->jsonSucc('操作成功！');
        }else{
            $decorate_model = D('Decorate');
            $id = intval($_REQUEST['id']);
            if ($id > 0){
                $effectdraw_info = $decorate_model->getDecorateInfo('decorate_effectdraw', array('draw_id' => $id));
                if (!empty($effectdraw_info)){
                    $condition = array();
                    $condition['draw_list_id'] = $effectdraw_info['draw_list_id'];
                    $draw_list = $decorate_model->getDecorateList('decorate_effectdraw', $condition, 'pic,is_cover', '');
                    if (!empty($draw_list)){
                        $pic_arr = array();
                        foreach ($draw_list as $item) {
                            $pic_data = array();
                            $pic_data['pic_arr'] = unserialize($item['pic']);
                            $pic_data['is_cover'] = $item['is_cover'];
                            $pic_arr[] = $pic_data;
                        }
                    }

                    $effectdraw_info['pic_list'] = $pic_arr;
                    $this->assign('decorate_effectdraw_info', $effectdraw_info);
                }
            }

            $type_model = D('EffectdrawType');
            $condition = array();
            $condition['status'] = 1;
            $field = 'id,type_name';
            $type_list = $type_model->selectEffectdrawType($condition, $field);
            $this->assign('type_list', $type_list);
            $this->display();
        }
    }
    
    public function validateOperate($param)
    {
        if(empty($param['title']) || (mb_strlen($param['title'])>50)){
            $this->jsonFail('标题不能为空且必须小于50个字!');
        }
        if(empty($param['type_id'])){
            $this->jsonFail('请选择风格!');
        }
        if(empty($param['pic'])){
            $this->jsonFail('请上传效果图!');
        }
    }
    
    public function deEffectdraw(){
        $idstr = $_POST['id'];
        if ($idstr == ''){
            $this->jsonFail('参数错误');
        }else{
            $decorate_model = D('Decorate');
            $res = $decorate_model->updDecorate('decorate_effectdraw', array('tesu_deleted'=>1), array('draw_id' => array('in', explode(',', $idstr))));
            if ($res){
                $this->jsonSucc('删除成功！');
            }else{
                $this->jsonFail('删除失败！');
            }
        }
    }
    
    /*
    * 输出json 成功信息
     */
    function jsonSucc($msg='成功！'){
        $result['code'] = 1;
        $result['resultText']['message'] = $msg;
        $this->ajaxReturn($result);
    }
    
    /*
    * 输出json 数组信息
     */
    function jsonArr($msg_arr=array()){
        $result['code'] = 1;
        $result['resultText']['data'] = $msg_arr;
        $result['resultText']['message'] = '成功';
        $this->ajaxReturn($result);
    }
    
    /*
    * 输出json 错误信息
     */
    function jsonFail($msg){
        $result['code'] = 0;
        $result['resultText']['message'] = $msg;
        $this->ajaxReturn($result);
    }

}