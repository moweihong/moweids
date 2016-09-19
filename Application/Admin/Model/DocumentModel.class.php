<?php
namespace Admin\Model;
use Think\Model;
class documentModel extends Model{
	protected $tableName = 'document';
	public function __construct(){
		parent::__construct();
		
	}
	/**
	 * 列表
	 *
	 * @param array $condition 检索条件
	 * @param obj $page 分页
	 * @return array 数组结构的返回结果
	 */
	public function getList($condition,$page='0'){
			
		$result=$this->where($condition)->order('doc_time')->page($page.',15')->select();
		//print_r($condition);exit;
		return $result;
	}
	/**
	 * 根据编号查询一条
	 * 
	 * @param unknown_type $id
	 */
	public function getOneById($id){
		
		return $this->find($id);
	}
	/**
	 * 根据标识码查询一条
	 * 
	 * @param unknown_type $id
	 */
	public function getOneByCode($code){
		$param	= array(
			'table'	=> 'document',
			'field'	=> 'doc_code',
			'value'	=> $code
		);
		return Db::getRow($param);
	}
	/**
	 * 更新
	 * 
	 * @param unknown_type $param
	 */
	public function update($param){
		if (empty($param)){
			return false;
		}
		if (is_array($param)){
			
			$where = " doc_id = '". $param['doc_id'] ."'";
			$result = $this->where($where)->save($param);
			return $result;
		}else {
			return false;
		}
	}
}