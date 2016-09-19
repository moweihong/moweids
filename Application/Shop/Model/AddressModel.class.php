<?php
/**
 * 我的地址
 *
 */
namespace Shop\Model;
use Think\Model;
class AddressModel extends Model {
    
    public function __construct() {
        parent::__construct('address');
    }
    
    /**
     * 取得买家默认收货地址
     *
     * @param array $condition
     */
    public function getDefaultAddressInfo($condition = array()) {
    	if(C('deleted_field')){
    		$condition[C('deleted_field')] = 0;
    	}
        return $this->where($condition)->order('is_default desc,address_id desc')->find();
    }
    
    public function getAddressInfo($condition) {
        return $this->where($condition)->find();
    }
    
	/**
	 * 读取地址列表
	 *
	 * @param 
	 * @return array 数组格式的返回结果
	 */
	public function getAddressList($condition, $order='address_id desc', $field = '*'){
		if(C('deleted_field')){
    		$condition[C('deleted_field')] = 0;
    	}
        return $this->where($condition)->field($field)->order($order)->select();
	}

	/**
	 * 构造检索条件
	 *
	 * @param array $condition 检索条件
	 * @return string 数组形式的返回结果
	 */
	private function _condition($condition){
		$condition_str = '';
		
		if ($condition['member_id'] != ''){
			$condition_str .= " member_id = '". intval($condition['member_id']) ."'";
		}
		
		return $condition_str;
	}
	
	/**
	 * 新增地址
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function addAddress($param){
        return $this->add($param);
	}
	
	/**
	 * 取单个地址
	 *
	 * @param int $area_id 地址ID
	 * @return array 数组类型的返回结果
	 */
	public function getOneAddress($id){
		if (intval($id) > 0){
			$param = array();
			$param['table'] = 'address';
			$param['field'] = 'address_id';
			$param['value'] = intval($id);
			$result = Db::getRow($param);
			return $result;
		}else {
			return false;
		}
	}
	
	/**
	 * 更新地址信息
	 *
	 * @param array $param 更新数据
	 * @return bool 布尔类型的返回结果
	 */
	public function editAddress($update, $condition){
        return $this->where($condition)->save($update);
	}
	/**
	 * 验证地址是否属于当前用户
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function checkAddress($member_id,$address_id) {
		/**
		 * 验证地址是否属于当前用户
		 */
		$check_array = self::getOneAddress($address_id);
		if ($check_array['member_id'] == $member_id){
			unset($check_array);
			return true;
		}
		unset($check_array);
		return false;
	}
	/**
	 * 删除地址
	 *
	 * @param int $id 记录ID
	 * @return bool 布尔类型的返回结果
	 */
	public function delAddress($condition){
		//print_r($condition);exit;
		return $this->where($condition)->save(array('tesu_deleted'=>1));
	    //return $this->where($condition)->delete();
	}

	public function undefaultAll(){
		$condition['member_id'] = $_SESSION['member_id'];
		$update['is_default'] = 0;
		$result =  $this->where($condition)->save($update);
		return $result;
	}
}
