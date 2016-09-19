<?php
/**
 * 系统设置内容
 * 
 */
namespace Shop\Model;
use Think\Model;

class SettingModel extends Model
{

	protected $tableName = 'setting';
    protected $trueTableName = 'allwood_setting';
    protected $tablePrefix = 'allwood_'; 
    
	/**
	 * 读取系统设置信息
	 *
	 * @param string $name 系统设置信息名称
	 * @return array 数组格式的返回结果
	 */
	public function getRowSetting($name){
//		$param	= array();
//		$param['table']	= 'setting';
//		$param['where']	= "name='".$name."'";
//		$result	= Db::select($param);
        $where = "name='".$name."'";
		$result	= $this->where($where)->select();
		if(is_array($result) and is_array($result[0])){
			return $result[0];
		}
		return false;
	}
	
	/**
	 * 读取系统设置列表
	 *
	 * @param 
	 * @return array 数组格式的返回结果
	 */
	public function getListSetting(){
		$param = array();
		//$param['table'] = 'allwood_setting';
		//$result = Db::select($param);
		$model = D('Setting');
		$result = $model->where(1)->select();
		/**
		 * 整理
		 */
		if (is_array($result)){
			$list_setting = array();
			foreach ($result as $k => $v){
				$list_setting[$v['name']] = $v['value'];
			}
		}
		return $list_setting;
	}
	
	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @return bool 布尔类型的返回结果
	 */
	public function updateSetting($param){
		if (empty($param)){
			return false;
		}

		if (is_array($param)){
			foreach ($param as $k => $v){
				$tmp = array();
				$specialkeys_arr = array('statistics_code');
				$tmp['value'] = (in_array($k,$specialkeys_arr) ? htmlentities($v,ENT_QUOTES) : $v);
				$where = " name = '". $k ."'";
				$model = M('setting');
				$result = $model->where($where)->save($tmp);
				if ($result > 0){
					return true;
				}
			}
		}else {
			return false;
		}
	}
}

