<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Shop\Model;
use Think\Model;

class StoreBindClassModel extends Model
{
    protected $tableName = 'store_bind_class';
    
	/**
	 * 读取列表 
	 * @param array $condition
	 *
	 */
	public function getStoreBindClassList($condition,$page='',$order='',$field='*'){
        $result = $this->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
	}

    /**
	 * 读取单条记录
	 * @param array $condition
	 *
	 */
    public function getStoreBindClassInfo($condition){
        $result = $this->where($condition)->find();
        return $result;
    }

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
	 */
    public function addStoreBindClass($param){
        return $this->add($param);
    }
	
	/*
	 * 增加 
	 * @param array $param
	 * @return bool
	 */
    public function addStoreBindClassAll($param){
        return $this->addAll($param);	
    }
	
	/*
	 * 更新
	 * @param array $update
	 * @param array $condition
	 * @return bool
	 */
    public function editStoreBindClass($update, $condition){
        return $this->where($condition)->save($update);
    }
	
	/*
	 * 删除
	 * @param array $condition
	 * @return bool
	 */
    public function delStoreBindClass($condition){
        return $this->where($condition)->delete();
    }
    
}