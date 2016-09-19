<?php
/**
 *
 * 商品图片控制器
 *
 */

namespace Shop\Model;
use Think\Model;

class GoodsImagesModel extends Model
{
    protected $tableName = 'goods_images';
    
    //插入图片
    public function insertImage($ins){
        return $this->addAll($ins);
    }

    //删除图片
    public function delImages($conditon){
        return $this->where($conditon)->delete();
    }

    //获取图片
    public function getImages($condition){
        return $this->where($condition)->select();
    }
}
