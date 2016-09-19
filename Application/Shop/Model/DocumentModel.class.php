<?php

/**
 * 系统文章
 *
 */
namespace Shop\Model;
use Think\Model;
class DocumentModel extends Model
{
	/**
	 * 查询所有系统文章
	 */
	public function getList(){
		return $this->select();
	}
	/**
	 * 根据编号查询一条
	 * 
	 * @param unknown_type $id
	 */
	public function getOneById($id){
		$param	= array(
			'doc_id'	=> $id
		);
		return $this->where($param)->find();
	}
	/**
	 * 根据标识码查询一条
	 * 
	 * @param unknown_type $id
	 */
	public function getOneByCode($code){
		$param	= array(
			'doc_code'	=> $code
		);
		return $this->where($param)->find();
	}
	/**
	 * 更新
	 * 
	 * @param unknown_type $param
	 */
	public function update($param){
		return $this->save($param);
	}
	
	
}