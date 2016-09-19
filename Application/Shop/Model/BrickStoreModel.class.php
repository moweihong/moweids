<?php
namespace Shop\Model;
use Think\Model;

class BrickStoreModel extends Model
{
    protected $tableName = 'brick_store';
    
	/**
	 * 获取所有有效的体验店列表
	 * @param $condition 查询条件
	 * @param int $page 每页显示条数
	 * @param int $count 总记录数,不指定自动计算记录数
	 * @param bool $iscache
	 * @return mixed
	 */
	public function getAllBrickStore($condition,$page=0,$count=0,$iscache=true){
		$condition['brickstore_delete']=0;
		if(!$iscache){
			return $this->where($condition)->page($page,$count)->select(array("cache"=>false));
		}else{
			return $this->where($condition)->page($page)->select();
		}

	}

	/**
	 *新增体验店
	 */
	public function addBrickStore($data){
		return $this->insert($data);
	}

	/**
	 * 更新体验店对应的商品数据
	 */
	public  function updateBrickStoreGoods($condition,$data){
		return $this->where($condition)->update($data);
	}

	/**
	 * 追加体验店对应的商品数据
	 */
	public  function addBrickStoreGoods($brick_id,$goods){
		//查询该体验店中已经存在的商品
		$condition['brickstore_id']=$brick_id;
		$condition['store_id']=$_SESSION['store_id'];
		$brick=$this->where($condition)->select();
		$goods_arr=unserialize($brick[0]['brickstore_goods'])?:[];
		$data['brickstore_goods']=serialize(array_values(array_merge($goods_arr,$goods)));
		return $this->where($condition)->update($data);
	}

	/**
	 * 删除数据
	 */
	public  function deleteBrickstore($condtion)
	{
		//检查体验店是否属于该商家
		$isBelong=$this->where(array('store_id'=>$_SESSION['store_id']))->select();
		if($isBelong){
		return $this->where($condtion)->delete();
		}
		return 0;
	}
}

