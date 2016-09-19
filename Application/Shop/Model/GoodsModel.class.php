<?php
namespace Shop\Model;
use Think\Model;

class GoodsModel extends Model{
    const STATE1 = 1;       // 出售中
    const STATE0 = 0;       // 下架
    const STATE2 = 0;       // 线上商品
    const STATE10 = 10;     // 违规
    const VERIFY1 = 1;      // 审核通过
    const VERIFY0 = 0;      // 审核失败
    const VERIFY10 = 10;    // 等待审核

    /**
     * 新增商品数据
     *
     * @param array $insert 数据
     * @param string $table 表名
     */
    public function addGoods($insert, $table = "goods")
    {
        //var_dump($insert);exit;
        return M()->table(C('DB_PREFIX').$table)->add($insert);
    }

    /**
     * 新增多条商品数据
     *
     * @param unknown $insert
     */
    public function addGoodsAll($insert, $table = 'goods')
    {
        return M()->table(C('DB_PREFIX').$table)->addAll($insert);
    }

    /**
     * 商品SKU列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array 二维数组
     */
    public function getGoodsList($condition, $field = '*', $group = '', $order = '', $limit = 0, $page = 0, $lock = false)
    {
        $condition = $this->_getRecursiveClass($condition);
        $query = $this->field($field)->where($condition);
	    $group && $query->group($group);
	    $limit && $query->limit($limit);
	    $page  && $query->page($page);
	    $order && $query->order($order);
	    $lock  && $query->lock($lock);
        return $query->select();
    }
    
	 public function getGoodsListByArea($condition, $field = '*', $group = '', $order = '', $limit = 0, $page = 0, $lock = false, $count = 0,$storeWhere)
    {
		if($storeWhere['province']) {
			$where['province_id']=$storeWhere['province'];
			if($storeWhere['city']){
				$where['city_id']=$storeWhere['city'];
			}
			if($storeWhere['county']){
				$where['county']=$storeWhere['county'];
			}
			$store_array = M('store')->field('store_id')->where($where)->select();
			if(isset($store_array) && !empty($store_array)){
				foreach ($store_array as $key => $value) {
					$tmp[] = $value['store_id'];    
				}
				unset($store_array);
				$store_array = $tmp;
			}else{
				$store_array = array();
			}
			//店铺必须存在且正常经营
			if(!isset($condition['store_id']) || empty($condition['store_id'])){
				$condition['store_id'] =$store_array? array('in', $store_array):'';
			}else{

			} 	
		}		
        return $this->getGoodsOnlineList($condition, $field, $page, $order, $limit, $group, $lock, $count);
    }

    /**
     * 出售中的商品SKU列表（只显示不同颜色的商品，前台商品索引，店铺也商品列表等使用）
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param number $page
     * @return array
     */
    public function getGoodsListByColorDistinct($condition, $field = '*', $order = 'goods_id asc', $page = 0, $get_count = 0)
    {
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        $condition['is_offline'] = self::STATE2;
        $condition = $this->_getRecursiveClass($condition);
        $field = "CONCAT(goods_commonid,',',color_id) as nc_distinct ," . $field;
        //$count = $this->getGoodsOnlineCount($condition, "distinct CONCAT(goods_commonid,',',color_id)");
        $count = $this->getGoodsOnlineCount($condition, "distinct goods_commonid");//修改bug hotfix 商品搜索分页栏数目显示不正确 
        $goods_list = array();
        if ($count != 0) {
            $goods_list = $this->getGoodsOnlineList($condition, $field, $page, $order, 0, 'goods_commonid', false, $count);
        }
        if ($get_count == 1) {
            return count($goods_list);
        }
        return $goods_list;
    }

    /**
     * 在售商品SKU列表
     *
     * @param array $condition 条件
     * @param string $field 字段
     * @param string $group 分组
     * @param string $order 排序
     * @param int $limit 限制
     * @param int $page 分页
     * @param boolean $lock 是否锁定
     * @return array
     */
    public function getGoodsOnlineList($condition, $field = '*', $page = 0, $order = 'goods_id desc', $limit = 0, $group = 'goods_commonid', $lock = false, $count = 0)
    {
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        $condition['is_offline'] = self::STATE2;
        //$condition['goods_storage'] = array('gt', 0);
        //20160602
        //author:rhodesiax
        //qq:909235815
        //商品的存在必须依附在店铺上
        //目前存在店铺关闭，或者删除店铺后，原店铺中的商品依然可以出现在展示页和搜索也和结算也中的
        //情况，为此，增加下列查询逻辑
        $store_array = M('Store')->field('store_id')->where(array('store_state' => "1"))->select();
        if(isset($store_array) && !empty($store_array)){
            foreach ($store_array as $key => $value) {
                $tmp[] = $value['store_id'];    
            }
            unset($store_array);
            $store_array = $tmp;
        }else{
            $store_array = array();
        }

        //店铺必须存在且正常经营
        if(!isset($condition['store_id']) || empty($condition['store_id'])){
            $condition['store_id'] = array('in', $store_array);
        }else{
            
        }


        return $this->getGoodsList($condition, $field, $group, $order, $limit, $page, $lock, $count);
    }

    /**
     * 商品SUK列表 goods_show = 1 为出售中，goods_show = 0为未出售（仓库中，违规，等待审核）
     *
     * @param unknown $condition
     * @param string $field
     * @return multitype:
     */
    public function getGoodsAsGoodsShowList($condition, $field = '*')
    {
        $field = $this->_asGoodsShow($field);
        return $this->getGoodsList($condition, $field);
    }

    /**
     * 商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonList($condition, $field = '*', $page = '1,10', $order = 'goods_commonid desc')
    {
        $condition = $this->_getRecursiveClass($condition);
        return M('GoodsCommon')->field($field)->where($condition)->order($order)->page($page)->select();
    }

    /**
     * 出售中的商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonOnlineList($condition, $field = '*', $page = 10, $order = "goods_commonid desc")
    {
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        return $this->getGoodsCommonList($condition, $field, $page, $order);
    }

    //连表查询
    public function getGoodsJoinCommonList($type, $condition, $field = '*', $limit = 0, $order = '',$offline=''){
        $model = M();
        if ($type == 'sale'){
            $condition['goods_common.goods_state'] = self::STATE1;
            $condition['goods_common.goods_verify'] = self::VERIFY1;
        }else if ($type == 'inventory'){
            $condition['goods_common.goods_state'] = self::STATE0;
            $condition['goods_common.goods_verify'] = array('in',self::VERIFY1.','.self::VERIFY10);
        }else if($type == 'verify'){
            $condition['goods_common.goods_state'] = array('in',self::STATE1.','.self::STATE10);
            $condition['goods_common.goods_verify'] = array('in',self::VERIFY10.','.self::VERIFY0);
        }
        $default_order = 'goods_common.goods_selltime desc';
        $order = $order != '' ?  $order.','.$default_order : $default_order;
        $join = 'LEFT JOIN allwood_goods as goods on goods_common.goods_commonid=goods.goods_commonid';
        $common_goods_list = $model->table('allwood_goods_common as goods_common')->join($join)->field($field)->where($condition)->limit($limit)->order($order)->group('goods_common.goods_commonid')->select();
        if(!$offline){
            foreach($common_goods_list as $k=>$v){
                if($v['is_offline']!=1){
                    $new_goods_list[]=$v;                 
                }
            }
            $count=count($new_goods_list);
            $goods_info['goods_list']=$new_goods_list;
        }else{
            $count = $model->table('allwood_goods_common as goods_common')->join($join)->field($field)->where($condition)->group('goods_common.goods_commonid')->count();;
            $goods_info['goods_list']=$common_goods_list;
        }
        $goods_info['count'] = $count;
        return $goods_info;
    }

    /**
     * 仓库中的商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonOfflineList($condition, $field = '*', $page = 10, $order = "goods_commonid desc")
    {
        $condition['goods_state'] = self::STATE0;
        $condition['goods_verify'] = self::VERIFY1;
        return $this->getGoodsCommonList($condition, $field, $page, $order);
    }

    /**
     * 违规的商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonLockUpList($condition, $field = '*', $page = 10, $order = "goods_commonid desc")
    {
        $condition['goods_state'] = self::STATE10;
        $condition['goods_verify'] = self::VERIFY1;
        return $this->getGoodsCommonList($condition, $field, $page, $order);
    }

    /**
     * 等待审核或审核失败的商品列表 卖家中心使用
     *
     * @param array $condition 条件
     * @param array $field 字段
     * @param string $page 分页
     * @param string $order 排序
     * @return array
     */
    public function getGoodsCommonWaitVerifyList($condition, $field = '*', $page = 10, $order = "goods_commonid desc")
    {
        if (!isset($condition['goods_verify'])) {
            $condition['goods_verify'] = array('neq', self::VERIFY1);
        }
        return $this->getGoodsCommonList($condition, $field, $page, $order);
    }

    /**
     * 公共商品列表，goods_show = 1 为出售中，goods_show = 0为未出售（仓库中，违规，等待审核）
     */
    public function getGoodsCommonAsGoodsShowList($condition, $field = '*')
    {
        return $this->getGoodsCommonList($condition, $field);
    }

    /**
     * 查询商品SUK及其店铺信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsStoreList($condition, $field = '*')
    {
        $condition = $this->_getRecursiveClass($condition);
        return $this->table('goods,store')->field($field)->join('inner')->on('goods.store_id = store.store_id')->where($condition)->select();
    }

    /**
     * 查询投资购分期购商品信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsFinancialList($condition, $join, $field = '*')
    {
        $condition = $this->_getRecursiveClass($condition);
        return $this->table("$join,goods")->field($field)->join($join)->on("goods.goods_commonid = $join.goods_commonid")->where($condition)->select();
    }

    /**
     * 计算商品库存
     *
     * @param array $goods_list
     * @return array|boolean
     */
    public function calculateStorage($goods_list, $storage_alarm = 0)
    {
        // 计算库存
        if (!empty($goods_list)) {
            $goodsid_array = array();
            foreach ($goods_list as $value) {
                $goodscommonid_array[] = $value['goods_commonid'];
            }
            $goods_storage = $this->getGoodsList(array('goods_commonid' => array('in', $goodscommonid_array)), 'goods_storage,goods_commonid,goods_id');
            $storage_array = array();
            foreach ($goods_storage as $val) {
                if ($storage_alarm != 0 && $val['goods_storage'] <= $storage_alarm) {
                    $storage_array[$val['goods_commonid']]['alarm'] = true;
                }
                $storage_array[$val['goods_commonid']]['sum'] += $val['goods_storage'];
                $storage_array[$val['goods_commonid']]['goods_id'] = $val['goods_id'];
            }
            return $storage_array;
        } else {
            return false;
        }
    }

    /**
     * 更新商品SUK数据
     *
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoods($update, $condition)
    {	
        return $this->where($condition)->save($update);
    }


    /**
     * 更新商品数据
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsCommon($update, $condition)
    {
        return M('GoodsCommon')->where($condition)->save($update);
    }

    /**
     * 更新商品数据
     * @param array $update 更新数据
     * @param array $condition 条件
     * @return boolean
     */
    public function editGoodsCommonNoLock($update, $condition)
    {
        $condition['goods_lock'] = 0;
        return M('GoodsCommon')->where($condition)->save($update);
    }

    /**
     * 锁定商品
     * @param unknown $condition
     * @return boolean
     */
    public function editGoodsCommonLock($condition)
    {
        $update = array('goods_lock' => 1);
        return $this->table('goods_common')->where($condition)->update($update);
    }

    /**
     * 解锁商品
     * @param unknown $condition
     * @return boolean
     */
    public function editGoodsCommonUnlock($condition)
    {
        $update = array('goods_lock' => 0);
        return $this->table('goods_common')->where($condition)->update($update);
    }

    /**
     * 更新商品信息
     *
     * @param array $condition
     * @param array $update1
     * @param array $update2
     * @return boolean
     */
    public function editProduces($condition, $update1, $update2 = array())
    {
        $update2 = empty($update2) ? $update1 : $update2;
        $return1 = $this->editGoodsCommon($update1, $condition);
        $return2 = $this->editGoods($update2, $condition);
        if ($return1 && $return2) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 更新未锁定商品信息
     *
     * @param array $condition
     * @param array $update1
     * @param array $update2
     * @return boolean
     */
    public function editProducesNoLock($condition, $update1, $update2 = array())
    {
        $update2 = empty($update2) ? $update1 : $update2;
        $condition['goods_lock'] = 0;
        $common_array = $this->getGoodsCommonList($condition);
        $common_array = array_under_reset($common_array, 'goods_commonid');
        $commonid_array = array_keys($common_array);
        $where = array();
        $where['goods_commonid'] = array('in', $commonid_array);
        $return1 = $this->editGoodsCommon($update1, $where);
        $return2 = $this->editGoods($update2, $where);
        if ($return1 && $return2) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 商品下架
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesOffline($condition)
    {
        $update = array('goods_state' => self::STATE0, 'tesu_offline_time' => $_SERVER["REQUEST_TIME"]);
        return $this->editProducesNoLock($condition, $update);
    }

    /**
     * 商品上架
     * @param array $condition 条件
     * @return boolean
     */
    public function editProducesOnline($condition)
    {
        //商品上架是否需要审核
        $model_setting = D('Setting');
        $goods_verify = $model_setting->getRowSetting('goods_verify');
        $goods_verify_value=$condition['is_offline']==1?self::VERIFY1:($goods_verify['value'] == 1 ? self::VERIFY10 : self::VERIFY1);//线下不用审核
        $update = array('goods_state' => self::STATE1,'goods_verify' => $goods_verify_value,'goods_addtime'=>time());
        // 禁售商品、审核失败商品不能上架。
        $condition['goods_state'] = self::STATE0;
        $condition['goods_verify'] = array('neq', self::VERIFY0);
        return $this->editProduces($condition, $update);
    }

    /**
     * 违规下架
     *
     * @param array $update
     * @param array $condition
     * @return boolean
     */
    public function editProducesLockUp($update, $condition)
    {
        $update_param['goods_state'] = self::STATE10;
        $update = array_merge($update, $update_param);
        return $this->editProduces($condition, $update, $update_param);
    }

    /**
     * 获取单条商品SKU信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsInfo($condition, $field = '*')
    {
        return $this->field($field)->where($condition)->find();
    }

    /**
     * 获取单条商品SKU信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsOnlineInfo($condition, $field = '*')
    {
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        return $this->field($field)->where($condition)->find();
    }

    /**
     * 获取单条商品SKU信息，goods_show = 1 为出售中，goods_show = 0为未出售（仓库中，违规，等待审核）
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsAsGoodsShowInfo($condition, $field = '*')
    {
        $field = $this->_asGoodsShow($field);
        return $this->getGoodsInfo($condition, $field);
    }

    /**
     * 获取单条商品信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodeCommonInfo($condition, $field = '*')
    {
        return M('GoodsCommon')->field($field)->where($condition)->find();
    }

    /**
     * 获取单条商品信息，goods_show = 1 为出售中，goods_show = 0为未出售（仓库中，违规，等待审核）
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodeCommonAsGoodsShowInfo($condition, $field = '*')
    {
        $field = $this->_asGoodsShow($field);
        return $this->getGoodeCommonInfo($condition, $field);
    }

    /**
     * 获取单条商品信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsDetail($goods_id)
    {
        if ($goods_id <= 0) {
            return null;
        }
        $result1 = $this->getGoodsAsGoodsShowInfo(array('goods_id' => $goods_id));
        if (empty($result1)) {
            return null;
        }
        $result2 = $this->getGoodeCommonAsGoodsShowInfo(array('goods_commonid' => $result1['goods_commonid']));
        $goods_info = array_merge($result2, $result1);

        if(intval($goods_info['store_id'])){
            $store_info = M('Store')->find($goods_info['store_id']);
        }

        $goods_info['spec_value'] = unserialize($goods_info['spec_value']);
        $goods_info['spec_name'] = unserialize($goods_info['spec_name']);
        $goods_info['goods_spec'] = unserialize($goods_info['goods_spec']);
        $goods_info['goods_attr'] = unserialize($goods_info['goods_attr']);

        // 查询所有规格商品
        $spec_array = $this->getGoodsList(array('goods_commonid' => $goods_info['goods_commonid']), 'goods_spec,goods_id,store_id,goods_image,color_id');
        $spec_list = array();       // 各规格商品地址，js使用
        $spec_list_mobile = array();       // 各规格商品地址，js使用
        $spec_image = array();      // 各规格商品主图，规格颜色图片使用
        foreach ($spec_array as $key => $value) {
            $s_array = unserialize($value['goods_spec']);
            $tmp_array = array();
            if (!empty($s_array) && is_array($s_array)) {
                foreach ($s_array as $k => $v) {
                    $tmp_array[] = $k;
                }
            }
            sort($tmp_array);
            $spec_sign = implode('|', $tmp_array);
            $tpl_spec = array();
            $tpl_spec['sign'] = $spec_sign;
            $tpl_spec['url'] = '/goods/index?store_id='.$value['store_id'].'&goods_id='.$value['goods_id'];
            $spec_list[] = $tpl_spec;
            $spec_list_mobile[$spec_sign] = $value['goods_id'];
            $spec_image[$value['color_id']] = thumb($value, 60);
        }
        $spec_list = json_encode($spec_list);

        // 商品多图
        //$image_more = $this->getGoodsImageList(array('goods_commonid' => $goods_info['goods_commonid'], 'color_id' => $goods_info['color_id']), 'goods_image');
        $image_more = $this->getGoodsImageList(array('goods_commonid' => $goods_info['goods_commonid']), 'goods_image');//修改bug 添加多规格时图片不显示
        $goods_image = array();
        $goods_image_mobile = array();
        if (!empty($image_more)) {
            foreach ($image_more as $val) {
                $goods_image[] = "{ 'title' : '', 'levelA' : '" . cthumb($val['goods_image'], 60, $goods_info['store_id']) . "', 'levelB' : '" . cthumb($val['goods_image'], 360, $goods_info['store_id']) . "', 'levelC' : '" . cthumb($val['goods_image'], 360, $goods_info['store_id']) . "', 'levelD' : '" . cthumb($val['goods_image'], 1280, $goods_info['store_id']) . "'}";
                $goods_image_mobile[] = cthumb($val['goods_image'], 360, $goods_info['store_id']);
            }
        } else {
            $goods_image[] = "{ title : '', levelA : '" . thumb($goods_info, 60) . "', levelB : '" . thumb($goods_info, 360) . "', levelC : '" . thumb($goods_info, 360) . "', levelD : '" . thumb($goods_info, 1280) . "'}";
            $goods_image_mobile[] = thumb($goods_info, 360);
        }

        // 商品受关注次数加1
        $_times = cookie('tm_visit_product');
        if (empty($_times)) {
            $this->editGoods(array('goods_click' => array('exp', 'goods_click + 1')), array('goods_id' => $goods_id));
            cookie('tm_visit_product', 1);
            $goods_info['goods_click'] = intval($goods_info['goods_click']) + 1;
        }

        $result = array();
        $result['goods_info'] = $goods_info;
        $result['spec_list'] = $spec_list;
        $result['spec_list_mobile'] = $spec_list_mobile;
        $result['spec_image'] = $spec_image;
        $result['goods_image'] = $goods_image;
        $result['goods_image_mobile'] = $goods_image_mobile;
        $result['store_info'] = array();
        if(!is_null($store_info)&&!empty($store_info)){
            $result['store_info'] = $store_info;
        }
        return $result;
    }

    /**
     * 获得商品SKU某字段的和
     *
     * @param array $condition
     * @param string $field
     * @return boolean
     */
    public function getGoodsSum($condition, $field)
    {
        return $this->where($condition)->sum($field);
    }

    /**
     * 获得商品SKU数量
     *
     * @param array $condition
     * @param string $field
     * @return int
     */
    public function getGoodsCount($condition)
    {
        return $this->table('goods')->where($condition)->count();
    }

    /**
     * 获得出售中商品SKU数量
     *
     * @param array $condition
     * @param string $field
     * @return int
     */
    public function getGoodsOnlineCount($condition, $field = '*', $group = '')
    {
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        //20160602
        //author:rhodesiax
        //qq:909235815
        //商品的存在必须依附在店铺上
        //目前存在店铺关闭，或者删除店铺后，原店铺中的商品依然可以出现在展示页和搜索也和结算也中的
        //情况，为此，增加下列查询逻辑
        $store_array = M('Store')->field('store_id')->where(array('store_state' => "1"))->select();
        if(isset($store_array) && !empty($store_array)){
            foreach ($store_array as $key => $value) {
                $tmp[] = $value['store_id'];    
            }
            unset($store_array);
            $store_array = $tmp;
        }else{
            $store_array = array();
        }
        //店铺必须存在且正常经营
        if(!isset($condition['store_id']) || empty($condition['store_id'])){
            $condition['store_id'] = array('in', $store_array);
        }else{
            
        }
        return $this->where($condition)->count($field);
    }

    /**
     * 获得商品数量
     *
     * @param array $condition
     * @param string $field
     * @return int
     */
    public function getGoodsCommonCount($condition)
    {
        return M('GoodsCommon')->where($condition)->count();
    }

    /**
     * 出售中的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonOnlineCount($condition)
    {
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 仓库中的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonOfflineCount($condition)
    {
        $condition['goods_state'] = self::STATE0;
        $condition['goods_verify'] = self::VERIFY1;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 等待审核的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonWaitVerifyCount($condition)
    {
        $condition['goods_verify'] = self::VERIFY10;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 审核是被的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonVerifyFailCount($condition)
    {
        $condition['goods_verify'] = self::VERIFY0;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 违规下架的商品数量
     *
     * @param array $condition
     * @return int
     */
    public function getGoodsCommonLockUpCount($condition)
    {
        $condition['goods_state'] = self::STATE10;
        $condition['goods_verify'] = self::VERIFY1;
        return $this->getGoodsCommonCount($condition);
    }

    /**
     * 商品图片列表
     *
     * @param array $condition
     * @param array $order
     * @param string $field
     * @return array
     */
    public function getGoodsImageList($condition, $field = '*', $order = 'is_default desc,goods_image_sort asc')
    {
//        $this->cls();
        return M('GoodsImages')->field($field)->where($condition)->order($order)->select();
    }

    /**
     * 浏览过的商品
     *
     * @return array
     */
    public function getViewedGoodsList()
    {
        //取浏览过产品的cookie(最大四组)
        $viewed_goods = array();
        $cookie_i = 0;
		//$cookie='HS8n6f8T54REI1Lz-N5eDmOkg9PurGzLiG70Obv5CivQFz5fN_u';//手动加数据调试
        if (cookie('viewed_goods')) {
            $string_viewed_goods = decrypt(cookie('viewed_goods'), MD5_KEY);
            if (get_magic_quotes_gpc()) $string_viewed_goods = stripslashes($string_viewed_goods);//去除斜杠
            $cookie_array = array_reverse(unserialize($string_viewed_goods));
			//print_r($string_viewed_goods);exit;
            $goodsid_array = array();
            foreach ((array)$cookie_array as $k => $v) {
                $info = explode("-", $v);
                if (is_numeric($info[0])) {
                    $goodsid_array[] = intval($info[0]);
                }
            }
            $viewed_list = $this->getGoodsList(array('goods_id' => array('in', $goodsid_array),), 'goods_id, goods_name, goods_state, goods_price, goods_image, store_id,gc_id');
            $goodsid_new_array=array_unique($goodsid_array);
            unset($goodsid_array);
             foreach($goodsid_new_array as $k=>$v){
                 $goodsid_array[]=$v;
             } 
            $result = array();
            //根据goods_array 的顺序进行排列
            for($i =0 ;$i < sizeof($goodsid_array) ;$i++){
                for($j =0 ;$j<sizeof($viewed_list); $j++)   {
                    //判断goodsid 
                    if($viewed_list[$j]['goods_id'] != $goodsid_array[$i])
                        continue;
                    $result[] =$viewed_list[$j];
                }
            }
            $viewed_list = $result;

            foreach ((array)$viewed_list as $val) {
                $viewed_goods[] = array(
                    "goods_id" => $val['goods_id'],
                    "goods_name" => $val['goods_name'],
                    'goods_state' => $val['goods_state'],
                    "goods_image" => $val['goods_image'],
                    "goods_price" => $val['goods_price'],
                    "store_id" => $val['store_id'],
                    "gc_id"=>$val['gc_id']
                );
            }
        }
        return $viewed_goods;
    }

    /**
     * 删除商品SKU信息
     *
     * @param array $condition
     * @return boolean
     */
    public function delGoods($condition)
    {
        $goods_list = $this->getGoodsList($condition, 'goods_id,store_id');
        if (!empty($goods_list)) {
            $upload = C('TMPL_PARSE_STRING')['__UPLOAD__'];
            foreach ($goods_list as $val) {
                @unlink(ltrim($upload,'\/') . ATTACH_STORE . DS . $goods_list['store_id'] . DS . $goods_list['goods_id'] . '.png');
            }
        }
        return $this->where($condition)->delete();
    }

    /**
     * 删除商品图片表信息
     *
     * @param array $condition
     * @return boolean
     */
    public function delGoodsImages($condition)
    {
        return M('GoodsImages')->where($condition)->delete();
    }

    /**
     * 商品删除及相关信息
     *
     * @param   array $condition 列表条件
     * @return boolean
     */
    public function delGoodsAll($condition)
    {
        $goods_list = $this->getGoodsList($condition, 'goods_id,goods_commonid,store_id');
        if (empty($goods_list)) {
            return false;
        }
        $goodsid_array = array();
        $commonid_array = array();
        foreach ($goods_list as $val) {
            $goodsid_array[] = $val['goods_id'];
            $commonid_array[] = $val['goods_commonid'];
            // 删除二维码
            unlink(BASE_UPLOAD_PATH . DS . ATTACH_STORE . DS . $val['store_id'] . DS . $val['goods_id'] . '.png');
        }
        $commonid_array = array_unique($commonid_array);

        // 删除商品表数据
        $this->delGoods(array('goods_id' => array('in', $goodsid_array)));
        // 删除商品公共表数据
        $this->table('goods_common')->where(array('goods_commonid' => array('in', $commonid_array)))->delete();
        // 删除商品图片表数据
        $this->delGoodsImages(array('goods_commonid' => array('in', $commonid_array)));
        // 删除属性关联表数据
        $this->table('goods_attr_index')->where(array('goods_id' => array('in', $goodsid_array)))->delete();
        // 删除买家收藏表数据
        $this->table('favorites')->where(array('fav_id' => array('in', $goodsid_array), 'fav_type' => 'goods'))->delete();
        // 删除优惠套装商品
        Model('p_bundling')->delBundlingGoods(array('goods_id' => array('in', $goodsid_array)));
        // 优惠套餐活动下架
        Model('p_bundling')->editBundlingCloseByGoodsIds(array('goods_id' => array('in', $goodsid_array)));
        // 推荐展位商品
        Model('p_booth')->delBoothGoods(array('goods_id' => array('in', $goodsid_array)));

        return true;
    }

    /**
     * 删除未锁定商品
     * @param unknown $condition
     */
    public function delGoodsNoLock($condition)
    {
        $condition['goods_lock'] = 0;
        $common_array = $this->getGoodsCommonList($condition, 'goods_commonid');
        $common_array = array_under_reset($common_array, 'goods_commonid');
        $commonid_array = array_keys($common_array);
        return $this->delGoodsAll(array('goods_commonid' => array('in', $commonid_array)));
    }

    /**
     * goods_show = 1 为出售中，goods_show = 0为未出售（仓库中，违规，等待审核）
     *
     * @param string $field
     * @return string
     */
    private function _asGoodsShow($field)
    {
        return $field . ',(goods_state=' . self::STATE1 . ' && goods_verify=' . self::VERIFY1 . ') as goods_show';
    }

    /**
     * 获得商品子分类的ID
     * @param array $condition
     * @return array
     */
    public function _getRecursiveClass($condition)
    {
        if (isset($condition['gc_id']) && !is_array($condition['gc_id'])) {
            //$gc_list = H('goods_class') ? H('goods_class') : H('goods_class', true);
            $gc_list = H('goods_class');
            if (!empty($gc_list[$condition['gc_id']])) {
                $gc_id[] = $condition['gc_id'];
                $gcchild_id = empty($gc_list[$condition['gc_id']]['child']) ? array() : explode(',', $gc_list[$condition['gc_id']]['child']);
                $gcchildchild_id = empty($gc_list[$condition['gc_id']]['childchild']) ? array() : explode(',', $gc_list[$condition['gc_id']]['childchild']);
                $gc_id = array_merge($gc_id, $gcchild_id, $gcchildchild_id);
                $condition['gc_id'] = array('in', $gc_id);
            }
        }
        return $condition;
    }

    /**
     * 取得商品详细信息（优先查询缓存）
     * 如果未找到，则缓存所有字段
     * @param int $goods_id
     * @param string $fields 需要取得的缓存键值, 例如：'*','goods_name,store_name'
     * @return array
     */
    public function getGoodsInfoByID($goods_id, $fields = '*')
    {
            //$goods_info = $this->getGoodsInfo(array('goods_id' => $goods_id));
        return $this->where(array('goods_id' => $goods_id))->find();
    }

    /**
     * 读取商品缓存
     * @param int $goods_id
     * @param string $fields
     * @return array
     */
    private function _rGoodsCache($goods_id, $fields)
    {
        return rcache($goods_id, 'goods', $fields);
    }

    /**
     * 写入商品缓存
     * @param int $goods_id
     * @param array $goods_info
     * @return boolean
     */
    private function _wGoodsCache($goods_id, $goods_info)
    {
        return wcache($goods_id, $goods_info, 'goods');
    }


    /**
     * home_layout布局文件需要的参数
     **/
    public function findArr($condition)
    {
        $model = Model();
        $condition['is_offline'] = self::STATE2;
        $on = 'goods.store_id=store.store_id';
        $data = $model->table('goods,store')->join('left join')->on($on)->where($condition)->field('goods.goods_id,store.store_id')->order('goods.goods_id desc')->group('goods.goods_commonid')->select();
        shuffle($data);
        $newdata = [];
        $goods_ids = [];
        //第一次循环，将数组合并
        foreach ($data as $key => $val) {
            if (!array_key_exists($val['store_id'], $newdata)) {
                $newdata[$val['store_id']] = $val['goods_id'];
            } else {
                $newdata[$val['store_id']] = $newdata[$val['store_id']] . ',' . $val['goods_id'];
            }
        }
        //重构结构
        foreach ($newdata as $key => $val) {
            $newdata[$key] = explode(',', $val);
            arsort($newdata[$key]);
            $newdata[$key] = array_values($newdata[$key]);
        }
        //遍历,获取数据
        for ($i = 0; $i < 6; $i++) {
            foreach ($newdata as $key => $val) {
                if (count($goods_ids) < 6) {
                    if (isset($newdata[$key][$i])) {
                        array_push($goods_ids, $newdata[$key][$i]);
                    }
                } else {
                    break;
                }
            }
        }
        $concat = implode(',', $goods_ids);
        $goods['goods_id'] = array('in', $concat);
        /**/
        $newdata = $this->where($goods)->select();
        return $newdata;
    }

    //通过商品id查找相关的gc_id
    public function findGcid($condition){
        return $this->where($condition)->field('goods_id,gc_id')->select();
    }
    

    public function goodsToGoodsClass($viewed_goods){
        //获取goods class id
        foreach ($viewed_goods as $key=>$val){
            $gc_ids[] = $val['gc_id'];
        }
        
        //通过goods class id 获取goodsclass 信息
        $goods_class=Model("goods_class");
        $condition_class['gc_id'] = array('in', implode(',', array_unique($gc_ids)));
        $category = $goods_class->findArrClass($condition_class);
        
        //统计
        $gc_count = array();
        foreach ($viewed_goods as $key => $val){
            $gc_count[$val['gc_id']] += 1; 
            if(isset($_GET['gc_id'])){
                if($val['gc_id'] != $_GET['gc_id']){
                    unset($viewed_goods[$key]);
                }
            }
        }
        
        $total = 0 ;
        foreach ($category as $key1 => $val2) {
            $category[$key1]['count'] = $gc_count[$val2['gc_id']];
        }
            
        foreach ($category as $key1 => $val2) {
            $total += $category[$key1]['count'];
        }

        $output['count'] = $total;
        $output['data'] = array();
        $output['data'] = $category;
        return $output;
    }

    public function goodsidToGoodsClass($idArr){
        if(!isset($idArr) || empty($idArr)){
            $output['count'] = 0;
            $output['data'] = array();
            return $output;
        }
        $condition_class['goods_id'] = array('in', implode(',', array_unique($idArr)));
        $result = $this->where($condition_class)->select();
        return $this->goodsToGoodsClass($result);
    }

    /*
    * 从所有商家中取一个商品
    * $store_condition 筛选店铺条件
    * $goods_condition 筛选商品条件
    * $goods_order  商品结果排序规则
    * $goods_num 需要取出多少个商品
     */
    public function cherryPicking($store_condition, $goods_condition, $goods_order, $goods_num){
        $store_model = Model('store');

        //获得所有满足条件的在线商品列表
        
        $store_record = $store_model->getStoreOnlineList($store_condition);
        $cherrypick_list = array();
        $count = 0;
        foreach ($store_record as $key => $value) {
            //商品个数足够，跳出
            if($count>=$goods_num)
                break;

            unset($tmp_condition);
            $tmp_condition = $goods_condition;
            $tmp_condition['store_id'] = $value['store_id'];
            $xx = $this->getGoodsOnlineList($tmp_condition, "*", 1, $goods_order, 0);
            $cherrypick_list = array_merge($xx,  $cherrypick_list);
            $count = sizeof($cherrypick_list);
        }
        return $cherrypick_list;
    }

    /*
     * 获取 goods 和 goods_common 表中gc_id 不一致的记录
     */
    public function gcidAlignList(){
        

    }


    /*
     * 判断是否是云端商品如果是
     * 判断商家和买家是否是好友关系，如果不是，提示
     */
    public function is_cloud_goods($goods_info){
        if(is_null($goods_info['store_info'])||empty($goods_info['store_info']))
            return;

        $store_com_type = $goods_info['store_info']['com_type'];
        $store_business_type = $goods_info['store_info']['business_type'];
        if($store_com_type ==  COM_TYPE_DSITRIBUTOR){
            //家居经销商

        }else if($store_com_type == COM_TYPE_FACTORY){
            //工厂
            if(is_null($_SESSION['member_id'])){
                showDialog("这是云端商品页面，只有工厂的好友可以访问！");
            }else if(is_null($_SESSION['store_id'])){
                showDialog('没有权限访问此页面');
            }

            if($this->check_friend($goods_info['store_info']['store_id'], $_SESSION['store_id'])){
                redirect(urlShop('goods', 'factory_goods', array('goods_id' => $goods_info['goods_info']['goods_id'])));
            }else{
        
                showDialog('没有访问权限');
            }

        }

    }

    /*
     * 判断是否是普通商品，如果是，跳转到普通商品页面
     */
    public function is_ordinary_goods($goods_info){
        if(is_null($goods_info['store_info'])||empty($goods_info['store_info']))
            return;
        $store_com_type = $goods_info['store_info']['com_type'];
        $store_business_type = $goods_info['store_info']['business_type'];
        
        if($store_com_type ==  COM_TYPE_DSITRIBUTOR){
            //家居经销商
            redirect(urlShop('goods', 'index', array('goods_id' => $goods_info['goods_info']['goods_id'])));
        }
    }

     /*
     * 判断是否是工厂好友
     * 是工厂自己， true
     * 不是工厂，判断和工厂的关系
     */
    private function check_friend($factory_id, $dist_id){
        //工厂浏览自己的商品
        if($dist_id == $factory_id)
            return true;

        $condition['dealer_store_id'] = $dist_id;
        $condition['factory_store_id'] = $factory_id;
        $condition['c_type'] = 1;
        $condition['is_look'] = 0;
        $result = Model('goods')->table('factory_friend')->where($condition)->select();
        echo json_encode($condition);
        echo json_encode($result);
        if(!is_null($result)&&!empty($result)){
            return true;
        }
        return false;
    }
    /*
     * 获取工厂商品的二级和三级分类
     * $offline 0是经销商 1是ipad  2是工厂 
     */
        public function get_factory_class($offline=2){
        $condition['goods.store_id'] = intval($_GET['store_id']);
        $condition['goods.goods_state'] = self::STATE1;
        $condition['goods.goods_verify'] = self::VERIFY1;
        $condition['goods.is_offline'] = $offline;
        $field = 'goods_class.gc_id,goods_class.gc_deep,goods_class.gc_name,goods_class.gc_parent_id';
        $join = 'INNER JOIN allwood_goods_class as goods_class on goods.gc_id=goods_class.gc_id';
        $on = 'goods.gc_id=goods_class.gc_id';
        $group = 'goods.gc_id';
        $order = 'goods_class.gc_sort asc';
        $good_list = M()->table('allwood_goods as goods')->join($join)->where($condition)->field($field)->group($group)->order($order)->select();
        $good_class = array();
        $class2 = array();
        foreach ($good_list as $val){
            if($val['gc_deep'] == 2){
                $class2[]=$val['gc_id'];
            }else if($val['gc_deep'] == 3){
                $good_class['class3'][]=$val;
                $class2[] = $val['gc_parent_id'];
            }
        }
        if(!empty($class2)){
            $condition_class['gc_id'] = array('in',implode(',', array_unique($class2)));
            $field = 'gc_id,gc_deep,gc_name,gc_parent_id';
            $order = 'gc_sort asc';
            //找到二级分类
            $good_class['class2'] = M('GoodsClass')->where($condition_class)->field($field)->order($order)->select();
        }    
        return $good_class;
    }
    /*
     * 获取工厂首页商品  
     */
    public function get_factory_goods($condition, $field='*', $order='',$page=1)
    {
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        $condition['is_offline'] = 2;//工厂商品
        $group = 'goods_commonid';
        $goods = $this->field($field)->where($condition)->group($group)->order($order)->page($page)->select();
        return $goods;
    }
    /*
     * 获取工厂首页商品  
     */
    public function get_factory_goods_count($condition)
    {
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        $condition['is_offline'] = 2;//工厂商品
        $count =  $this->field($field)->where($condition)->count('distinct goods_commonid');
        return $count;
    }
    /*
     * 获取工厂商品详情页的 推荐商品
     */
    public function get_factory_goods_commend($condition, $field='*', $order='',$limit=''){
        $condition['goods_state'] = self::STATE1;
        $condition['goods_verify'] = self::VERIFY1;
        $condition['goods_commend'] = 1;//推荐
        $condition['is_offline'] = 2;//工厂商品
        $group = 'goods_commonid';
        $goods = $this->field($field)->where($condition)->group($group)->order($order)->limit($limit)->select();
        return $goods;
    }

}

?>
