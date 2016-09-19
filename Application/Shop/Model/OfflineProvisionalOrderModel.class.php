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

class OfflineProvisionalOrderModel extends Model{
    public function __construct(){
        parent::__construct('offline_provisional_order');
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
	public function getOfflineOrderList($condition, $page = null, $order = 'provisional_order_id desc', $field = '*'){
            $model = Model();
            if($condition['order_type']==1){
                $list = $model->table('offline_provisional_order,member')->field('offline_provisional_order.*')->join('left join')
                        ->on('offline_provisional_order.customer_id=member.mid')->where($condition)->page($page)->order($order)->select();
            }else{
                $list = $model->table('offline_provisional_order,offline_provisional_customer')->field('offline_provisional_order.*,offline_provisional_customer.usr_name,offline_provisional_customer.usr_tel')
                     ->join('left join')->on('offline_provisional_order.customer_id=offline_provisional_customer.provisional_customer_id')->where($condition)->page($page)->order($order)->select();                
            }

            foreach($list as $k=>$v){
                $goods_list=Model('offline_provisional_order_goods')->where(array('provisional_order_id'=>$v['provisional_order_id']))->select();
                foreach($goods_list as $k2=>$v2){
                    $list[$k]['goods_count']+=$v2['goods_num'];
                }
                $list[$k]['terminal_usr_name']=Model('store_terminal_inf')->where(array('terminal_usr_id'=>$v['terminal_usr_id']))->get_field('usr_name');
            }
            //echo "<pre>";print_r($list);exit;
            return $list;
	}
      
	/**
	 * 详情
	 *
	 * @param array $condition 查询条件
	 * @param int $page 分页数
	 * @param string $order 排序
	 * @param string $field 字段
     * @return array
	 */
	public function getOfflineOrderInfo($condition,$field = '*'){
            $model = Model();
            if($condition['order_type']==1){
                $info =$model->table('offline_provisional_order,member')->field('offline_provisional_order.*,member.mobile')->join('left join')
                ->on('offline_provisional_order.customer_id=member.mid')->where($condition)->find();            
            }else{
                $info = $model->table('offline_provisional_order,offline_provisional_customer')->field('offline_provisional_order.*,offline_provisional_customer.usr_name,offline_provisional_customer.usr_tel')
                         ->join('left join')->on('offline_provisional_order.customer_id=offline_provisional_customer.provisional_customer_id')->where($condition)->find();                
            }
            $info['goods_list']=$model->table('offline_provisional_order_goods')->where(array('provisional_order_id'=>$info['provisional_order_id']))->select();
            //echo "<pre>";print_r($info);
            foreach($info['goods_list'] as $k=>$v){
                $goodsInfo=$model->table('goods')->where(array('goods_id'=>$v['goods_id']))->find();
                $spec_value=$v['spec_string']?$v['spec_string']:'无';
                //$spec_value=Model('goods_common')->where(array('goods_commonid'=>$goodsInfo['goods_commonid']))->get_field('spec_value');//print_r($spec_value);
//                $spec = unserialize($spec_value)?unserialize($spec_value):[];//print_r(unserialize($spec_value));print_r($spec);
//                $spec_str = '';
//                if (is_array($spec) && !empty($spec)) {
//                    foreach ($spec as $k1 => $v1) {
//                        if (is_array($v1) && !empty($v1)) {
//                            foreach ($v1 as $k2 => $v2) {
//                                $spec_str .= $v2 . ',';
//                            }
//                        }elseif(!is_array ($v1)&&!empty ($v1)){
//                            $spec_str=$v1;
//                        }
//                    }
//                }
//                if ($spec_str) {
//                    $spec_str = substr($spec_str,-1)==','?substr($spec_str, 0, -1):$spec_str;
//                } else {
//                    $spec_str = '无';
//                }
                $info['goods_list'][$k]['spec_value'] = $spec_value;
                $info['goods_list'][$k]['remark_goods_image_info']=Model()->table('offline_provisional_order_goods_image')->where(array('order_goods_id'=>$v['order_goods_id']))->select();
                $info['goods_list'][$k]['goodsInfo'] = $goodsInfo;
            }//print_r($info);exit;
            return $info;
	}    
       
	/**
	 * 删除
	 *
	 * @param array $condition 条件
	 * @return bool 布尔类型的返回结果
	 */
	public function delOfflineOrder($condition){
            return $this->where($condition)->delete();	
	}  
	/**
	 * 更新信息
	 *
	 * @param array $param 更新数据
	 * @param array $condition 条件
	 * @return bool 布尔类型的返回结果
	 */
	public function editOfflineOrder($param, $condition){
            return $this->where($condition)->update($param);
	}         




}
