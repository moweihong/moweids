<?php
namespace Admin\Model;
use Think\Model;

/**
* 会员管理MODEL层
*/
class MemberModel extends Model{
	protected $tableName = 'member';

	/**
     * 会员列表
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     */
	public function getMemberList($condition=array(), $field = '*' , $page = 0, $order = 'member_id desc'){
		return $this->where($condition)->field($field)->order($order)->page($page)->select();
	}

	/**
	 * 会员数量
	 * @param array $codition
	 */
	public function getMemberCount($condition=array()){
		return $this->where($condition)->count();
	}
}