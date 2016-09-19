<?php
/**
 * 买家收藏
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;
class FavoritesModel extends Model{
    protected $tableName  =   'favorites';
    
    /**
     * 收藏列表
     * 
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getFavoritesList($condition, $field = '*', $page = 0 , $order = 'fav_time desc') {
        return $this->where($condition)->order($order)->page($page)->select();
    }
    
    /**
     * 收藏商品列表
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getGoodsFavoritesList($condition, $field = '*', $page = 0, $order = 'fav_time desc', $catA = 0) {
        if(!$catA){
            $condition['fav_type'] = 'goods';
            return $this->getFavoritesList($condition, '*', $page, $order);    
        }else{
            //连表查询
            $model = Model();
            $condition2['favorites.member_id '] = $condition['member_id'];
            $condition2['goods.gc_id'] = $catA;
            $fav_list = $model->table('favorites,goods')->join('left join')->on('favorites.fav_id=goods.goods_id')->where($condition2)->page(C('record_per_page'))->order($order)->select();
            return $fav_list;
        }
        
    }
    
    /**
     * 收藏店铺列表
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getStoreFavoritesList($condition, $field = '*', $page = 0, $order = 'fav_time desc') {
        $condition['fav_type'] = 'store';
        return $this->getFavoritesList($condition, $page, $order);
    }
    
    //连表查询
    public function getStoreFavoritesByScidList($condition, $field = '*', $page = 0, $order = 'fav_time desc'){
        $model = Model();
        $con['favorites.fav_type'] = 'store';
        $con['favorites.member_id'] = $condition['member_id'];
        if (isset($condition['sc_id'])) {
            $con['store.sc_id'] = $condition['sc_id'];
        }
		$con['favorites.tesu_deleted'] = 0;
        $fav_list = $model->table('favorites,store')->field($field)->join('left join')->on('favorites.fav_id=store.store_id')->where($con)->page(C('record_per_page'))->order($order)->select();
        return $fav_list;
    }
    
// 	/**
// 	 * 收藏列表
// 	 *
// 	 * @param array $condition 检索条件
// 	 * @param obj $obj_page 分页对象
// 	 * @return array 数组类型的返回结果
// 	 */
// 	public function getFavoritesList($condition,$page = ''){
// 		$condition_str = $this->_condition($condition);
// 		$param = array(
// 					'table'=>'favorites',
// 					'where'=>$condition_str,
// 					'order'=>$condition['order'] ? $condition['order'] : 'fav_time desc'
// 				);		
// 		$result = Db::select($param,$page);
// 		return $result;
// 	}	
	/**
	 * 取单个收藏的内容
	 *
	 * @param array $condition 查询条件
	 * @param string $field 查询字段
	 * @return array 数组类型的返回结果
	 */
	public function getOneFavorites($condition,$field='*'){

        return $this->where($condition)->find();
		
	}
	
	/**
	 * 新增收藏
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function addFavorites($param){
		if (empty($param)){
			return false;
		}
        return $this->add($param);
	}
	
	/**
	 * 更新收藏数量
	 * 
	 * 
	 * @param string $table 表名
	 * @param array  $update 更新内容
	 * @param array  $param  相应参数
	 * @return bool 布尔类型的返回结果
	 */
	public function updateFavoritesNum($table, $update, $param){
		$where = $this->_condition($param);
		//echo $table;
		//print_r($update);
		//print_r($param);exit;
		$res=$this->where($where)->save($update);
		//echo $this->getLastSql();exit;
		return $res;
	}
	
	/**
	 * 验证是否为当前用户收藏
	 *
	 * @param array $param 条件数据
	 * @return bool 布尔类型的返回结果
	 */
	public function checkFavorites($fav_id,$fav_type,$member_id){
		if (intval($fav_id) == 0 || empty($fav_type) || intval($member_id) == 0){
			return true;
		}
		$result = self::getOneFavorites($fav_id,$fav_type,$member_id);
		if ($result['member_id'] == $member_id){
			return true;
		}else {
			return false;
		}
	}
	
	/**
	 * 删除
	 *
	 * @param int $id 记录ID
	 * @return array $rs_row 返回数组形式的查询结果
	 */
	public function delFavorites($condition){
		if (empty($condition)){
			return false;
		}
		$condition_str = '';	
		if ($condition['fav_id'] != ''){
			$condition_str .= " and fav_id='{$condition['fav_id']}' ";
		}
		if ($condition['member_id'] != ''){
			$condition_str .= " and member_id='{$condition['member_id']}' ";
		}
		if ($condition['fav_type'] != ''){
			$condition_str .= " and fav_type='{$condition['fav_type']}' ";
		}
		if ($condition['fav_id_in'] !=''){
			$condition_str .= " and fav_id in({$condition['fav_id_in']}) ";
		}
		return Db::delete('favorites',$condition_str);
	}
	/**
	 * 构造检索条件
	 *
	 * @param array $condition 检索条件
	 * @return string 字符串类型的返回结果
	 */
	public function _condition($condition){
		$condition_str = '1';
		
		if ($condition['member_id'] != ''){
			$condition_str .= " and member_id = '".$condition['member_id']."'";
		}
		if ($condition['fav_type'] != ''){
			$condition_str .= " and fav_type = '".$condition['fav_type']."'";
		}
		if ($condition['goods_id'] != ''){
			$condition_str .= " and goods_id = '".$condition['goods_id']."'";
		}
		if ($condition['store_id'] != ''){
			$condition_str .= " and store_id = '".$condition['store_id']."'";
		}
		if ($condition['fav_id_in'] !=''){
			$condition_str .= " and allwood_favorites.fav_id in({$condition['fav_id_in']}) ";
		}
		return $condition_str;
	}
}
