<?php
/**
 * 购物车管理
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;
class ThemeNavModel extends Model {

    public function __construct() {
       parent::__construct('theme_nav'); 
    }

    /**
     * 取属性值魔术方法
     *
     * @param string $name
     */
    public function __get($name) {
        return $this->$name;
    }
    
    /*
     * 获取所有分类
     */
    public function getAllCate(){
        $model = Model();
        $allCate =  $model->table('goods_class,theme_nav')->join('inner')->on('goods_class.gc_id=theme_nav.class_id')->order('type_sort asc')->select();
        return $allCate;
    }
    
}
