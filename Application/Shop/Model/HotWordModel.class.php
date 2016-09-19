<?php
/**
 * 热词模型
 *
 *
 *
 *

 */
namespace Shop\Model;
use Think\Model;

class HotWordModel extends Model
{
    public function __construct()
    {
        parent::__construct('hot_word');
    }

    /**
     * 类别详细
     *
     * @param   array $condition 条件
     * $param   string  $field  字段
     * @return  array   返回一维数组
     */
    public function getGoodsClassInfo($condition, $field = '*'){
        $result = $this->field($field)->where($condition)->find();
        return $result;
    }

    /**
     * 取得店铺绑定的分类
     *
     * @param   number $store_id 店铺id
     * @param   number $pid 父级分类id
     * @param   number $deep 深度
     * @return  array   二维数组
     */
    public function getGoodsClass($store_id, $pid = 0, $deep = 1){
        // 读取商品分类
        $gc_list = $this->getGoodsClassList(array('gc_parent_id' => $pid), 'gc_id, gc_name, type_id');
        // 如果店铺ID不为商城店铺的话，读取绑定分类
        if (!checkPlatformStore()) {
            $gc_list = array_under_reset($gc_list, 'gc_id');
            $model_storebindclass = Model('store_bind_class');
            $gcid_array = $model_storebindclass->getStoreBindClassList(array('store_id' => $store_id), '', "class_{$deep} asc", "distinct class_{$deep}");
            if (!empty($gcid_array)) {
                $tmp_gc_list = array();
                foreach ($gcid_array as $value) {
                    if (isset($gc_list[$value["class_{$deep}"]])) {
                        $tmp_gc_list[] = $gc_list[$value["class_{$deep}"]];
                    }
                }
                $gc_list = $tmp_gc_list;
            } else {
                return array();
            }
        }
        return $gc_list;
    }

    /**
     * 删除商品分类
     * @param unknown $condition
     * @return boolean
     */
    public function updHotWord($condition, $data){
        $this->where($condition)->update($data);
        return $this->where(array('hw_parent_id' => $condition['hw_id']))->update($data);
    }

    /**
     * 删除商品分类
     *
     * @param array $gcids
     * @return boolean
     */
    public function delHotWordByHwIdString($gcids){
        $gcids = explode(',', $gcids);
        if (empty($gcids)) {
            return false;
        }
        $goods_class = H('hot_word') ? H('hot_word') : H('hot_word', true);
        $gcid_array = array();
        /* p($goods_class);
        p($gcids);die(); */
        foreach ($gcids as $gc_id) {
            $child = (!empty($goods_class[$gc_id]['child'])) ? explode(',', $goods_class[$gc_id]['child']) : array();
            $childchild = (!empty($goods_class[$gc_id]['childchild'])) ? explode(',', $goods_class[$gc_id]['childchild']) : array();
            $gcid_array = array_merge($gcid_array, array($gc_id), $child, $childchild);
        }
        
        //修改分类状态
        $this->updHotWord(array('hw_id' => array('in', $gcid_array)), array('status' => 0));
        return true;
    }

    /**
     * 前台头部的商品分类
     *
     * @param   number $update_all 更新
     * @return  array   数组
     */
    public function get_all_category($update_all = 0)
    {
        $file_name = BASE_DATA_PATH . '/cache/index/category.php';
        if (!file_exists($file_name) || $update_all == 1) {//文件不存在时更新或者强制更新时执行
            $class_list = $this->getGoodsClassList(array(), 'gc_id, gc_name, type_id, gc_parent_id, gc_sort');
            header("Content-Type:text/html; charset=utf-8");


            $gc_list = array();
            $class1_deep = array();//第1级关联第3级数组
            $class2_ids = array();//第2级关联第1级ID数组
            $type_ids = array();//第2级分类关联类型
            if (is_array($class_list) && !empty($class_list)) {
                foreach ($class_list as $key => $value) {
                    $p_id = $value['gc_parent_id'];//父级ID
                    $gc_id = $value['gc_id'];
                    $sort = $value['gc_sort'];
                    if ($p_id == 0) {//第1级分类
                        $gc_list[$gc_id] = $value;
                    } elseif (array_key_exists($p_id, $gc_list)) {//第2级
                        $class2_ids[$gc_id] = $p_id;
                        $type_ids[] = $value['type_id'];
                        $gc_list[$p_id]['class2'][$gc_id] = $value;
                    } elseif (array_key_exists($p_id, $class2_ids)) {//第3级
                        $parent_id = $class2_ids[$p_id];//取第1级ID
                        $gc_list[$parent_id]['class2'][$p_id]['class3'][$gc_id] = $value;
                        $class1_deep[$parent_id][$sort][] = $value;
                    }
                }


                $type_brands = $this->get_type_brands($type_ids);//类型关联品牌
                foreach ($gc_list as $key => $value) {
                    $gc_id = $value['gc_id'];
                    $pic_name = BASE_UPLOAD_PATH . '/' . ATTACH_COMMON . '/category-pic-' . $gc_id . '.jpg';
                    if (file_exists($pic_name)) {
                        $gc_list[$gc_id]['pic'] = UPLOAD_SITE_URL . '/' . ATTACH_COMMON . '/category-pic-' . $gc_id . '.jpg';
                    }
                    $class3s = $class1_deep[$gc_id];

                    if (is_array($class3s) && !empty($class3s)) {//取关联的第3级
                        $class3_n = 0;//已经找到的第3级分类个数
                        ksort($class3s);//排序取到分类
                        foreach ($class3s as $k3 => $v3) {
                            if ($class3_n >= 9) {//最多取9个
                                break;
                            }
                            foreach ($v3 as $k => $v) {
                                if ($class3_n >= 9) {
                                    break;
                                }
                                if (is_array($v) && !empty($v)) {
                                    $p_id = $v['gc_parent_id'];
                                    $gc_id = $v['gc_id'];
                                    $parent_id = $class2_ids[$p_id];//取第1级ID
                                    $gc_list[$parent_id]['class3'][$gc_id] = $v;
                                    $class3_n += 1;
                                }
                            }
                        }
                    }
                    $class2s = $value['class2'];
                    if (is_array($class2s) && !empty($class2s)) {//第2级关联品牌
                        foreach ($class2s as $k2 => $v2) {
                            $p_id = $v2['gc_parent_id'];
                            $gc_id = $v2['gc_id'];
                            $type_id = $v2['type_id'];
                            $gc_list[$p_id]['class2'][$gc_id]['brands'] = $type_brands[$type_id];
                        }
                    }
                }

                //var_dump($gc_list);die;
                F('category', $gc_list, 'cache/index');
            }
        } else {
            //var_dump($file_name);die;
            $gc_list = include $file_name;
        }
        return $gc_list;
    }

    /**
     * 类型关联品牌
     *
     * @param   array $type_ids 类型
     * @return  array   数组
     */
    public function get_type_brands($type_ids = array())
    {
        $brands = array();//品牌
        $type_brands = array();//类型关联品牌
        if (is_array($type_ids) && !empty($type_ids)) {
            $type_ids = array_unique($type_ids);
            $type_list = $this->table('type_brand')->where(array('type_id' => array('in', $type_ids)))->limit(10000)->select();
            if (is_array($type_list) && !empty($type_list)) {
                $brand_list = $this->table('brand')->field('brand_id,brand_name,brand_pic')->where(array('brand_apply' => 1))->limit(10000)->select();
                if (is_array($brand_list) && !empty($brand_list)) {
                    foreach ($brand_list as $key => $value) {
                        $brand_id = $value['brand_id'];
                        $brands[$brand_id] = $value;
                    }
                    foreach ($type_list as $key => $value) {
                        $type_id = $value['type_id'];
                        $brand_id = $value['brand_id'];
                        $brand = $brands[$brand_id];
                        if (is_array($brand) && !empty($brand)) {
                            $type_brands[$type_id][$brand_id] = $brand;
                        }
                    }
                }
            }

        }
        return $type_brands;
    }

    /**
     * 类别列表
     *
     * @param array $condition 检索条件
     * @return array 数组结构的返回结果
     */
    public function getClassList($condition, $field = '*'){
        $condition_str = $this->_condition($condition);
        $param = array();
        $param['table'] = 'hot_word';
        $param['field'] = $field;
        $param['where'] = $condition_str;
        $param['order'] = $condition['order'] ? $condition['order'] : 'hw_parent_id asc,hw_sort asc,hw_id asc';
        $result = Db::select($param);

        return $result;
    }

    /**
     * 构造检索条件
     *
     * @param int $id 记录ID
     * @return string 字符串类型的返回结果
     */
    private function _condition($condition)
    {
        $condition_str = '';

        if (!is_null($condition['hw_parent_id'])) {
            $condition_str .= " and hw_parent_id = '" . intval($condition['hw_parent_id']) . "'";
        }
        if (!is_null($condition['no_hw_id'])) {
            $condition_str .= " and hw_id != '" . intval($condition['no_hw_id']) . "'";
        }
        if ($condition['in_hw_id'] != '') {
            $condition_str .= " and hw_id in (" . $condition['in_hw_id'] . ")";
        }
        if ($condition['hw_name'] != '') {
            $condition_str .= " and hw_name = '" . $condition['hw_name'] . "'";
        }
        if ($condition['gc_id'] != '') {
            $condition_str .= " and gc_id = '" . $condition['gc_id'] . "'";
        }
        if ($condition['status'] != '') {
            $condition_str .= " and status = '" . $condition['status'] . "'";
        }
        if (isset($condition['un_type_name'])) {
            $condition_str .= " and type_name <> ''";
        }
        if ($condition['un_type_id'] != '') {
            $condition_str .= " and type_id <> '" . $condition['un_type_id'] . "'";
        }
        if ($condition['in_type_id'] != '') {
            $condition_str .= " and type_id in (" . $condition['in_type_id'] . ")";
        }

        return $condition_str;
    }

    /**
     * 取单个分类的内容
     *
     * @param int $id 分类ID
     * @return array 数组类型的返回结果
     */
    public function getOneHotWord($id){
        if (intval($id) > 0) {
            $result = $this->where(array('hw_id' => $id))->find();
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 新增
     *
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function add($param){
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            $tmp = array();
            foreach ($param as $k => $v) {
                $tmp[$k] = $v;
            }
            $result = Db::insert('hot_word', $tmp);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function hotWordUpdate($param){
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            $tmp = array();
            foreach ($param as $k => $v) {
                $tmp[$k] = $v;
            }
            $where = " hw_id = '" . $param['hw_id'] . "'";
            $result = Db::update('hot_word', $tmp, $where);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 更新信息
     *
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function updateWhere($param, $condition){
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            $tmp = array();
            foreach ($param as $k => $v) {
                $tmp[$k] = $v;
            }
            $where = $this->_condition($condition);
            $result = Db::update('hot_word', $tmp, $where);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 删除分类
     *
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id){
        if (intval($id) > 0) {
            $where = " hw_id = '" . intval($id) . "'";
            $result = Db::delete('hot_word', $where);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 取分类列表，最多为三级
     *
     * @param int $show_deep 显示深度
     * @param array $condition 检索条件
     * @return array 数组类型的返回结果
     */
    public function getTreeClassList($show_deep = '3', $condition = array()){
        $condition['status'] = 1;
        $class_list = $this->getClassList($condition);
        $hot_word = array();//分类数组
        if (is_array($class_list) && !empty($class_list)) {
            $show_deep = intval($show_deep);
            if ($show_deep == 1) {//只显示第一级时用循环给分类加上深度deep号码
                foreach ($class_list as $val) {
                    if ($val['hw_parent_id'] == 0) {
                        $val['deep'] = 1;
                        $hot_word[] = $val;
                    } else {
                        break;//父类编号不为0时退出循环
                    }
                }
            } else {//显示第二和三级时用递归
                $hot_word = $this->_getTreeClassList($show_deep, $class_list);
            }
        }
        return $hot_word;
    }

    /**
     * 递归 整理分类
     *
     * @param int $show_deep 显示深度
     * @param array $class_list 类别内容集合
     * @param int $deep 深度
     * @param int $parent_id 父类编号
     * @param int $i 上次循环编号
     * @return array $show_class 返回数组形式的查询结果
     */
    private function _getTreeClassList($show_deep, $class_list, $deep = 1, $parent_id = 0, $i = 0)
    {
        static $show_class = array();//树状的平行数组
        if (is_array($class_list) && !empty($class_list)) {
            $size = count($class_list);
            if ($i == 0) $show_class = array();//从0开始时清空数组，防止多次调用后出现重复
            for ($i; $i < $size; $i++) {//$i为上次循环到的分类编号，避免重新从第一条开始
                $val = $class_list[$i];
                $gc_id = $val['hw_id'];
                $gc_parent_id = $val['hw_parent_id'];
                if ($gc_parent_id == $parent_id) {
                    $val['deep'] = $deep;
                    $show_class[] = $val;
                    if ($deep < $show_deep && $deep < 3) {//本次深度小于显示深度时执行，避免取出的数据无用
                        $this->_getTreeClassList($show_deep, $class_list, $deep + 1, $gc_id, $i + 1);
                    }
                }
                if ($gc_parent_id > $parent_id) break;//当前分类的父编号大于本次递归的时退出循环
            }
        }
        return $show_class;
    }

    /**
     * 取指定分类ID下的所有子类
     *
     * @param int /array $parent_id 父ID 可以单一可以为数组
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function getChildClass($parent_id)
    {
        $condition = array('order' => 'gc_parent_id asc,gc_sort asc,gc_id asc');
        $all_class = $this->getClassList($condition);
        if (is_array($all_class)) {
            if (!is_array($parent_id)) {
                $parent_id = array($parent_id);
            }
            $result = array();
            foreach ($all_class as $k => $v) {
                $gc_id = $v['gc_id'];//返回的结果包括父类
                $gc_parent_id = $v['gc_parent_id'];
                if (in_array($gc_id, $parent_id) || in_array($gc_parent_id, $parent_id)) {
                    $parent_id[] = $v['gc_id'];
                    $result[] = $v;
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    public function getHotWordList() {
        $condition['status'] = 1;
        $filed = 'hw_id,hw_name,hw_url,hw_parent_id,gc_id';
        $list = $this->getClassList($condition, $filed);
        
        $hw_class_arr = array();
        $gc_arr = array();
        if (!empty($list)) {
            foreach ($list as $v){
                $parent_id = $v['hw_parent_id'];
                $hw_id = $v['hw_id'];
                $gc_id = $v['gc_id'];
                
                if ($parent_id == 0) {
                    $hw_class_arr[$hw_id] = $v;
                }elseif (array_key_exists($parent_id, $hw_class_arr)){//第二级
                    $hw_class_arr[$parent_id]['class2'][$hw_id] = $v;
                }
            }
            
            if (!empty($hw_class_arr)) {
                foreach ($hw_class_arr as $val){
                    $gc_arr[$val['gc_id']][] = $val;
                }
            }
        }
        
        return $gc_arr;
    }
}