<?php
/*
 * 特速金融页面
 */

namespace Shop\Controller;
use Think\Controller;
class TSFinanceController extends MemberController {

	/*
     * 买家中心，特速金融页面
     */
	public function tsFinance(){
        $pagenum = I('get.p')==null?0:I('get.p');
        $model=M("order");
        $list=$model->field("add_time,order_sn,order_amount_delta,period,order_type")
                    ->where(array("order_type"=>array('in','2,3'),"buyer_id"=>$_SESSION["member_id"],'order_state'=>array('neq','1')))
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
        $count=$model->where(array("order_type"=>array('in','2,3'),"buyer_id"=>$_SESSION["member_id"],'order_state'=>array('neq','1')))->count();
        $Page = new \Think\Page($count,10);
        $show = $Page->show();
        $this->assign("totalrepay",$totalrepay);
        $this->assign("show_page",$show);
        $this->display('index');
    }

    /**
     * 还款记录弹窗模板
     */
    public function tplRepaymentHistory(){
        $orderid=$_GET["order_id"];
        if(!empty($orderid)){
            $url="http://".C("allwood_url").C("javaapi_get_repay");
            $apiinterface=API("api");
            $result = $apiinterface->api($url,array("order_id"=>$orderid), 'POST');
            $result=json_decode($result,true);
            if($result["code"]==0){//成功
                $this->assign("list",$result["return_param"]);
            }else{//失败
                $this->assign("list","");
            }
        }else{
            $this->assign("list","");
        }
        $this->assign("orderid",$orderid);
        // Tpl::setLayout('null_layout');
        $this->display();
    }

    /*
     * 装修款转账
     */
    public function decorateFundTransfer(){
        $order_sn = $_GET['order_sn'];
        $buyer_id = $_SESSION["member_id"];
        $fields = 'allwood_order.add_time,allwood_order.order_sn,allwood_order.order_amount_delta,allwood_order.order_type,decorate_plan.title,decorate_plan.cost';
        //乐装分期订单
        $decorate_order =M('order')->field($fields)->join('allwood_decorate_plan decorate_plan on decorate_plan.de_plan_id = allwood_order.plan_id')
                        ->where(array("allwood_order.order_sn"=>$order_sn,"allwood_order.buyer_id"=>$buyer_id))
                        ->find();
        //
        //已转账金额
        $transfer_money = M('transfer_record')->where(array('order_sn' => $order_sn))->sum('money');
        if(empty($transfer_money)){
            $transfer_money = 0;
        }
        //待转账
        $transfer_end = $decorate_order['order_amount_delta'] - $transfer_money;
        
        //转账记录
        $transfer_list = M('transfer_record')->field('money,create_time')->where(array('order_sn' => $order_sn))->select();
        
        $this->assign('decorate_order',$decorate_order);
        $this->assign('transfer_money',$transfer_money);
        $this->assign('transfer_end',$transfer_end);
        $this->assign('transfer_list',$transfer_list);
        $this->display();
    }
	
	/*
     * 转账操作
     */
    public function transferMoney(){
        $order_sn = $_POST['order_sn'];
        $money = $_POST['money'];
        $buyer_id = $_SESSION["member_id"];
        $member_phone = M('member')->where(array('id'=>$_SESSION["member_id"]))->field('mobile')->find();
        if($_POST['code']!=$_SESSION['ts_'.$member_phone['mobile']]['verify_code']){
            $this->jsonFail('短信验证码错误！');
        }
        if($money <= 0 || !preg_match('/^\d+$/i', $money)){
            $this->jsonFail('金额必须是大于0的整数！');
        }
        $fields = 'allwood_order.order_amount_delta,allwood_order.store_id,member.decorate_fund,order_id,store_name';
        $order = M('order')->field($fields)->join('allwood_member member on member.member_id = allwood_order.buyer_id')
                      ->where(array("allwood_order.order_sn"=>$order_sn,"allwood_order.buyer_id"=>$buyer_id))->find();
        if(empty($order)){
            $this->jsonFail('订单号错误！');
        }else{
            //已转账金额
            $transfer_money = M('transfer_record')->where(array('order_sn' => $order_sn))->sum('money');
            if(empty($transfer_money)){
                $this->$transfer_money = 0;
            }
            if($money > $order['decorate_fund']){
                $this->jsonFail('账户余额不足！当前余额为：'.$order['decorate_fund']);
            }
            
            $money_end = $order['order_amount_delta'] - $transfer_money;
            if($money_end == 0){
                $this->jsonFail('已经还完，不需要再次还款！');
            }
            try {
                //开始事务
                M()->startTrans();
                //大于剩余还款金额  只需要还剩余的金额
                if($money > $money_end){
                    $decorate_fund = $order['decorate_fund'] - $money_end;
                    $data['buyer_id'] = $buyer_id;
                    $data['store_id'] = $order['store_id'];
                    $data['order_sn'] = $order_sn;
                    $data['money'] = $money_end;
                    $data['balance'] = $decorate_fund;
                    $data['create_time'] = time();
                    //插入转账记录
                    M('transfer_record')->add($data);
                    // $store = M('store')->where(array('store_id'=>$order['store_id']))->find();
                    //更新卖家装修款
                    // M('store')->where(array('store_id'=>$order['store_id']))->save(array('decorate_fund'=>$store['decorate_fund']+$money));

                    $m_record_model = M('money_record');
                    $store_info = M('store')->where(array('store_id' => $order['store_id']))->field('member_id,member_name,store_name,ser_charge,deposit_avaiable')->find();
                    $store_yu_e = $store_info['deposit_avaiable'];

                    if(!is_numeric($store_info['ser_charge'])){
                        throw new \Exception("服务费不合法", 1);
                    }
                    /*抽佣*/
                    unset($data);
                    $data['created_at']    = TIMESTAMP;
                    $data['pay_class']     = 'online';
                    $data['money']         = $money_end*1.0*$store_info['ser_charge']/100;
                    $data['business_type'] = 2;//0个人 1经销商 2家装设计公司 3工厂
                    $data['is_pay']        = 1;//0:收入 1：支出
                    $data['m_type']        = LIUSHUI_TYPE_COMMISSION;//金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购
                    $data['member_id']     = $store_info['member_id'];
                    $data['member_name']   = $store_info['member_name'];
                    $data['store_id']      = $order['store_id'];
                    $data['order_id']      = $order['order_id'];
                    $data['order_sn']      = $order_sn;
                    $data['des']           = "乐装订单，全木行抽佣装修公司";
                    $data['store_name']    = $order['store_name'];
                    $data['yu_e']          = $store_yu_e + ($money_end*(100-$store_info['ser_charge'])*1.0/100);
                    $insert = $m_record_model->add($data);
                    if (!$insert) {
                        throw new \Exception('抽佣写入错误');
                    }


                    //经销商记录
                    $data = array();
                     $data['created_at']    = TIMESTAMP;
                     $data['pay_class']     = 'online';
                     $data['money']         = $money_end;
                     $data['business_type'] = 2;//0个人 1经销商 2家装设计公司 3工厂
                     $data['is_pay']        = 0;//0:收入 1：支出
                     $data['m_type']        = LIUSHUI_TYPE_EASYDECO;//金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购
                     $data['member_id']     = $store_info['member_id'];
                     $data['member_name']   = $store_info['member_name'];
                     $data['store_id']      = $order['store_id'];
                     $data['order_id']      = $order['order_id'];
                    $data['order_sn']      = $order_sn;
                    $data['des']           = "乐装订单，买家打款装修公司";
                     $data['store_name']    = $order['store_name'];
                     $data['yu_e']          = $store_yu_e + $money_end;

                    $insert = $m_record_model->add($data);
                    if (!$insert) {
                        throw new \Exception('记录我的收入出现错误');
                    }
                    /*普通订单的结算方式只能是online ，所以只需要向卖家的可用余额账户打款即可*/
                     /*扣除佣金*/
                    unset($data_store);
                    $data_store['deposit_avaiable'] = array('exp','deposit_avaiable+'.($money_end*(100-$store_info['ser_charge'])*1.0/100));
                    if(!$update = M('store')->where(array('store_id'=>$order['store_id']))->save($data_store)){
                        throw new \Exception("抽佣失败", 1);
                        
                    }



                    //更新个人用户装修款
                    M('member')->where(array('member_id'=>$buyer_id))->save(array('decorate_fund'=>$decorate_fund));
                }else{
                    $decorate_fund = $order['decorate_fund'] - $money;
                    $data['buyer_id'] = $buyer_id;
                    $data['store_id'] = $order['store_id'];
                    $data['order_sn'] = $order_sn;
                    $data['money'] = $money;
                    $data['balance'] = $decorate_fund;
                    $data['create_time'] = time();
                    //插入转账记录
                    M('transfer_record')->add($data);
                    // $store = M('store')->where(array('store_id'=>$order['store_id']))->find();
                    //更新卖家装修款
                    // M('store')->where(array('store_id'=>$order['store_id']))->save(array('decorate_fund'=>$store['decorate_fund']+$money));




                    $m_record_model = M('money_record');
                    $store_info = M('store')->where(array('store_id' => $order['store_id']))->field('member_id,member_name,store_name,ser_charge,deposit_avaiable')->find();
                    $store_yu_e = $store_info['deposit_avaiable'];

                    if(!is_numeric($store_info['ser_charge'])){
                        throw new \Exception("服务费不合法", 1);
                    }
                    /*抽佣*/
                    unset($data);
                    $data['created_at']    = TIMESTAMP;
                    $data['pay_class']     = 'online';
                    $data['money']         = $money*1.0*$store_info['ser_charge']/100;
                    $data['business_type'] = 2;//0个人 1经销商 2家装设计公司 3工厂
                    $data['is_pay']        = 1;//0:收入 1：支出
                    $data['m_type']        = LIUSHUI_TYPE_COMMISSION;//金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购
                    $data['member_id']     = $store_info['member_id'];
                    $data['member_name']   = $store_info['member_name'];
                    $data['store_id']      = $order['store_id'];
                    $data['order_id']      = $order['order_id'];
                    $data['order_sn']      = $order_sn;
                    $data['des']           = "乐装订单，全木行抽佣装修公司";
                    $data['store_name']    = $order['store_name'];
                    $data['yu_e']          = $store_yu_e + ($money*(100-$store_info['ser_charge'])*1.0/100);
                    $insert = $m_record_model->add($data);
                    if (!$insert) {
                        throw new \Exception('抽佣写入错误');
                    }


                    //经销商记录
                    $data = array();
                     $data['created_at']    = TIMESTAMP;
                     $data['pay_class']     = 'online';
                     $data['money']         = $money;
                     $data['business_type'] = 2;//0个人 1经销商 2家装设计公司 3工厂
                     $data['is_pay']        = 0;//0:收入 1：支出
                     $data['m_type']        = LIUSHUI_TYPE_EASYDECO;//金额类型:2:充值 3:提现 4:交易服务费 5:分期贴息 6:转账 7:v-f 提货 8：c-v 订单  9 乐装 10 乐购
                     $data['member_id']     = $store_info['member_id'];
                     $data['member_name']   = $store_info['member_name'];
                     $data['store_id']      = $order['store_id'];
                     $data['order_id']      = $order['order_id'];
                    $data['order_sn']      = $order_sn;
                    $data['des']           = "乐装订单，买家打款装修公司";
                     $data['store_name']    = $order['store_name'];
                     $data['yu_e']          = $store_yu_e + $money;

                    $insert = $m_record_model->add($data);
                    if (!$insert) {
                        throw new \Exception('记录我的收入出现错误');
                    }
                    /*普通订单的结算方式只能是online ，所以只需要向卖家的可用余额账户打款即可*/
                     /*扣除佣金*/
                    unset($data_store);
                    $data_store['deposit_avaiable'] = array('exp','deposit_avaiable+'.($money*(100-$store_info['ser_charge'])*1.0/100));
                    if(!$update = M('store')->where(array('store_id'=>$order['store_id']))->save($data_store)){
                        throw new \Exception("抽佣失败", 1);
                        
                    }





                    //更新个人用户装修款
                    M('member')->where(array('member_id'=>$buyer_id))->save(array('decorate_fund'=>$decorate_fund));
                }
                //提交事务
                M()->commit();
                $this->jsonSucc();
            } catch (Exception $e) {
                //回滚事务
                M()->rollback();
                $this->jsonFail('转账失败！');
            }
        }
    }

    /*
     * 专修金转款成功
     */
    public function transfersuccess(){
        $money = $_GET['money'];
        $this->assign('money',$money);
        $this->display();
    }

    /*
     * 发送短信验证码
     */
    public function sendCode(){
        $member_phone = M('member')->where(array('id'=>$_SESSION["member_id"]))->field('mobile')->find();
        $result=sendLogin($member_phone['mobile']);
        if ($result==1) {//die("aaa");
            //短信发送成功!
            $suc['code']=1;
            $suc['message'] = '验证码已发送至'.substr_replace($member_phone['mobile'], '****', 3,4);
            $this->ajaxReturn($suc);
        } else {
            $suc['code']=0;
            $error['message'] = '短信发送失败!';
            $this->ajaxReturn($error);
        }
    }

}