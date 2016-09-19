<?php
/**
 * 装修公司管理
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;
class DecorateModel extends Model {
    
    protected $tableName  =   'decorate_plan';
    /**
     * 取得单条记录
     *
     * @param array $condition
     */
    public function getDecorateInfo($table, $condition = array(), $field = '*') {
    	if(($table == 'decorate_plan' || $table == 'decorate_effectdraw')){
    		$condition['tesu_deleted'] = 0;
    	}
        return M()->table(C('DB_PREFIX').$table)->where($condition)->field($field)->find();
    }
    
	/**
	 * 读取列表
	 *
	 * @param 
	 * @return array 数组格式的返回结果
	 */
	public function getDecorateList($table, $condition, $field = '*', $order='de_plan_id desc', $pagesize = ''){
		if(($table == 'decorate_plan' || $table == 'decorate_effectdraw')){
    		$condition['tesu_deleted'] = 0;
    	}
        return M()->table(C('DB_PREFIX').$table)->where($condition)->field($field)->order($order)->page($pagesize)->select();
	}
	/**
	 * 读取列表
	 *
	 * @param 
	 * @return array 数组格式的返回结果
	 */
	public function getDecorateListCount($table, $condition){
		if(($table == 'decorate_plan' || $table == 'decorate_effectdraw')){
    		$condition['tesu_deleted'] = 0;
    	}
        return M()->table(C('DB_PREFIX').$table)->where($condition)->count();
	}

    /**
     * 新增
     *@param string $param 表名
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
	public function insertToDecorate($table, $param){
        return M()->table(C('DB_PREFIX').$table)->add($param);
    }
    /**
     * 删除
     */
	public function delDecorate($table, $condition){
        return M()->table(C('DB_PREFIX').$table)->where($condition)->delete();
    }

    /**
     * 更新
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function updDecorate($table, $update, $condition){
        return M()->table(C('DB_PREFIX').$table)->where($condition)->save($update);
    }
}