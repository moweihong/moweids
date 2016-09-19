<?php
namespace Shop\Controller;
use Shop\Controller\ShopCommonController;
class TEasypayController extends ShopCommonController {

	/*
	 * 我的额度	
     */
	public function index()
	{
		//查询用户的乐购额度状态，渲染前端页面
		// if(session('mid')){
		// 	$api = API("easypay");
		// 	$result = json_decode($api->get_credit_status(array('usrid' => $_SESSION['mid'])), true);			
		// }
		$re = $this->getStatus();
  
		$this->assign('status', $re);
		$this->display();
	}

	/*
	 * 获取乐购授信状态
	 */
	private function getStatus(){

		if($_SESSION['is_login'] != 1){
            return 0;
        }
        $application_model = Model('Easypayapplication');
        //调用java接口查询用户额度信息，跟新数据库表中的额度信息
        //如果接口异常，查询数据库表中的额度信息
        //失败，使用默认值 ，写入session
        $java_query_result = false;
        $easypay_api = API('easypay');
        $result = json_decode($easypay_api->get_credit_status(array('usrid' => $_SESSION['mid'])), true);
        if($result['code'] == 0){
        //if(!APP_DEBUG){
                //写入数据库
                $data = $result['return_param'];
                $updata_data = $this->data_construct($result['return_param']);
                $application_model->insert_update($_SESSION['member_id'], $updata_data);
                
                //写入session
                $this->_update_session($data['check_flag'], $data['loan_limit'], $data['loan_useble'], $data['is_activate']);

                unset($re);
                if($data['is_activate'] == 1){
                    $re['code']   = 7;
                    $re['credit'] = $data['loan_limit'];
                    $re['debt']   = $data['loan_limit'] - $data['loan_useble'];
                    $re['expire'] = substr($data['limit_validity_time'], 0, 10);
                }else if($data['check_flag'] == 5){
                    $re['code'] = 5;
                    $re['credit'] = $data['loan_limit'];
                }else{
                    $re['code'] = $data['check_flag'];
                }
                return $re;
            }

            
        //}
        	
        // $record = $application_model->where(array('member_id' => $_SESSION['member_id']))->find();
        // \Think\log::ext_log('xxxxx = '.json_encode($record), 'api');
        // if($record){
        //     //写入session
        //     $this->_update_session($record['credit_status'], $record['credit_total'], $record['credit_available'], $record['is_activate']);
        //     unset($re);
        //     if($record['is_activate'] == 1){
        //             $re['code']   = 7;
        //             $re['credit'] = $record['loan_limit'];
        //             $re['debt']   = $record['loan_limit'] - $record['loan_useble'];
        //             $re['expire'] = $record['expire'];
        //         }else if($record['check_flag'] == 5){
        //             $re['code'] = 5;
        //             $re['credit'] = $record['loan_limit'];
        //         }else{
        //             $re['code'] = $record['credit_status'];
        //         }
        //         return $re;
        // }


        // // 0:未提交申请或提交未完成，1：未审核，:2：初审不通过，3：初审通过，4：复审不通过，5：复审通过，6：账号被冻结。
        // // 是否激活0:未激活，1：已激活
        // $code = 7;
        // switch ($code) {
        //     //需要重新申请
        //     case '0':
        //         $re['code'] = 0;
        //         break;
        //     case '2':
        //         $re['code'] = 2;
        //         break;
        //     case '4':
        //         $re['code'] = 4;
        //         break;
        //     //待审核
        //     case '3';
        //         $re['code'] = 3;
        //         break;
        //     case '1';
        //         $re['code'] = 1;
        //         break;
        //     //待激活
        //     case '5';
        //         $re['code'] = 5;
        //         $re['credit'] = 12345;
        //         break;
        //     case '7';
        //         $re['code'] = 7;
        //         $re['credit'] = 10000;
        //         $re['debt'] = 1234;
        //         $re['expire'] = "2016-08-20";
        //         break;
        //     default:
        //         # code...
        //         break;
        // }
        // return $re ;
	}


	 /*
     *  封装参数
     */
    private function data_construct($data){

        if(is_null($data))
            return array();
        //总额度
        $arr['credit_total']     = $data['loan_limit'];
        //可用额度
        $arr['credit_available'] = $data['loan_useble'];
        //授信状态
        $arr['credit_status']    = $data['check_flag'];
        //expire
        $arr['limit_validity_time'] = $data['limit_validity_time']?$data['limit_validity_time']:"1970-1-1";
        //is_activate
        $arr['is_activate'] = $data['is_activate'];
        return $arr;
    }

        /*
     * 更新session
     */
    private function _update_session($status, $total, $available, $is_activate){
        $_SESSION['easypay_credit_status'] = $status;
        $_SESSION['easypay_credit_total'] = $total;
        $_SESSION['easypay_credit_available'] = $available;
        $_SESSION['is_activate'] = $is_activate;
        $_SESSION['easypay_freeze'] = $_SESSION['easypay_credit_status'] === -1 ? 1:0;;
        $_SESSION['easypay_status_zh'] = str_replace(
            array(0, 1, 3, 2, 5, 4, 6),
            array('未开启', '审批中','审批中', '审批未通过', '已开通', '审批未通过','冻结' ),
            intval($_SESSION['easypay_credit_status']));

        //
    }
}