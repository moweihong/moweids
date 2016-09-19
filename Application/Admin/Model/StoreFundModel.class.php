<?php
namespace Admin\Model;
use Think\Model;

/**
* 店铺管理MODEL层
*/
class StoreFundModel extends Model{
	protected $tableName = 'store_fund';
	protected $trueTableName = 'allwood_store_fund';
	protected $tablePrefix = 'allwood_'; 

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
    public function getPdRechargeList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    public function getStoreFundRechargeList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 添加充值记录
     * @param array $data
     */
    public function addPdRecharge($data) {
        return $this->insert($data);
    }

    /**
     * 插入账单明细表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addHistory($data) {
        return $this->table('bill_history')->insert($data);
    }

    /**
     * 编辑
     * @param unknown $data
     * @param unknown $condition
     */
    public function editPdRecharge($data,$condition = array()) {
        return $this->where($condition)->update($data);
    }

    /**
     * 取得单条充值信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getPdRechargeInfo($condition = array(), $fields = '*') {
        return $this->where($condition)->field($fields)->find();
    }

    /**
     * 取得单条充值信息
     * @param unknown $condition
     * @param string $fields
     */
    public function getStoreFundInfo($condition = array(), $fields = '*') {
       // log::i(__METHOD__.' PARAM = '.json_encode($condition), 'ali');
        return $this->where($condition)->field($fields)->find();
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
        return $this->table('pd_cash')->where($condition)->count();
    }

    /**
     * 取日志总数
     * @param unknown $condition
     */
    public function getPdLogCount($condition = array()) {
        return $this->table('pd_log')->where($condition)->count();
    }

    /**
     * 取得预存款变更日志列表
     * @param unknown $condition
     * @param string $pagesize
     * @param string $fields
     * @param string $order
     */
    public function getPdLogList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('store_fund_log')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }

    /**
     * 变更预存款
     * @param unknown $change_type
     * @param unknown $data
     * @throws Exception
     * @return unknown
     */
    public function changePd($change_type,$data = array()) {
        //var_dump($data);die;
        $data_log = array();
        $data_pd = array();
        $data_log['lg_member_id'] = $data['member_id'];
        $data_log['lg_member_name'] = $data['member_name'];
        $data_log['lg_add_time'] = TIMESTAMP;
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
        	default:
        	    throw new Exception('参数错误');
        	    break;
        }
        $update = $this->table('member')->where(array('member_id'=>$data['member_id']))->save($data_pd);
        if (!$update) {
            throw new Exception('操作失败');
        }
        $insert = $this->table('pd_log')->add($data_log);
        if (!$insert) {
            throw new Exception('操作失败');
        }
        return $insert;
    }

    /**
     * 删除充值记录
     * @param unknown $condition
     */
    public function delPdRecharge($condition) {
        return $this->where($condition)->delete();
    }
}