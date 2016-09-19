<?php
/**
 * 属性模型
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;

class AttributeModel extends Model {
    const SHOW0 = 0;    // 不显示
    const SHOW1 = 1;    // 显示
	protected  $tablePrefix = 'allwood_';
    public function __construct() {
        parent::__construct();
	}
    
    /**
     * 属性列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getAttributeList($condition, $field = '*') {
        return $this->table($this->tablePrefix.'attribute')->where($condition)->field($field)->order('attr_sort asc')->select();
    }

    /**
     * 属性列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getAttributeShowList($condition, $field = '*') {
        $condition['attr_show'] = self::SHOW1;
        return $this->getAttributeList($condition, $field);
    }
    
    /**
     * 属性值列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getAttributeValueList($condition, $field = '*') {
        return $this->table($this->tablePrefix.'attribute_value')->where($condition)->field($field)->order('attr_value_sort asc,attr_value_id asc')->select();
    }
    
    /**
     * 保存属性值
     * @param array $insert
     * @return boolean
     */
    public function addAttributeValueAll($insert) {
        return $this->table($this->tablePrefix.'attribute_value')->insertAll($insert);
    }
    
    /**
     * 保存属性值
     * @param array $insert
     * @return boolean
     */
    public function addAttributeValue($insert) {
        return $this->table($this->tablePrefix.'attribute_value')->insert($insert);
    }
    
    /**
     * 编辑属性值
     * @param array $update
     * @param array $condition
     * @return boolean
     */
    public function editAttributeValue($update, $condition) {
        return $this->table($this->tablePrefix.'attribute_value')->where($condition)->update($update);
    }
    
    
    /*
     * 链表查询attr值
     */
    public function getAttrValue($condition, $field = '*', $order = '')
    {
        $table = C('DB_PREFIX').'attribute as attribute';
        $join = C('DB_PREFIX').'attribute_value as attribute_value on attribute.attr_id=attribute_value.attr_id';
        $order = !empty($order) ? $order : 'attribute.attr_sort asc, attribute_value.attr_value_sort asc, attribute_value.attr_value_id asc';
        $result = M()->table($table)->join($join)->field($field)->where($condition)->order($order)->select();
        return $result;
    }
}