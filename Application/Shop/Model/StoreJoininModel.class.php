<?php
/**
 * 店铺入住模型
 *
 * 
 *
 *

 */
namespace Shop\Model;
use Think\Model;
class StoreJoininModel extends Model
{
    protected $tableName = 'store_joinin';
    
	/**
	 * 读取列表 
	 * @param array $condition
	 *
	 */
	public function getList($condition,$page='',$order='',$field='*'){
        $result = $this->field($field)->where($condition)->page($page)->order($order)->select();
        return $result;
	}
	
	/**
	 * 店铺入住数量
	 * @param unknown $condition
	 */
	public function getStoreJoininCount($condition) {
	    return  $this->where($condition)->count();
	}

    /**
	 * 读取单条记录
	 * @param array $condition
	 *
	 */
    public function getOne($condition){
        $result = $this->where($condition)->find();
        return $result;
    }

    public function storeJoinPayed(){
        $param = array();
        $param['joinin_state'] = STORE_JOIN_STATE_PAY;
        return $this->modify($param, array('member_id'=>$_SESSION['member_id']));
    }

    public function storeJoinPayed2($member_id){
        $param = array();
        $param['joinin_state'] = STORE_JOIN_STATE_PAY;
        return $this->modify($param, array('member_id'=>$member_id));
    }

	/*
	 *  判断是否存在 
	 *  @param array $condition
     *
	 */
	public function isExist($condition) {
        $result = $this->getOne($condition);
        if(empty($result)) {
            return FALSE;
        }
        else {
            return TRUE;
        }
	}

	/*
	 * 增加 
	 * @param array $param
	 * @return bool
	 */
    public function save($param){
        return $this->add($param);	
    }
	
	/*
	 * 增加 
	 * @param array $param
	 * @return bool
	 */
    public function saveAll($param){
        return $this->addAll($param);	
    }
	
	/*
	 * 更新
	 * @param array $update
	 * @param array $condition
	 * @return bool
	 */
    public function modify($update, $condition){
        //因为store_joinin表没有主键 只能用M方法
        $joiin = M('StoreJoinin');
        return $joiin->where($condition)->save($update);
        
    }
	
	/*
	 * 删除
	 * @param array $condition
	 * @return bool
	 */
    public function drop($condition){
        return $this->where($condition)->delete();
    }
}

