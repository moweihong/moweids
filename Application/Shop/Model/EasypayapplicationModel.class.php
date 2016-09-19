<?php
namespace Shop\Model;
use Think\Model;
class EasypayapplicationModel extends Model {

	protected $tableName = 'easypay_application';
    

    /**
     * 更新
     * 如果member_id 检查不存在，插入
     * 如果member_id 检查存在，更新
     * @param  [type] $member_id [description]
     * @param  [type] $data      [description]
     * @return [type]            [description]
     */
    public function insert_update($member_id, $data){
    	$model=M('easypay_application');
        $data['member_id'] = $member_id;
        $result =$model->where(array('member_id' => $member_id))->find();
        if(!$result){
        	$data['usrid']=0;
			$data['usr_native_province']=0;
			$data['usr_native_city']=0;
			$data['add_areainfo']=0;
            return $model->add($data);
        }else{
            return $this->where(array('member_id' => $member_id))->save($data);
        }
    }

    /**
     * 获取单条记录
     *
     * @param  [type] $member_id [description]
     * @param  [type] $field     [description]
     * @return [type]            [description]
     */
    public function getApplicationInfo($condition, $field = '*'){
        $result = $this->where($condition)->field($field)->find();
        return $result;
    }
}
