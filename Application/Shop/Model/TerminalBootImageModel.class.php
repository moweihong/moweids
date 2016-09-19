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

class TerminalBootImageModel extends Model{
    public function __construct(){
        parent::__construct('terminal_boot_image');
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
	public function getTerAdList($condition, $page = null, $order = 'id desc', $field = '*'){
        $link_list = $this->field($field)->where($condition)->page($page)->order($order)->select();

		//整理图片链接
		if (is_array($link_list)){
			foreach ($link_list as $k => $v){
				if (!empty($v['boot_image'])){
					$link_list[$k]['boot_image_url'] = "../data/upload".'/'.ATTACH_TERMINAL.'/ad'.'/'.$v['boot_image'];
				}
			}
		}

		return $link_list;
	}
	/**
	 * 取单个内容
	 *
	 * @param int $id ID
	 * @return array 数组类型的返回结果
	 */
	public function getTerAdInfoByID($id){
		if (intval($id) > 0){
            $condition = array('id' => $id);
            $result = $this->where($condition)->find();
			return $result;
		}else {
			return false;
		}
	}
	/**
	 * 取单个内容
	 *
	 * @param int $id ID
	 * @return array 数组类型的返回结果
	 */
	public function getTerAdCount(){
		return Db::getCount('terminal_boot_image');
	}
	/**
	 * 新增
	 *
	 * @param array $param 参数内容
	 * @return bool 布尔类型的返回结果
	 */
	public function addTerAd($param){
        return $this->insert($param);	
	}
	
	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @param array $condition 条件
	 * @return bool 布尔类型的返回结果
	 */
	public function editTerAd($param, $condition){
        return $this->where($condition)->update($param);
	}
	
	/**
	 * 删除
	 *
	 * @param int $id 记录ID
	 * @return bool 布尔类型的返回结果
	 */
	public function delTerAd($id){
		if (intval($id) > 0){
            //删除图片
			$tmp = $this->getTerAdInfoByID($id);
			if (!empty($tmp['boot_image'])){
				@unlink(BASE_ROOT_PATH.DS.DIR_UPLOAD.DS.ATTACH_TERMINAL.'/ad/'.$tmp['boot_image']);
			}

            $condition = array('id' => $id);
            return $this->where($condition)->delete();
        } else {
            return false;
        }
	}	
}
