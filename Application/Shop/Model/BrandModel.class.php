<?php

/**
 * 
 * 商品品牌模型
 * 
 */
namespace Shop\Model;
use Think\Model;

class BrandModel extends Model
{
    protected $tableName = 'brand';
    
    /**
     * 通过的品牌列表
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getBrandPassList($condition, $field = '*') {
        $condition['brand_apply'] = 1;
        return $this->where($condition)->field($field)->select();
    }
    
    /**
     * 查询品牌数量
     * @param array $condition
     * @return array
     */
    public function getBrandCount($condition) {
        return $this->where($condition)->count();
    }
    
	/**
	 * 品牌列表
	 *
	 * @param array $condition 检索条件
	 * @return array 数组结构的返回结果
	 */
	public function getBrandList($condition,$page=''){
		$condition_str = $this->_condition($condition);
//		$param = array();
//		$param['table'] = 'brand';
//		$param['order'] = $condition['order'] ? $condition['order'] : 'brand_sort';
//		$param['where'] = $condition_str;
//		$param['field'] = $condition['field'];
//		$param['group'] = $condition['group'];
//		$param['limit'] = $condition['limit'];
  //      var_dump($condition_str);exit;
        $field = $condition['field'];
        $order = $condition['order'] ? $condition['order'] : 'brand_sort';
        $group = $condition['group'];
        $limit = $condition['limit'];
		//$result = Db::select($param,$page);
        $result = $this->field($condition['field'])->where($condition_str)->order($order)->limit($limit)->group($group)->select();
        
		return $result;
	}

	/**
	 * 一组品牌列表
	 *
	 * @param array $condition
	 * @return array 数组结构的返回结果
	 */
	public function getBrandLists($condition){
		$result = $this->where($condition)->select();
		return $result;
	}


	/**
	 * 构造检索条件
	 *
	 * @param int $id 记录ID
	 * @return array $rs_row 返回数组形式的查询结果
	 */
	private function _condition($condition){
		//$condition_str = '';
		$condition_str = array();
		
		if ($condition['brand_class'] != ''){
			//$condition_str .= " and brand_class = '". $condition['brand_class'] ."'";
			$condition_str['brand_class'] = $condition['brand_class'];
		}
		if ($condition['brand_recommend'] != ''){
			//$condition_str .= " and brand_recommend = '". intval($condition['brand_recommend']) ."'";
			$condition_str['brand_recommend']=intval($condition['brand_recommend']);
		}
		if ($condition['no_brand_id'] != ''){
			//$condition_str .= " and brand_id != '". intval($condition['no_brand_id']) ."'";
			$condition_str['brand_id'] =array('NEQ',intval($condition['no_brand_id']));
		}
		if ($condition['brand_id'] != ''){
//			$condition_str .= " and brand_id = '". intval($condition['brand_id']) ."'";
			$condition_str['brand_id'] = intval($condition['brand_id']);
		}
		if ($condition['no_in_brand_id'] != ''){
//			$condition_str .= " and brand_id NOT IN( ". $condition['no_in_brand_id'] ." )";
			$condition_str['brand_id'] = array('NOT IN',$condition['no_in_brand_id']);
		}
		if ($condition['brand_name'] != ''){
//			$condition_str .= " and brand_name = '". $condition['brand_name'] ."'";
			$condition_str['brand_name'] = $condition['brand_name'];
		}
		if ($condition['like_brand_name'] != ''){
//			$condition_str .= " and brand_name like '%". $condition['like_brand_name'] ."%'";
			$condition_str['brand_name'] = array('LIKE' ,'%'. $condition['like_brand_name'] .'%');
		}
		if ($condition['brand_apply'] != ''){
			$condition_str['brand_apply'] = $condition['brand_apply'];
		}
		if($condition['storeid_equal'] != '') {
			//$condition_str	.= " and store_id = '{$condition['storeid_equal']}'";
			$condition_str['store_id'] = $condition['storeid_equal'];
		}
		if($condition['store_id'] != ''){
			//$condition_str	.= " and store_id in(".$condition['store_id'].")";
			$condition_str['store_id'] = array('IN',$condition['store_id']);
		}
		if($condition['class_id'] != ''){
//			$condition_str	.= " and  class_id in(".$condition['class_id'].")";
			$condition_str['class_id'] = array('IN',$condition['class_id']);
		}
		return $condition_str;
	}
	
	/**
	 * 取单个品牌的内容
	 *
	 * @param int $brand_id 品牌ID
	 * @return array 数组类型的返回结果
	 */
	public function getOneBrand($where){
		return $this->where($where)->find();
	}
	
	/**
	 * 新增
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function add($param){
		if (empty($param)){
			return false;
		}
		if (is_array($param)){
			$tmp = array();
			foreach ($param as $k => $v){
				$tmp[$k] = $v;
			}
			$result = Db::insert('brand',$tmp);
			return $result;
		}else {
			return false;
		}
	}
	
	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @return bool 布尔类型的返回结果
	 */
	public function edit($param){
		if (empty($param)){
			return false;
		}
		if (is_array($param)){
			$tmp = array();
			foreach ($param as $k => $v){
				$tmp[$k] = $v;
			}
			$where = " brand_id = '". $param['brand_id'] ."'";
			$result = Db::update('brand',$tmp,$where);
			return $result;
		}else {
			return false;
		}
	}
	
	/**
	 * 删除品牌
	 *
	 * @param int $id 记录ID
	 * @return bool 布尔类型的返回结果
	 */
	public function del($id){
		if (intval($id) > 0){
			$where = " brand_id = '". intval($id) ."'";
			$result = Db::delete('brand',$where);
			return $result;
		}else {
			return false;
		}
	}
    
}
