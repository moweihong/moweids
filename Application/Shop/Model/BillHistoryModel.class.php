<?php
namespace Shop\Model;
use Think\Model;
class BillHistoryModel extends Model{
    /**
     * 插入账单明细表信息
     * @param array $data
     * @return int 返回 insert_id
     */
    public function addHistory($data) {
        return $this->table('bill_history')->insert($data);
    }

    /**
     * 取得账户详细列表
     *
     * @param array $condition
     * @param string $field
     * @param number $page
     * @param string $order
     * @return array
     */
    public function getBillHistoryList($condition = array(), $pagesize = '', $fields = '*', $order = '', $limit = '') {
        return $this->table('bill_history')->where($condition)->field($fields)->order($order)->limit($limit)->page($pagesize)->select();
    }
}