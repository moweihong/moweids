<?php
namespace Shop\Model;
use Think\Model;

class UserModel extends Model 
{
	
	public function __construct()
	{
		$this->pagenum=8;
		$this->tablePrefix='allwood_';
	}
	
	const STATE1 = 1;       // 出售中
    const STATE0 = 0;       // 下架
    const STATE2 = 0;       // 线上商品
    const STATE10 = 10;     // 违规
    const VERIFY1 = 1;      // 审核通过
    const VERIFY0 = 0;      // 审核失败
    const VERIFY10 = 10;    // 等待审核
	
	//我的订单
	public function myOrder($uid)
	{
		$Order=M('Order');
		$res=$Order->select();
		//var_dump($res);
		return $res;
	}
	
	//我的购物车
	public function myShopCart($uid)
	{
		echo $uid;
	}
	
	//我的资料
	public function myInfo($uid)
	{
		$Member=M('Member');
		$res=$Member->where('member_id='.$uid)->find();
		//var_dump($res);
		return $res;
	}
	
	//更新我的资料
	public function updateMyINfo($uid,$data)
	{
		return 1;
	}
	
	
	//收货地址
	public function myAddress($uid)
	{
		$Address=M('Address');
		$res=$Address->where('member_id='.$uid.' and tesu_deleted=0')->select();
		//var_dump($res);
		return $res;
	}
	
	//修改收货地址
	public function changeMyAddress($aid,$data)
	{
		$Address=M('Address');
		$res=$Address->find($aid);
		if(is_array($data))
		{
			foreach($data as $key=>$val)
			{
				$res->$key=$val;
			}
			$result=$res->save();
		}
		else
		{
			$result=FALSE;	
		}
		//var_dump($res);
		return $result;
	}
	
	/**
     * 收藏商品列表
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getGoodsFavoritesList($condition, $field = '*', $page = 0, $order = 'fav_time desc', $catA = 0) {
        //print_r($condition);exit;	
        if(!$catA){
        	//print_r($condition);exit;	
            $condition['fav_type'] = 'goods';
            return $this->getFavoritesList($condition, '*', $page, $order);    
        }else{
            //连表查询
            $model = M('Favorites');
            $condition2['allwood_favorites.member_id '] = $condition['member_id'];
            $condition2['allwood_goods.gc_id'] = $catA;
			//print_r($condition2);exit;
            $fav_list = $model->join(' allwood_goods on allwood_favorites.fav_id=allwood_goods.goods_id')->where($condition2)->page($page.','.$this->pagenum)->order($order)->select();
            return $fav_list;
        }
        
    }
	
	 /**
     * 收藏列表
     * 
     * @param array $condition
     * @param treing $field
     * @param int $page
     * @param string $order
     * @return array
     */
    public function getFavoritesList($condition, $field = '*', $page = 0 , $order = 'fav_time desc') {
        $Favorites=M('Favorites');	
        $res=$Favorites->where($condition)->order($order)->page($page.','.$this->pagenum)->select();
    	return $res;
    	//print_r($Favorites->getLastSql());
	}
	
	
	 //连表查询
    public function getStoreFavoritesByScidList($condition, $field = '*', $page = 0, $order = 'fav_time desc'){
        $field=$this->addHouZui($field);
		//print_r($order);
		//$order=$this->addHouZui($order);
		//print_r($order);
		//$condition=$this->addHouZui($condition);
		//print_r($condition);
        $model = M('Favorites');
        $con['allwood_favorites.fav_type'] = 'store';
        $con['allwood_favorites.member_id'] = $condition['member_id'];
        if (isset($condition['sc_id'])) {
            $con['allwood_store.sc_id'] = $condition['sc_id'];
        }
		$con['allwood_favorites.tesu_deleted'] = 0;
        $fav_list = $model->field($field)->join('allwood_store on allwood_favorites.fav_id=allwood_store.store_id')->where($con)->page($page.','.$this->pagenum)->order($order)->select();
        return $fav_list;
    }
	
	protected function addHouZui($string)
	{
		if(is_array($string))
		{   $arr=array();
			foreach($string as $k=>$v)
			{
				$key='allwood_'.$k;
				$arr[$key]=$v;
			}
			return $arr;
		}
		elseif(strstr($string,','))
		{
			$res=explode(',',$string);
			foreach($res as $k=>$v)
			{
				$res[$k]='allwood_'.$v;
			}
			$string=implode(',', $res);
		}else
		{
			$string='allwood_'.$string;
		}
		return $string;
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

        if (cookie('viewed_goods')) {
            $string_viewed_goods = decrypt(cookie('viewed_goods'), MD5_KEY);
            if (get_magic_quotes_gpc()) $string_viewed_goods = stripslashes($string_viewed_goods);//去除斜杠
            $cookie_array = array_reverse(unserialize($string_viewed_goods));
            $goodsid_array = array();
            foreach ((array)$cookie_array as $k => $v) {
                $info = explode("-", $v);
                if (is_numeric($info[0])) {
                    $goodsid_array[] = intval($info[0]);
                }
            }
            $viewed_list = $this->getGoodsList(array('goods_id' => array('in', $goodsid_array),'goods_state'=>1,'tesu_delete'=>0), 'goods_id, goods_name, goods_state, goods_price, goods_image, store_id,gc_id');
            $gc_id=array();
			$raminNum=8-count($viewed_list);
            if(count($viewed_list)<8)
            {
            	foreach($viewed_list as $v)
            	{
            		$gc_id=$v['gc_id'];
            	}
				$goodsinfo=M()->table('allwood_goods')->where(array('gc_id' => array('in', $gc_id),'goods_id' => array('not in', $goodsid_array),'goods_state'=>1,'tesu_delete'=>0))->field('goods_id, goods_name, goods_state, goods_price, goods_image, store_id,gc_id')->limit($raminNum)->select();
       			//echo M()->getLastSql();
       			//print_r($goodsinfo);exit;
       			if(empty(!$goodsinfo))
       			{
       				$viewed_list=array_merge($goodsinfo,$viewed_list);
       			}
       			$countnum=count($goodsinfo);
       			if($countnum<$raminNum)
       			{
       				$limitNum=$raminNum-$countnum;
       				$goodsinfoSec=M()->table('allwood_goods')->where(array('gc_id' => array('not in', $gc_id),'goods_id' => array('not in', $goodsid_array),'goods_state'=>1,'tesu_delete'=>0))->field('goods_id, goods_name, goods_state, goods_price, goods_image, store_id,gc_id')->limit($limitNum)->select();
       			}
				$viewed_list=array_merge($goodsinfoSec,$viewed_list);
			}
			 return $viewed_list;

        }
        return $viewed_goods;
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
        return $this->table('goods')->where($condition)->update($update);
    }
	
	/*
	 * 编辑店铺
     *
	 * @param array $update 更新信息
	 * @param array $condition 条件
	 * @return bool
	 */
    public function editStore($update, $condition){
        //清空缓存
//      $store_list = $this->getStoreList($condition);
//      foreach ($store_list as $value) {
//          wcache($value['store_id'], array(), 'store_info');
//      }

        return $this->where($condition)->update($update);
    }
	
	/**
	 * 类别列表
	 *
	 * @param array $condition 检索条件
	 * @return array 数组结构的返回结果
	 */
	public function getClassList($condition){
		$condition_str = $this->_store_class_condition($condition);
		$param = array();
		$param['table'] = $this->tablePrefix.'store_class';
		$param['order'] = $condition['order'] ? $condition['order'] : 'sc_parent_id asc,sc_sort asc,sc_id asc';
		$param['where'] = $condition_str;
		//print_r($param);exit;
		$result = M()->select($param);
		return $result;
	}
	
	/**
	 * 构造检索条件
	 *
	 * @param int $id 记录ID
	 * @return string 字符串类型的返回结果
	 */
	private function _store_class_condition($condition){
		$condition_str = '1';
		if (isset($condition['sc_parent_id'])){
			$condition_str .= " and sc_parent_id = '". intval($condition['sc_parent_id']) ."'";
		}
		if ($condition['no_sc_id'] != ''){
			$condition_str .= " and sc_id != '". intval($condition['no_sc_id']) ."'";
		}
		if ($condition['sc_name'] != ''){
			$condition_str .= " and sc_name = '". $condition['sc_name'] ."'";
		}
		
		return $condition_str;
	}

	public function goodsidToGoodsClass($idArr){
		$Goods=M('Goods');
        if(!isset($idArr) || empty($idArr)){
            $output['count'] = 0;
            $output['data'] = array();
            return $output;
        }
        $condition_class['goods_id'] = array('in', implode(',', array_unique($idArr)));
        $result = $Goods->where($condition_class)->select();
        return $this->goodsToGoodsClass($result);
    }

    public function goodsToGoodsClass($viewed_goods){
        //获取goods class id
        foreach ($viewed_goods as $key=>$val){
            $gc_ids[] = $val['gc_id'];
        }
        
        //通过goods class id 获取goodsclass 信息
        $goods_class=M("Goods_class");
        $condition_class['gc_id'] = array('in', implode(',', array_unique($gc_ids)));
        $category = $goods_class->where($condition_class)->select();
        
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

	 /**
     * 查询商品SUK及其店铺信息
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getGoodsStoreList($condition, $field = '*')
    {
    	if($field!='*')
    	{
    		$res=explode(',',$field);
			foreach($res as $k=>$v)
			{
				$res[$k]='allwood_'.$v;
			}
			$field=implode(',', $res);
			//var_dump($field);exit;
    	}
        $Goods=M('Goods');
        return $Goods->join('allwood_store on allwood_goods.store_id = allwood_store.store_id')->where($condition)->field($field)->select();
    }
	
	 /**
     * 获取商品销售排行
     *
     * @param int $store_id 店铺编号
     * @param int $limit 数量
     * @return array	商品信息
     */
    public function getHotSalesList($store_id, $limit = 5, $get_count = 0) {
        $prefix = 'store_hot_sales_list_' . $limit;
        //$model_goods = M('Goods');
        $hot_sales_list = $this->getGoodsOnlineList(array('store_id' => $store_id), '*', 0, 'goods_salenum desc', $limit);    
        return $hot_sales_list;
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
        //$condition = $this->_getRecursiveClass($condition);
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
     * 获得商品子分类的ID
     * @param array $condition
     * @return array
     */
//  public function _getRecursiveClass($condition)
//  {
//  	//print_r($condition);
//      if (isset($condition['gc_id']) && !is_array($condition['gc_id'])) {
//          $gc_list = H('goods_class') ? H('goods_class') : H('goods_class', true);
//          if (!empty($gc_list[$condition['gc_id']])) {
//              $gc_id[] = $condition['gc_id'];
//              $gcchild_id = empty($gc_list[$condition['gc_id']]['child']) ? array() : explode(',', $gc_list[$condition['gc_id']]['child']);
//              $gcchildchild_id = empty($gc_list[$condition['gc_id']]['childchild']) ? array() : explode(',', $gc_list[$condition['gc_id']]['childchild']);
//              $gc_id = array_merge($gc_id, $gcchild_id, $gcchildchild_id);
//              $condition['gc_id'] = array('in', $gc_id);
//          }
//      }
//		//print_r($condition);exit;
//      return $condition;
//  }
	
	
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
        return M()->table('allwood_goods')->where($condition)->count($field);
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
    public function getGoodsOnlineList($condition, $field = '*', $page = 0, $order = 'goods_id desc', $limit = 0, $group = '', $lock = false, $count = 0)
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
    public function getGoodsList($condition, $field = '*', $group = '', $order = '', $limit = 0, $page = 0, $lock = false, $count = 0)
    {
    	
        //$condition = $this->_getRecursiveClass($condition);
        $ret = M()->table('allwood_goods')->field($field)->where($condition)->group($group)->order($order)->limit(0,$limit)->lock($lock)->select();
        //print_r(M()->getLastSql());
        return $ret;
    }
	
	/**
	 * 取单个分类的内容
	 *
	 * @param int $id 分类ID
	 * @return array 数组类型的返回结果
	 */
	public function getOneClass($id){
		if (intval($id) > 0){
			$param = array();
			$param['table'] = 'allwood_store_class';
			$param['field'] = 'sc_id';
			$param['value'] = intval($id);
			$result = M()->select($param);
			return $result;
		}else {
			return false;
		}
	}
	
	//收藏店铺
	public function myCollectShops($uid)
	{
		
	}
	
	
	/**
	 * 更新收藏数量
	 * 
	 * 
	 * @param string $table 表名
	 * @param array  $update 更新内容
	 * @param array  $param  相应参数
	 * @return bool 布尔类型的返回结果
	 */
	public function updateFavoritesNum($table, $update, $param){
		$where = $this->_condition($param);
		return Db::update($table,$update,$where);
	}
	
	//浏览记录
	public function myLookInfo($uid)
	{
		
	}
	
	//短信发送内容入库
	public function saveInfo($arr)
	{
		$data['phone']=$arr['phone'];
		$data['user']=$arr['sendUser'];
		$data['info']=$arr['info'];
		$data['is_all']=$arr['type'];
		$info=M('Infomation');
		$res=$info->data($data)->add();
		return $res;
		
	}
	
	//短信查看列表
	public function getUserInfo($page)
	{
		$info=M('Infomation');
		$res=$info->page($page.',25')->select();
		$count=$info->count();
		$data['info']=$res;
		$data['count']=$count;
		return $data;
	}


	/**
	 * 构造检索条件
	 *
	 * @param array $condition 检索条件
	 * @return string 字符串类型的返回结果
	 */
	public function _condition($condition){
		$condition_str = '';
		
		if ($condition['member_id'] != ''){
			$condition_str .= " and member_id = '".$condition['member_id']."'";
		}
		if ($condition['fav_type'] != ''){
			$condition_str .= " and fav_type = '".$condition['fav_type']."'";
		}
		if ($condition['goods_id'] != ''){
			$condition_str .= " and goods_id = '".$condition['goods_id']."'";
		}
		if ($condition['store_id'] != ''){
			$condition_str .= " and store_id = '".$condition['store_id']."'";
		}
		if ($condition['fav_id_in'] !=''){
			$condition_str .= " and favorites.fav_id in({$condition['fav_id_in']}) ";
		}
		return $condition_str;
	}
	
	
	//计算乐装的账户余额
	public function tongJiLzMoney($uid)
	{
		$model_lz=M('Mem_decoratefund');
		$shouru=$model_lz->where('type=1 and userid='.$uid)->sum('amount');
		$zhichu=$model_lz->where('type=0 and userid='.$uid)->sum('amount');
		$remain=$shouru-$zhichu;
		if($remain<0) $remain=0;
		return $remain;
	}
	
	

}
?>