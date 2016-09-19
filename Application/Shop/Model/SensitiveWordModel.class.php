<?php
/**
 * 从excel中读取敏感词,插入到数据库表中
 */
namespace Shop\Model;
use Think\Model;
class SensitiveWordModel extends Model{
	public function __construct()
	{
		parent::__construct('sensitive_word');
	}

	/**
	 * 添加敏感词
	 *
	 * @param array $input
	 * @return bool
	 */
	public function add($input){

    	$ex=$this->checkExist($input);
		if(!$ex){
		$this->insert($input);
		}
	}

	/**
	 *检查是否存在数据中
	 */
	public function checkExist($condition){

		return Model()->query("SELECT * FROM allwood_sensitive_word where binary word='{$condition['word']}'");
	}

	/**
	 *读取所有敏感词库
	 */
	public function getAllRecord($condition){
		return $this->where($condition)->field('word')->limit(100000)->select();
	}


}