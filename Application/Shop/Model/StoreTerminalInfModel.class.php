<?php
/**
 * 手机端广告
 *
 * 
 *
 *




 */

namespace Shop\Model;
use Think\Model;

class StoreTerminalInfModel extends Model{
    public function __construct(){
        parent::__construct('store_terminal_inf');
    }

	/**
	 * 列表
	 *
	 * @param array $condition 查询条件
	 * @param int $page 分页数
	 * @param string $order 排序
	 * @param string $field 字段
     * @return array
	 */
	public function getTerminalList($condition, $page = null, $order = 'terminal_usr_id desc', $field = '*'){
            $model = Model();
            $list = $model->table('store_terminal_inf,store')->field('store_terminal_inf.*,store.store_name')->join('left join')->on('store_terminal_inf.store_id=store.store_id')->where($condition)->page($page)->order($order)->select();
            return $list;
	}
	/**
	 * 商家终端总数
	 *
	 * @param array $condition 查询条件
     * @return array
	 */
	public function getTerminalCount($condition){
            $model = Model();
            $count = $model->table('store_terminal_inf,store')->field('store_terminal_inf.*,store.store_name')->join('left join')->on('store_terminal_inf.store_id=store.store_id')->where($condition)->count();
            return $count;
	}        
	/**
	 * 详情
	 *
	 * @param array $condition 查询条件
	 * @param int $page 分页数
	 * @param string $order 排序
	 * @param string $field 字段
     * @return array
	 */
	public function getTerminalInfo($condition,$field = '*'){
            $model = Model();
            $info = $this->field($field)->where($condition)->find();
            return $info;
	}        
	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @param array $condition 条件
	 * @return bool 布尔类型的返回结果
	 */
	public function editTerminal($param, $condition){
            return $this->where($condition)->update($param);
	}  
	/**
	 * 新增
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function addTerminal($param){
            return $this->insert($param);	
	}        





}
