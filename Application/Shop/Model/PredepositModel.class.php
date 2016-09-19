<?php
/**
 * 预存款
 *

 */
namespace Shop\Model;
use Think\Model;
class PredepositModel extends Model {

    protected $tableName = 'pd_recharge';

    /**
     * 生成充值编号
     * @return string
     */
    public function makeSn() {
       return mt_rand(10,99)
    	      . sprintf('%010d',time() - 946656000)
    	      . sprintf('%03d', (float) microtime() * 1000)
    	      . sprintf('%03d', (int) $_SESSION['member_id'] % 1000);
    }

    /**
     * 取得充值列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdRechargeList($condition = array(), $pagesize = '', $fields = '*', $order = '') {
        return $this->where($condition)->field($fields)->order($order)->page($pagesize)->select();
    }
	
	/**
	 * 新增
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function add($param){
		if (empty($param)){
			return false;
		}
		if (is_array($param)){
			$tmp = array();
			foreach ($param as $k => $v){
				$tmp[$k] = $v;
			}
			$result = M('account')->add($tmp);
			return $result;
		}else {
			return false;
		}
	}
	
	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @return bool 布尔类型的返回结果
	 */
	public function edit($param){
		if (empty($param)){
			return false;
		}
		if (is_array($param)){
			$tmp = array();
			foreach ($param as $k => $v){
				$tmp[$k] = $v;
			}
			$where = " id = '". $param['id'] ."'";
			$result = M('account')->where($where)->save($tmp);
			return $result;
		}else {
			return false;
		}
	}

    /**
     * 添加充值记录
     * @param array $data
     */
    public function addPdRecharge($data) {
        return $this->table('pd_recharge')->insert($data);
    }

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdRecharge($data,$condition = array()) {
        return M('pd_recharge')->where($condition)->save($data);
    }

    /**
     * 取得单条充值信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdRechargeInfo($condition = array(), $fields = '*') {
        return M('pd_recharge')->where($condition)->field($fields)->find();
    }

    /**
     * 取充值信息总数
     * @param unknown $condition
     */
    public function getPdRechargeCount($condition = array()) {
        return $this->where($condition)->count();
    }

    /**
     * 取提现单信息总数
     * @param unknown $condition
     */
    public function getPdCashCount($condition = array()) {
        return M('pd_cash')->where($condition)->count();
    }

    /**
     * 取日志总数
     * @param unknown $condition
     */
    public function getPdLogCount($condition = array()) {
        return M('pd_log')->where($condition)->count();
    }

    /**
     * 佢汇出账号总数
     * @param unknown $condition
     */
    public function getAccountCount($condition = array()){
        return M('account')->where($condition)->count();
    }

    /**
     * 取得预存款变更日志列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdLogList($condition = array(), $pagesize = '', $fields = '*', $order = '') {
        return M('pd_log')->where($condition)->field($fields)->order($order)->page($pagesize)->select();
    }
	
	
	 /**
     * 取得汇出账号列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getAccountList($condition = array(), $pagesize = '', $fields = '*', $order = '') {
        return M('account')->where($condition)->field($fields)->order($order)->page($pagesize)->select();
    }

    /**
     * 变更预存款
     * @param unknown $change_type
     * @param unknown $data
     * @throws Exception
     * @return unknown
     */
    public function changePd($change_type,$data = array()) {
        $data_log = array();
        $data_pd = array();
        $data_log['lg_member_id'] = $data['member_id'];
        $data_log['lg_member_name'] = $data['member_name']?$data['member_name']:($_SESSION['member_name']?$_SESSION['member_name']:"");
        $data_log['lg_add_time'] = $_SERVER['REQUEST_TIME'];
        $data_log['lg_type'] = $change_type;
        switch ($change_type){
            case 'order_pay':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付预存款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.($data['amount']+$data['cishan']));
                break;
            case 'order_freeze':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '下单，冻结预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
                break;
            case 'order_cancel':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消订单，解冻预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                break;
            case 'order_comb_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付被冻结的预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                break;
        	case 'recharge':
        	    $data_log['lg_av_amount'] = $data['amount'];
        	    $data_log['lg_desc'] = '充值，充值单号: '.$data['pdr_sn'];
        	    $data_log['lg_admin_name'] = $data['admin_name'];
        	    $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
        	    break;
        	case 'refund':
        	    $data_log['lg_av_amount'] = $data['amount'];
        	    $data_log['lg_desc'] = '确认退款，订单号: '.$data['order_sn'];
        	    $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
        	    break;
        	case 'cash_apply':
        	    $data_log['lg_av_amount'] = -$data['amount'];
        	    $data_log['lg_freeze_amount'] = $data['amount'];
        	    $data_log['lg_desc'] = '申请提现，冻结预存款，提现单号: '.$data['order_sn'];
        	    $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
        	    $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);
        	    break;
    	    case 'cash_pay':
    	        $data_log['lg_freeze_amount'] = -$data['amount'];
    	        $data_log['lg_desc'] = '提现成功，提现单号: '.$data['order_sn'];
    	        $data_log['lg_admin_name'] = $data['admin_name'];
    	        $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
    	        break;
	        case 'cash_del':
	            $data_log['lg_av_amount'] = $data['amount'];
	            $data_log['lg_freeze_amount'] = -$data['amount'];
	            $data_log['lg_desc'] = '取消提现申请，解冻预存款，提现单号: '.$data['order_sn'];
	            $data_log['lg_admin_name'] = $data['admin_name'];
	            $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
	            $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
	            break;
            case 'storefund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = 0;
                $data_log['lg_desc'] = '开店缴费，提现单号: '.$data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                break;
        	default:
        	    throw new Exception('参数错误');
        	    break;
        }
        $update = M('member')->where(array('member_id'=>$data['member_id']))->save($data_pd);
         if (!$update) {
             return '操作失败';
         }
        $insert = M('pd_log')->add($data_log);
        if (!$insert) {
            return '操作失败';
        }
        return $insert;
    }

        /**
     * 变更预存款
     * @param unknown $change_type
     * @param unknown $data
     * @throws Exception
     * @return unknown
     */
    public function changePd2($change_type,$data = array()) {
        $data_log = array();
        $data_pd = array();
        $data_log['lg_member_id'] = $data['member_id'];
        $data_log['lg_member_name'] = $data['member_name']?$data['member_name']:($_SESSION['member_name']?$_SESSION['member_name']:"");
        $data_log['lg_add_time'] = $_SERVER['REQUEST_TIME'];
        $data_log['lg_type'] = $change_type;
        switch ($change_type){
            case 'order_pay':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付预存款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.($data['amount']+$data['cishan']));
                break;
            case 'order_easypay':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付预存款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.($data['amount']+$data['cishan']));
                break;
            case 'order_easydeco':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付预存款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.($data['amount']+$data['cishan']));
                break;
            case 'order_freeze':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '下单，冻结预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
                break;
            case 'order_cancel':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消订单，解冻预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                break;
            case 'order_comb_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '下单，支付被冻结的预存款，订单号: '.$data['order_sn'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                break;
            case 'recharge':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '充值，充值单号: '.$data['pdr_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                break;
            case 'refund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_desc'] = '确认退款，订单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                break;
            case 'cash_apply':
                $data_log['lg_av_amount'] = -$data['amount'];
                $data_log['lg_freeze_amount'] = $data['amount'];
                $data_log['lg_desc'] = '申请提现，冻结预存款，提现单号: '.$data['order_sn'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit-'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit+'.$data['amount']);
                break;
            case 'cash_pay':
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '提现成功，提现单号: '.$data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                break;
            case 'cash_del':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消提现申请，解冻预存款，提现单号: '.$data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                break;
            case 'storefund':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = 0;
                $data_log['lg_desc'] = '开店缴费，提现单号: '.$data['order_sn'];
                $data_log['lg_admin_name'] = $data['admin_name'];
                $data_pd['available_predeposit'] = array('exp','available_predeposit+'.$data['amount']);
                $data_pd['freeze_predeposit'] = array('exp','freeze_predeposit-'.$data['amount']);
                break;
            default:
                throw new Exception('参数错误');
                break;
        }
        $update = M('member')->where(array('member_id'=>$data['member_id']))->save($data_pd);
         if (!$update) {
             return '操作失败';
         }
        $insert = M('pd_log')->add($data_log);
        if (!$insert) {
            return '操作失败';
        }
        return $insert;
    }




    /**
     * 变更更改商铺预存款
     * @param unknown $change_type
     * @param unknown $data
     * @throws Exception
     * @return unknown
     */
    public function changeStore($change_type,$data = array()) {
        $data_log = array();
        $data_pd = array();
        $data_log['lg_member_id'] = $data['member_id'];
        $data_log['lg_member_name'] = $data['member_name']?$data['member_name']:($_SESSION['member_name']?$_SESSION['member_name']:"");
        $data_log['lg_add_time'] = time();
        $data_log['lg_type'] = $change_type;
        switch ($change_type){
            case 'order_cancel':
                $data_log['lg_av_amount'] = $data['amount'];
                $data_log['lg_freeze_amount'] = -$data['amount'];
                $data_log['lg_desc'] = '取消订单，解冻预存款，订单号: '.$data['order_sn'];
                $data_pd['deposit_freeze'] = array('exp','deposit_freeze-'.$data['amount']);
                $data_pd['deposit_avaiable'] = array('exp','deposit_avaiable+'.$data['amount']);
                $money_inset['des'] = '提现失败';
                break;
        	case 'cash_apply':
        	    $data_log['lg_av_amount'] = -$data['amount'];
        	    $data_log['lg_freeze_amount'] = $data['amount'];
        	    $data_log['lg_desc'] = '申请提现，冻结预存款，提现单号: '.$data['order_sn'];
        	    $data_pd['deposit_avaiable'] = array('exp','deposit_avaiable-'.$data['amount']);
        	    $data_pd['deposit_freeze'] = array('exp','deposit_freeze+'.$data['amount']);
                $money_inset['des'] = '申请提现';
        	    break;
    	    case 'cash_pay':
    	        $data_log['lg_freeze_amount'] = -$data['amount'];
    	        $data_log['lg_desc'] = '提现成功，提现单号: '.$data['order_sn'];
    	        $data_log['lg_admin_name'] = $data['admin_name'];
    	        $data_pd['deposit_freeze'] = array('exp','deposit_freeze-'.$data['amount']);
    	        break;
        	default:
        	    throw new Exception('参数错误');
        	    break;
        }
        $update = M('store')->where(array('store_id'=>$data['store_id']))->save($data_pd);
         if (!$update) {
             throw new Exception('操作失败');
        }
        if($change_type != 'cash_pay'){
            $store =   M('store')->where(array('store_id'=>$data['store_id']))->find();
            $money_inset['member_id'] = $data['member_id'];
            $money_inset['member_name'] = $data_log['lg_member_name'];
            $money_inset['store_id'] = $store['store_id'];
            $money_inset['store_name'] = $store['store_name'];
            $money_inset['order_sn'] = $data['order_sn'];
            $money_inset['m_type'] = 3;
            $money_inset['is_pay'] = 1;
            if($store['com_type'] == 3){
                $money_inset['business_type'] = 3;
            }else if($store['com_type'] == 2){
                $money_inset['business_type'] = 2;
            }else{
                $money_inset['business_type'] = 1;
            }
            $money_inset['money'] = $data['amount'];
            $money_inset['yu_e'] = $store['deposit_avaiable'];
            $money_inset['created_at'] = time();
            M('money_record')->add($money_inset);
        }
        
        
        $insert = M('pd_log')->add($data_log);
        if (!$insert) {
            throw new Exception('操作失败');
        }
        return $insert;
    }

    /*
     * 插入提现表
     */
    public function addPdc($data){
        return M('pd_cash')->add($data);
    }
    /**
     * 插入账单明细表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addHistory($data){
        return M('bill_history')->add($data);
    }

    /**
     * 删除充值记录
     * @param unknown $condition
     */
    public function delPdRecharge($condition) {
        return $this->table('pd_recharge')->where($condition)->delete();
    }

    /**
     * 取得提现列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdCashList($condition = array(), $pagesize = '', $fields = '*', $order = '') {
        return M('pd_cash')->where($condition)->field($fields)->order($order)->page($pagesize)->select();
    }
    /**
     * 取得提现列表总数
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdCashListCount($condition = array()) {
        return M('pd_cash')->where($condition)->count();
    }

    /**
     * 添加提现记录
     * @param array $data
     */
    public function addPdCash($data) {
        return $this->table('pd_cash')->insert($data);
    }

    /**
     * 编辑提现记录
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdCash($data,$condition = array()) {
        return M('pd_cash')->where($condition)->save($data);
    }

    /**
     * 取得单条提现信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdCashInfo($condition = array(), $fields = '*') {
        return M('pd_cash')->where($condition)->field($fields)->find();
    }

    /**
     * 删除提现记录
     * @param unknown $condition
     */
    public function delPdCash($condition) {
        return $this->table('pd_cash')->where($condition)->delete();
    }
}