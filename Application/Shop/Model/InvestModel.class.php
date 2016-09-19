<?php
/**
 * 商品管理
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;

class InvestModel extends Model{
    private $tabel = 'financial_invest';

    public function __construct(){
        parent::__construct('financial_invest');
    }
    
    /**
     * 新增分期购数据
     * 
     * @param array $insert 数据
     * @param string $table 表名
     */
    public function addInvest($insert) {
        $insert['addtime'] = time();
        return $this->table($this->tabel)->insert($insert);
    }

    /**
     * 新增多条分期购数据
     * 
     * @param unknown $insert
     */
    public function addInvestAll($insert) {
        return $this->table($this->tabel)->insertAll($insert);
    }

    /**
     * 获取单条分期购信息
     * 
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getInvest($condition, $field = '*') {
        return $this->table($this->tabel)->field($field)->where($condition)->find();
    }

    /**
     * 更新分期购信息
     * @param unknown $condition
     * @return boolean
     */
    public function updateInvest($condition, $update) {
        return $this->table($this->tabel)->where($condition)->update($update);
    }

    /**
     * 删除分期购信息
     *
     * @param array $condition
     * @return boolean
     */
    public function delInvest($condition) {
        return $this->table($this->tabel)->where($condition)->delete();
    }
}
