<?php
/**
 * Created by PhpStorm.
 * User: wch
 * Date: 2016/8/4 0004
 * Time: 15:19
 */
namespace Shop\Model;
use Think\Model;
class EffectdrawTypeModel extends Model
{
    protected $tableName        =   'effectdraw_type';
    
    /*
	 * 添加风格
     *
	 * @param array $param 风格信息
	 * @return bool
	 */
    public function addEffectdrawType($param){
        return $this->add($param);
    }

    /*
	 * 编辑风格
     *
	 * @param array $update 更新信息
	 * @param array $condition 条件
	 * @return bool
	 */
    public function editEffectdrawType($update, $condition){
        return $this->where($condition)->save($update);
    }

    /*
	 * 查找风格
     *
	 * @param array $param
	 * @return bool
	 */
    public function findEffectdrawType($condition = array(), $field = '*'){
        return $this->where($condition)->field($field)->find();
    }

    /*
	 * 获取风格列表
     *
	 * @param array $param
	 * @return bool
	 */
    public function selectEffectdrawType($condition = array(), $field = '*', $page = ''){
        return $this->where($condition)->field($field)->page($page)->select();
    }
}