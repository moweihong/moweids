<?php
namespace Admin\Controller;
use Admin\Controller\AdminController;
class PredepositController extends AdminController {
	/**
	 * 充值列表
	 */
	public function predeposit(){
        $page_num = I('get.p')==null?0:I('get.p');
        $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
        $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['pdr_add_time'] = array('between',array($start_unixtime,$end_unixtime));
        }
        if (!empty($_GET['mname'])){
        	$condition['pdr_member_name'] = trim($_GET['mname']);
        }
		if ($_GET['paystate_search'] != -1 && !is_null($_GET['paystate_search'])){
			$condition['pdr_payment_state'] = $_GET['paystate_search'];
		}
		$model_pd = D('Shop/Predeposit');
		$recharge_list = $model_pd->getPdRechargeList($condition,$page_num.',20','*','pdr_id desc');
		$recharge_count = $model_pd->getPdRechargeCount($condition);

        $recharge_list = D('money_record')->where(array('m_type' => 2))->order('created_at desc')->select();
        // echo json_encode($recharge_list);
        // 
        // die;
        $recharge_count = sizeof($recharge_list);
		$Page = new \Think\Page($recharge_count,20);
        $show = $Page->show();
		//信息输出
		$this->assign('list',$recharge_list);
        $this->assign('page',$show);
        $this->display();
	}

	/**
	 * 充值编辑(更改成收到款)
	 */
	public function rechargeEdit(){
		$id = intval($_GET['id']);
		if ($id <= 0){
			$this->error('参数错误');
		}
		//查询充值信息
		$model_pd = D('Shop/Predeposit');
		$condition = array();		
		$condition['pdr_id'] = $id;
		$condition['pdr_payment_state'] = 0;
		$info = $model_pd->getPdRechargeInfo($condition);
		if (empty($info)){
			$this->error('记录信息错误');
		}

		if (!IS_POST) {
		    //显示支付接口列表
		    $payment_list = D('Shop/payment')->getPaymentOpenList();
		    //去掉预存款和货到付款
		    foreach ($payment_list as $key => $value){
		        if ($value['payment_code'] == 'predeposit' || $value['payment_code'] == 'offline') {
		            unset($payment_list[$key]);
		        }
		    }
		    $this->assign('payment_list',$payment_list);
		    $this->assign('info',$info);
            $this->display();
            exit();
		}

		//取支付方式信息
		$model_payment = D('Shop/payment');
		$condition = array();
		$condition['payment_code'] = $_POST['payment_code'];
		$payment_info = $model_payment->getPaymentOpenInfo($condition);
		if(!$payment_info || $payment_info['payment_code'] == 'offline' || $payment_info['payment_code'] == 'offline') {
		    $this->error('系统不支持选定的支付方式');
		}

		$condition = array();
		$condition['pdr_sn'] = $info['pdr_sn'];
		$condition['pdr_payment_state'] = 0;
		$update = array();
		$update['pdr_payment_state'] = 1;
		$update['pdr_payment_time'] = strtotime($_POST['payment_time']);
		$update['pdr_payment_code'] = $payment_info['payment_code'];
		$update['pdr_payment_name'] = $payment_info['payment_name'];
		$update['pdr_trade_sn'] = $_POST['trade_no'];
		$update['pdr_admin'] = 'admin';//$this->admin_info['name'];
        //$log_msg = L('admin_predeposit_recharge_edit_state').','.L('admin_predeposit_sn').':'.$info['pdr_sn'];

		try {
		    $model_pd->startTrans();
		    //更改充值状态
		    $state = $model_pd->editPdRecharge($update,$condition);
		    if (!$state) {
		        throw Exception('充值信息支付失败');
		    }
		    //变更会员预存款
		    $data = array();
		    $data['member_id'] = $info['pdr_member_id'];
		    $data['member_name'] = $info['pdr_member_name'];
		    $data['amount'] = $info['pdr_amount'];
		    $data['pdr_sn'] = $info['pdr_sn'];
		    $data['admin_name'] = $this->admin_info['name'];
		    $model_pd->changePd('recharge',$data);
		    $model_pd->commit();
		   // $this->log($log_msg,1);
		   $this->success('充值信息修改成功',__CONTROLLER__."/predeposit");
		} catch (Exception $e) {
		    $model_pd->rollback();
			//$this->log($log_msg,0);
		    $this->error($e->getMessage());
		}
	}

	/**
	 * 充值查看
	 */
	public function rechargeInfo(){
		$id = intval($_GET['id']);
		if ($id <= 0){
			$this->error('参数错误');
		}
		//查询充值信息
		$model_pd = D('Shop/Predeposit');
		$condition = array();		
		$condition['pdr_id'] = $id;
		$info = $model_pd->getPdRechargeInfo($condition);
		if (empty($info)){
			$this->error('记录信息错误');
		}
		$this->assign('info',$info);

		$this->display();
	
	}

	/**
	 * 预存款日志
	 */
	public function pdLogList(){
        $page_num = I('get.p')==null?0:I('get.p');
	    $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['lg_add_time'] = array('between',array($start_unixtime,$end_unixtime));
        }
        if (!empty($_GET['mname'])){
        	$condition['lg_member_name'] = $_GET['mname'];
        }
        if (!empty($_GET['aname'])){
            $condition['lg_admin_name'] = $_GET['aname'];
        }
		$model_pd = D('Shop/Predeposit');
		$list_log = $model_pd->getPdLogList($condition,$page_num.",20",'*','lg_id desc');
		$count_log = $model_pd->getPdLogCount($condition);
		$Page = new \Think\Page($count_log,20);
        $show = $Page->show();
        $this->assign('page',$show);
		$this->assign('list_log',$list_log);
		$this->display();
	}

	/**
	 * 提现列表
	 */
	public function pdCashList(){
        $page_num = I('get.p')==null?0:I('get.p');
	    $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['pdc_add_time'] = array('between',array($start_unixtime,$end_unixtime));
        }
        if (!empty($_GET['mname'])){
            $condition['pdc_member_name'] =array('like', '%' . trim($_GET['mname']) . '%');
        }
        if (!empty($_GET['pdc_store_name'])){
        	$condition['pdc_store_name'] = array('like', '%' . trim($_GET['pdc_store_name']) . '%');
        }
        if (!empty($_GET['pdc_sn'])){
        	$condition['pdc_sn'] = trim($_GET['pdc_sn']);
        }
		if ($_GET['paystate_search'] != -1 && !is_null($_GET['paystate_search'])){
			$condition['pdc_payment_state'] = $_GET['paystate_search'];
		}

		$model_pd = D('Shop/Predeposit');
		$cash_list = $model_pd->getPdCashList($condition,$page_num.',20','*','pdc_add_time desc,pdc_payment_state asc,pdc_id asc');
        //添加汇出银行账户
        foreach ($cash_list as $key => $v){
            $cash_list[$key]['accout'] = array();
            if($v['account_id'] > 0){
                $accout = M('account')->where(array('id'=>$v['account_id']))->find();
                $cash_list[$key]['accout'] = $accout;
            }
        }
        $cash_count = $model_pd->getPdCashCount($condition);
		$Page = new \Think\Page($cash_count,20);
        $show = $Page->show();
        $this->assign('page',$show);
		$this->assign('list',$cash_list);
        $this->display();
	}

    /*
     * 财务审核
     */
    public function pdCashVerify()
    {
	    $model_pd = D('Shop/Predeposit');
        $list = $model_pd->getAccountList();
	    $info = $model_pd->getPdCashInfo(array('pdc_id'=>$_GET['id']));
        $this->assign('info', $info);
        $this->assign('list', $list);
        $this->display();
    }
	/**
	 * 更改审核状态
	 */
	public function pdCashCheck(){
	    $id = intval($_POST['pdc_id']);
	    if ($id <= 0){
	        $this->error('参数错误',__CONTROLLER__.'/pdcashlist');
	    }
	    $model_pd = D('Shop/Predeposit');
	    $condition = array();
	    $condition['pdc_id'] = $id;
	    $condition['pdc_payment_state'] = 0;
	    $info = $model_pd->getPdCashInfo($condition);
	    if (!is_array($info) || count($info)<0){
	        $this->error('记录信息错误',__CONTROLLER__.'/pdcashlist');
	    }

	    //查询用户信息
	    // $model_member = D('Shop/Member');
	    // $member_info = $model_member->infoMember(array('member_id'=>$info['pdc_member_id']));

        $update = array();
        if($_POST['verify_state'] == 1){
            //审核通过
            $update['pdc_payment_state'] = 1;
        }else{
            //审核不通过
            $update['pdc_payment_state'] = 2;
        } 
        $update['pdc_check_admin'] = 'admin';
        $update['pdc_check_time'] = time(0);
        $update['tesu_description'] = $_POST['verify_reason'];
        //$log_msg = L('admin_predeposit_cash_edit_state').','.L('admin_predeposit_cs_sn').':'.$info['pdc_sn'];
       
        try {
            $model_pd->startTrans();
            $result = $model_pd->editPdCash($update,$condition);
            if (!$result) {
                throw new Exception('提现信息修改失败');
            }
            $model_pd->commit();
            //$this->log($log_msg,1);
            $this->success('操作成功',__CONTROLLER__.'/pdcashlist');
           
        } catch (Exception $e) {
            $model_pd->rollback();
           // $this->log($log_msg,0);
            $this->error('操作失败',__CONTROLLER__.'/pdcashlist');
        }
        
	}
	/**
	 * 更改汇款状态
	 */
	public function pdCashPay(){
	    $id = intval($_POST['pdc_id']);
	    if ($id <= 0){
	        $this->error('参数错误',__CONTROLLER__.'/pdcashlist');
	    }
	    $model_pd = D('Shop/Predeposit');
	    $condition = array();
	    $condition['pdc_id'] = $id;
	    $condition['pdc_payment_state'] = 1;
	    $info = $model_pd->getPdCashInfo($condition);
	    if (!is_array($info) || count($info)<0){
	        $this->error('记录信息错误',__CONTROLLER__.'/pdcashlist');
	    }

	    //查询用户信息
	    $model_member = D('Shop/Member');
	    $member_info = $model_member->infoMember(array('member_id'=>$info['pdc_member_id']));

        $update = array();
        if($_POST['verify_state'] == 1){
            //汇款成功
            $update['pdc_payment_state'] = 3;
            $update['account_id'] = $_POST['bank'];
        }else{
            //汇款失败
            $update['pdc_payment_state'] = 4;
        } 
        $update['pdc_payment_admin'] = "admin";
        $update['pdc_payment_time'] = time(0);
        $update['tesu_description'] = $_POST['verify_reason'];
        //$log_msg = L('admin_predeposit_cash_edit_state').','.L('admin_predeposit_cs_sn').':'.$info['pdc_sn'];
       
        try {
            $model_pd->startTrans();
            $result = $model_pd->editPdCash($update,$condition);
            if (!$result) {
                throw new Exception('提现信息修改失败');
            }
            //扣除冻结的预存款
            $data = array();
            $data['member_id'] = $member_info['member_id'];
            $data['member_name'] = $member_info['member_name'];
            $data['amount'] = $info['pdc_amount'];
            $data['order_sn'] = $info['pdc_sn'];
            $data['admin_name'] = $admininfo['name'];
            //汇款成功
            if($_POST['verify_state'] == 1){
                if($info['pdc_tx_type'] == 1){
                    //商家提现
                    $data['store_id'] = $info['pdc_store_id'];
                    $model_pd->changeStore('cash_pay',$data);
                }else{
                    $model_pd->changePd('cash_pay',$data);
                }  
            }else{
                //汇款失败
                if($info['pdc_tx_type'] == 1){
                    //商家提现 冻结金 返还
                    $data['store_id'] = $info['pdc_store_id'];
                    $model_pd->changeStore('order_cancel',$data);
                }else{
                    $model_pd->changePd('cash_del',$data);
                }
            }
            
            $model_pd->commit();
            //$this->log($log_msg,1);
            $this->success('操作成功',__CONTROLLER__.'/pdcashlist');

           
        } catch (Exception $e) {
            $model_pd->rollback();
           // $this->log($log_msg,0);
            $this->error('操作成功',__CONTROLLER__.'/pdcashlist');
        }
        
	}

	/**
	 * 查看提现信息
	 */
	public function pdCashView(){
        $this->display();
        die;
	    $id = intval($_GET['id']);
	    if ($id <= 0){
	        showMessage(Language::get('admin_predeposit_parameter_error'),'index.php?act=predeposit&op=pd_cash_list','','error');
	    }
	    $model_pd = Model('predeposit');
	    $condition = array();
	    $condition['pdc_id'] = $id;
	    $info = $model_pd->getPdCashInfo($condition);
	    if (!is_array($info) || count($info)<0){
	        showMessage(Language::get('admin_predeposit_record_error'),'index.php?act=predeposit&op=pd_cash_list','','error');
	    }
	    Tpl::output('info',$info);
	    Tpl::showpage('pd_cash.view');
	}


	/**
	 * 导出预存款充值记录
	 *
	 */
	public function exportStep1(){
	    $condition = array();
	    $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_start_date']);
	    $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['query_end_date']);
	    $start_unixtime = $if_start_date ? strtotime($_GET['query_start_date']) : null;
	    $end_unixtime = $if_end_date ? strtotime($_GET['query_end_date']): null;
	    if ($start_unixtime || $end_unixtime) {
	        $condition['pdr_add_time'] = array('time',array($start_unixtime,$end_unixtime));
	    }
	    if (!empty($_GET['mname'])){
	        $condition['pdr_member_name'] = $_GET['mname'];
	    }
	    if ($_GET['paystate_search'] != ''){
	        $condition['pdr_payment_state'] = $_GET['paystate_search'];
	    }
	    $model_pd = Model('predeposit');
		if (!is_numeric($_GET['curpage'])){		
			$count = $model_pd->getPdRechargeCount($condition);
			$array = array();
			if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=predeposit&op=predeposit');				
				Tpl::showpage('export.excel');
			}else{	//如果数量小，直接下载
				$data = $model_pd->getPdRechargeList($condition,'','*','pdr_id desc',self::EXPORT_SIZE);
				$rechargepaystate = array(0=>'未支付',1=>'已支付');
				foreach ($data as $k=>$v) {
					$data[$k]['pdr_payment_state'] = $rechargepaystate[$v['pdr_payment_state']];
				}
				$this->createExcel($data);
			}
		}else{	//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_pd->getPdRechargeList($condition,'','*','pdr_id desc',"{$limit1},{$limit2}");
			$rechargepaystate = array(0=>'未支付',1=>'已支付');
			foreach ($data as $k=>$v) {
				$data[$k]['pdr_payment_state'] = $rechargepaystate[$v['pdr_payment_state']];
			}			
			$this->createExcel($data);
		}
	}

	/**
	 * 生成导出预存款充值excel
	 *
	 * @param array $data
	 */
	private function createExcel($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_yc_no'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_yc_member'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_yc_ctime'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_yc_ptime'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_yc_pay'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_yc_money'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_yc_paystate'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_yc_memberid'));
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>'NC'.$v['pdr_sn']);
			$tmp[] = array('data'=>$v['pdr_member_name']);
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['pdr_add_time']));
			if (intval($v['pdr_payment_time'])) {
	            if (date('His',$v['pdr_payment_time']) == 0) {
	               $tmp[] = array('data'=>date('Y-m-d',$v['pdr_payment_time']));
	            } else {
	               $tmp[] = array('data'=>date('Y-m-d H:i:s',$v['pdr_payment_time']));
	            }
			} else {
			    $tmp[] = array('data'=>'');
			}
			$tmp[] = array('data'=>$v['pdr_payment_name']);
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['pdr_amount']));
			$tmp[] = array('data'=>$v['pdr_payment_state']);
			$tmp[] = array('data'=>$v['pdr_member_id']);
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('exp_yc_yckcz'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('exp_yc_yckcz'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}

	/**
	 * 导出预存款提现记录
	 *
	 */
	public function exportCashStep1(){
	    $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['pdc_add_time'] = array('time',array($start_unixtime,$end_unixtime));
        }
        if (!empty($_GET['mname'])){
            $condition['pdc_member_name'] = $_GET['mname'];
        }
        if (!empty($_GET['pdc_bank_user'])){
        	$condition['pdc_bank_user'] = $_GET['pdc_bank_user'];
        }
		if ($_GET['paystate_search'] != ''){
			$condition['pdc_payment_state'] = $_GET['paystate_search'];
		}

		$model_pd = Model('predeposit');

		if (!is_numeric($_GET['curpage'])){		
			$count = $model_pd->getPdCashCount($condition);
			$array = array();
			if ($count > self::EXPORT_SIZE ){	//显示下载链接
				$page = ceil($count/self::EXPORT_SIZE);
				for ($i=1;$i<=$page;$i++){
					$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
					$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
					$array[$i] = $limit1.' ~ '.$limit2 ;
				}
				Tpl::output('list',$array);
				Tpl::output('murl','index.php?act=predeposit&op=pd_cash_list');		
				Tpl::showpage('export.excel');
			}else{	//如果数量小，直接下载
				$data = $model_pd->getPdCashList($condition,'','*','pdc_id desc',self::EXPORT_SIZE);
				$cashpaystate = array(0=>'未支付',1=>'已支付');
				foreach ($data as $k=>$v) {
					$data[$k]['pdc_payment_state'] = $cashpaystate[$v['pdc_payment_state']];
				}
				$this->createCashExcel($data);
			}
		}else{	//下载
			$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
			$limit2 = self::EXPORT_SIZE;
			$data = $model_pd->getPdCashList($condition,'','*','pdc_id desc',"{$limit1},{$limit2}");
			$cashpaystate = array(0=>'未支付',1=>'已支付');
			foreach ($data as $k=>$v) {
				$data[$k]['pdc_payment_state'] = $cashpaystate[$v['pdc_payment_state']];
			}
			$this->createCashExcel($data);
		}
	}

	/**
	 * 生成导出预存款提现excel
	 *
	 * @param array $data
	 */
	private function createCashExcel($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_no'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_member'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_money'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_ctime'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_state'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_tx_memberid'));
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>'NC'.$v['pdc_sn']);
			$tmp[] = array('data'=>$v['pdc_member_name']);
			$tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['pdc_amount']));
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['pdc_add_time']));
			$tmp[] = array('data'=>$v['pdc_payment_state']);
			$tmp[] = array('data'=>$v['pdc_member_id']);
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('exp_tx_title'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('exp_tx_title'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}
	
	/**
	 * 预存款明细信息导出
	 */
	public function exportMxStep1(){
	    $condition = array();
	    $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
	    $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
	    $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
	    $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
	    if ($start_unixtime || $end_unixtime) {
	        $condition['lg_add_time'] = array('time',array($start_unixtime,$end_unixtime));
	    }
	    if (!empty($_GET['mname'])){
	        $condition['lg_member_name'] = $_GET['mname'];
	    }
	    if (!empty($_GET['aname'])){
	        $condition['lg_admin_name'] = $_GET['aname'];
	    }
		$model_pd = Model('predeposit');
		if (!is_numeric($_GET['curpage'])){		
    		$count = $model_pd->getPdLogCount($condition);
    		$array = array();
    		if ($count > self::EXPORT_SIZE ){	//显示下载链接
    			$page = ceil($count/self::EXPORT_SIZE);
    			for ($i=1;$i<=$page;$i++){
    				$limit1 = ($i-1)*self::EXPORT_SIZE + 1;
    				$limit2 = $i*self::EXPORT_SIZE > $count ? $count : $i*self::EXPORT_SIZE;
    				$array[$i] = $limit1.' ~ '.$limit2 ;
    			}
    			Tpl::output('list',$array);
    			Tpl::output('murl','index.php?act=predeposit&op=pd_log_list');		
    			Tpl::showpage('export.excel');
    		}else{	//如果数量小，直接下载
    			$data = $model_pd->getPdLogList($condition,'','*','lg_id desc',self::EXPORT_SIZE);
    			$this->createmxExcel($data);
    		}
    	}else{	//下载
    		$limit1 = ($_GET['curpage']-1) * self::EXPORT_SIZE;
    		$limit2 = self::EXPORT_SIZE;
    		$data = $model_pd->getPdLogList($condition,'','*','lg_id desc',"{$limit1},{$limit2}");
    		$this->createmxExcel($data);
    	}
	}

	/**
	 * 导出预存款明细excel
	 *
	 * @param array $data
	 */
	private function createmxExcel($data = array()){
		Language::read('export');
		import('libraries.excel');
		$excel_obj = new Excel();
		$excel_data = array();
		//设置样式
		$excel_obj->setStyle(array('id'=>'s_title','Font'=>array('FontName'=>'宋体','Size'=>'12','Bold'=>'1')));
		//header
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_mx_member'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_mx_ctime'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_mx_av_money'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_mx_freeze_money'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_mx_system'));
		$excel_data[0][] = array('styleid'=>'s_title','data'=>L('exp_mx_mshu'));
		foreach ((array)$data as $k=>$v){
			$tmp = array();
			$tmp[] = array('data'=>$v['lg_member_name']);
			$tmp[] = array('data'=>date('Y-m-d H:i:s',$v['lg_add_time']));
			if (floatval($v['lg_av_amount']) == 0){
			    $tmp[] = array('data'=>'');
			} else {
			    $tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['lg_av_amount']));
			}
			if (floatval($v['lg_freeze_amount']) == 0){
			    $tmp[] = array('data'=>'');
			} else {
			    $tmp[] = array('format'=>'Number','data'=>ncPriceFormat($v['lg_freeze_amount']));
			}
			$tmp[] = array('data'=>$v['lg_admin_name']);
			$tmp[] = array('data'=>$v['lg_desc']);
			$excel_data[] = $tmp;
		}
		$excel_data = $excel_obj->charset($excel_data,CHARSET);
		$excel_obj->addArray($excel_data);
		$excel_obj->addWorksheet($excel_obj->charset(L('exp_mx_rz'),CHARSET));
		$excel_obj->generateXML($excel_obj->charset(L('exp_mx_rz'),CHARSET).$_GET['curpage'].'-'.date('Y-m-d-H',time()));
	}

	public function pdAccountSet()
	{
        $page_num = I('get.p')==null?0:I('get.p');
		$model_pd = D('Shop/Predeposit');
		if(IS_POST){
			if(empty($_POST['accountid'])){
				$insert_array = array();
				$insert_array['account'] = trim($_POST['account']);
				$insert_array['bank_name'] = trim($_POST['bank_name']);
				$insert_array['bank_num']	= $_POST['bank_num'];
				$insert_array['user_name'] = trim($_POST['user_name']);
				if(!empty($insert_array['account']) && !empty($insert_array['bank_name']) && !empty($insert_array['bank_num']) && !empty($insert_array['user_name'])){                        
					$result = $model_pd->add($insert_array);
				}
			}else{
				$insert_array['id'] = trim($_POST['accountid']);
				$insert_array['account'] = trim($_POST['account']);
				$insert_array['bank_name'] = trim($_POST['bank_name']);
				$insert_array['bank_num']	= $_POST['bank_num'];
				$insert_array['user_name'] = trim($_POST['user_name']);
				if(!empty($insert_array['account']) && !empty($insert_array['bank_name']) && !empty($insert_array['bank_num']) && !empty($insert_array['user_name'])){
					$result = $model_pd->edit($insert_array);
				}
			}
		}	
		$condition = array();
		$accountlist = $model_pd->getAccountList($condition,$page_num.',20','*','id desc');
		$account_count = $model_pd->getAccountCount($condition);
		$Page = new \Think\Page($account_count,20);
        $show = $Page->show();
        $this->assign('page',$show);
		$this->assign('accountlist',$accountlist);
        $this->display();
	}
    /*
     * 结算管理
     */
    public function settlement(){
    	$page_num = I('get.p')==null?0:I('get.p');
	    $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['payment_time'] = array('between',array($start_unixtime,$end_unixtime));
        }
        if (!empty($_GET['order_sn'])){
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if (!empty($_GET['pay_sn'])){
        	$condition['pay_sn'] = $_GET['pay_sn'];
        }
        if (!empty($_GET['order_type'])){
        	$condition['order_type'] = $_GET['order_type'];
        }
        if (!empty($_GET['buyer_name'])){
        	$condition['buyer_name'] = array('like', '%' . trim($_GET['buyer_name']) . '%');
        }
        if (!empty($_GET['store_name'])){
        	$condition['store_name'] = array('like', '%' . trim($_GET['store_name']) . '%');
        }
        $condition['order_state']  = array('egt',20);
        $list = M('order')->where($condition)->order('order_id desc')->page($page_num.',20')->select();
        $count = M('order')->where($condition)->order('order_id desc')->count();
        foreach ($list as $key=>$val){
            $store = M('store')->where(array('store_id'=>$val['store_id']))->find();
            $list[$key]['ser_charge'] = $store['ser_charge'];
            $list[$key]['ser_charge_money'] = $val['order_amount']*($store['ser_charge']/100);
        }
        $Page = new \Think\Page($count,20);
		$show = $Page->show();
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display();
    }
    /*
     * 店铺收支
     */
    public function settlementStore(){
    	$page_num = I('get.p')==null?0:I('get.p');
	    $condition = array();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['created_at'] = array('between',array($start_unixtime,$end_unixtime));
        }
        if (!empty($_GET['order_sn'])){
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if (!empty($_GET['store_name'])){
        	$condition['store_name'] = array('like', '%' . trim($_GET['store_name']) . '%');
        }
        $list = M('money_record')->where($condition)->order('id desc')->page($page_num,',20')->select();
        $count = M('money_record')->where($condition)->count();
        $Page = new \Think\Page($count,20);
		$show = $Page->show();
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->display();
    }

    /*
     * 店铺收支明细
     */
    public function settlementStoreDetail(){
    	$page_num = I('get.p')==null?0:I('get.p');
	    $condition = array();
        $store_id = $_GET['store_id'];
        $store = M('store')->where(array('store_id'=>$store_id))->find();
        $if_start_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['stime']);
        $if_end_date = preg_match('/^20\d{2}-\d{2}-\d{2}$/',$_GET['etime']);
        $start_unixtime = $if_start_date ? strtotime($_GET['stime']) : null;
        $end_unixtime = $if_end_date ? strtotime($_GET['etime']): null;
        if ($start_unixtime || $end_unixtime) {
            $condition['created_at'] = array('between',array($start_unixtime,$end_unixtime));
        }
        if (!empty($_GET['order_sn'])){
            $condition['order_sn'] = $_GET['order_sn'];
        }
        if (!empty($_GET['id'])){
        	$condition['id'] = $_GET['id'];
        }
        if (isset($_GET['m_type'])&&$_GET['m_type']!=-1){
        	$condition['m_type'] = $_GET['m_type'];
        }
        $condition['store_id'] = $store_id;
        $list = M('money_record')->where($condition)->order('id desc')->page($page_num.',20')->select();
       	$count = M('money_record')->where($condition)->count();
        $Page = new \Think\Page($count,20);
		$show = $Page->show();
        $this->assign('list',$list);
        $this->assign('page',$show);
        $this->assign('store',$store);
        $this->display();
    }
}