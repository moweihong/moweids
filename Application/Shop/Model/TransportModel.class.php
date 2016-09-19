<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Shop\Model;
use Think\Model;

class TransportModel extends Model
{
    protected $tableName = 'transport';
    
    /**
     * 增加运费模板
     *
     * @param unknown_type $data
     * @return unknown
     */
    public function addTransport($data)
    {
        return $this->add($data);
    }

    /**
     * 增加各地区详细运费设置
     *
     * @param unknown_type $datagetAllTransportInfo
     * @return unknown
     */
    public function addExtend($data)
    {
        return M('transport_extend')->addAll($data);
    }

    /**
     * 取得一条运费模板信息
     *
     * @return unknown
     */
    public function getTransportInfo($condition)
    {
        return $this->where($condition)->find();
    }

    /**
     * 取得1000条运费模板信息
     * @param $condition tiaojian
     * @param bool $iccache 是否缓存
     * @return mixed
     */
    public function getAllTransportInfo($condition,$iccache=true)
    {
        if(!$iccache){//不缓存
            return $this->limit(1000)->where($condition)->select(array("cache"=>false));
        }else{
            return $this->limit(1000)->where($condition)->select();
        }

    }


    /**
     * 取得一条运费模板扩展信息
     *
     * @return unknown
     */
    public function getExtendInfo($condition)
    {
        return M('transport_extend')->where($condition)->select();
    }


    /**
     * 取得1000条模板扩展信息
     *
     * @return unknown
     */
    public function getAllExtendInfo($condition)
    {
        return M('transport_extend')->limit(1000)->where($condition)->select();
    }


    /**
     * 删除运费模板
     *
     * @param unknown_type $id
     * @return unknown
     */
    public function delTansport($condition)
    {
        try {
            $this->startTrans();
            $delete = $this->where($condition)->delete();
            if ($delete) {
                $delete = M('transport_extend')->where(array('transport_id' => $condition['id']))->delete();
            }
            if (!$delete) throw new Exception();
            $this->commit();
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
        return true;
    }

    /**
     * 删除运费模板扩展信息
     *
     * @param unknown_type $transport_id
     * @return unknown
     */
    public function delExtend($transport_id)
    {
        return M('transport_extend')->where(array('transport_id' => $transport_id))->delete();
    }

    /**
     * 取得运费模板列表
     *
     * @param unknown_type $condition
     * @param unknown_type $page
     * @param unknown_type $order
     * @return unknown
     */
    public function getTransportList($condition = array(), $pagesize = '', $order = 'id desc')
    {
        return $this->where($condition)->order($order)->page($pagesize)->select();
    }

    /**
     * 取得扩展信息列表
     *
     * @param unknown_type $condition
     * @param unknown_type $order
     * @return unknown
     */
    public function getExtendList($condition = array(), $order = 'is_default')
    {
        return M('transport_extend')->where($condition)->order($order)->select();
    }

    public function transUpdate($data)
    {
        return $this->save($data);
    }

    /**
     * 检测运费模板是否正在被使用
     *
     */
    public function isUsing($id)
    {
        if (!is_numeric($id)) return false;
        $goods_info = M('Goods')->where(array('transport_id' => $id))->field('goods_id')->find();
        return $goods_info ? true : false;
    }

    /**
     * 计算某地区某运费模板ID下的商品总运费，如果运费模板不存在或，按免运费处理
     *
     * @param int $transport_id
     * @param int $buy_num
     * @param int $area_id
     * @return number/boolean
     */
    public function calc_transport($transport_id, $buy_num, $area_id)
    {
        if (empty($transport_id) || empty($buy_num) || empty($area_id)) return 0;
        $extend_list = $this->getExtendList(array('transport_id' => $transport_id));
        if (empty($extend_list)) {
            return 0;
        } else {
            return $this->calc_unit($area_id, $buy_num, $extend_list);
        }
    }

    /**
     * 计算某个具单元的运费
     *
     * @param 配送地区 $area_id
     * @param 购买数量 $num
     * @param 运费模板内容 $extend
     * @return number 总运费
     */
    private function calc_unit($area_id, $num, $extend)
    {
        if (!empty($extend) && is_array($extend)) {
            foreach ($extend as $v) {
                if (strpos($v['area_id'], "," . $area_id . ",") !== false) {
                    if ($num <= $v['snum']) {
                        //在首件数量范围内
                        $calc_total = $v['sprice'];
                    } else {
                        //超出首件数量范围，需要计算续件
                        $calc_total = sprintf('%.2f', ($v['sprice'] + ceil(($num - $v['snum']) / $v['xnum']) * $v['xprice']));
                    }
                }
                if ($v['is_default'] == 1) {
                    if ($num <= $v['snum']) {
                        //在首件数量范围内
                        $calc_default_total = $v['sprice'];
                    } else {
                        //超出首件数量范围，需要计算续件
                        $calc_default_total = sprintf('%.2f', ($v['sprice'] + ceil(($num - $v['snum']) / $v['xnum']) * $v['xprice']));
                    }
                }
            }
            //如果运费模板中没有指定该地区，取默认运费
            if (!isset($calc_total) && isset($calc_default_total)) {
                $calc_total = $calc_default_total;
            }
        }
        return $calc_total;
    }

    /*****
     * $goodsid 商品id
     * $area_id 区域id
     * $num 购买数量
     * 计算最终运费,返回包含快递,物流等方式的运费*/

    public function _calcTransportFee($goodsid, $area_id, $num)
    {
        //获取商品的重量和体积信息
        $model_goods = D('Goods');
        $goods_detail = $model_goods->getGoodsDetail($goodsid, '*');
        $goods_weight = $goods_detail['goods_info']['goods_weight'];//重量
        $goods_volume = $goods_detail['goods_info']['goods_volume'];//体积
        $transport_id = $goods_detail['goods_info']['transport_id'];//运费模版id
        //获取运费模版以及拓展运费模版
        $transport = $this->getTransportInfo(array('id' => $transport_id));
        $condition['transport_id'] = $transport_id;
        if(is_null($area_id)){
            $condition['area_id'] = "";
        }
        $transport_extend = $this->getExtendInfo($condition);
        //获取计费类型
        $cash_type = $transport['cash_type'];// 1.按照体积计费  2.按照重量计费
        $res = [];

        switch ($cash_type) {
            case '1':
                //按照体积扣费
                foreach ($transport_extend as $k=>$v){
                    $area_arr=explode(',',$v['area_id']);
                    if(in_array($area_id,$area_arr) || empty($v['area_id'])){
                        //捕获计费方式,属于该计费类型
                        $res[$k]['transport_type']=$v['transport_type'];
                        $res[$k]['transport_area_id']=$v['area_id'];
                        $res[$k]['transport_fee']=sprintf('%.2f',ceil(($goods_volume*$num-$v['snum'])/$v['xnum'])*$v['xprice']+$v['sprice']);
                    }
                }
                break;
            case '2':
                //按照重量计费
                foreach ($transport_extend as $k=>$v){
                    $area_arr=explode(',',$v['area_id']);
                    if(in_array($area_id,$area_arr) || empty($v['area_id'])){
                        //捕获计费方式,属于该计费类型
                        $res[$k]['transport_type']=$v['transport_type'];
                        $res[$k]['transport_area_id']=$v['area_id'];
                        $res[$k]['transport_fee']=sprintf('%.2f',ceil(($goods_weight*$num-$v['snum'])/$v['xnum'])*$v['xprice']+$v['sprice']);
                    }
                }
                break;
            default:
                //旧的计算方式
                break;
        }
        //重组res数组,如果包含了area_id,就把该运送方式的全国方式去除
        $ress=[];
        foreach ($res as $k=>$v){
            if(!array_key_exists($v['transport_type'],$ress)){
                $ress[$v['transport_type']]=$v;
            }else{
                if($v['transport_area_id']){
                $ress[$v['transport_type']]=$v;
                }
            }
        }
        return $ress;
    }


    /*
     * $goodsid 商品id
     * $area_id 区域id
     * $num 购买数量
     * 计算最终运费,返回包含快递,物流等方式的运费*/

    public function _calcTransportFeeTotal($goodsid, $area_id, $num,$transport_type='1')
    {

        //获取商品的重量和体积信息
        $model_goods = D('Goods');
        $goods_detail = $model_goods->getGoodsDetail($goodsid, '*');
        $goods_weight = $goods_detail['goods_info']['goods_weight'];//重量
        $goods_volume = $goods_detail['goods_info']['goods_volume'];//体积
        $transport_id = $goods_detail['goods_info']['transport_id'];//运费模版id
        //获取运费模版以及拓展运费模版
        $condition['id'] = $transport_id;

        $transport = $this->getTransportInfo(array('id' => $transport_id));
        unset($condition);
        $condition['transport_id'] = $transport_id;
        $condition['transport_type'] = $transport_type;
        //如果area_id 为空，增加筛选条件，查询全国运费模板
        if(is_null($area_id)){
            $condition['area_id'] = "";
        }

        $transport_extend = $this->getExtendInfo($condition);
        //获取计费类型
        $cash_type = $transport['cash_type'];// 1.按照体积计费  2.按照重量计费
        $res = [];
        switch ($cash_type) {
            case '1':
                //按照体积扣费
                foreach ($transport_extend as $k=>$v){
                    $area_arr=explode(',',$v['area_id']);
                    if(in_array($area_id,$area_arr) || empty($v['area_id'])){
                        //捕获计费方式,属于该计费类型
                        $res[$k]['transport_type']=$v['transport_type'];
                        $res[$k]['transport_area_id']=$v['area_id'];
                        $res[$k]['transport_fee']=sprintf('%.2f',ceil(($goods_volume*$num-$v['snum'])/$v['xnum'])*$v['xprice']+$v['sprice']);
                    }
                }
                break;
            case '2':
                //按照重量计费
                foreach ($transport_extend as $k=>$v){
                    $area_arr=explode(',',$v['area_id']);
                    if(in_array($area_id,$area_arr) || empty($v['area_id'])){
                        //捕获计费方式,属于该计费类型
                        $res[$k]['transport_type']=$v['transport_type'];
                        $res[$k]['transport_area_id']=$v['area_id'];
                        $res[$k]['transport_fee']=sprintf('%.2f',ceil(($goods_weight*$num-$v['snum'])/$v['xnum'])*$v['xprice']+$v['sprice']);
                    }
                }
                break;
            default:
                //旧的计算方式
                break;
        }
        //重组res数组,如果包含了area_id,就把该运送方式的全国方式去除
        $ress=[];
        foreach ($res as $k=>$v){
            if(!array_key_exists($v['transport_type'],$ress)){
                $ress[$v['transport_type']]=$v;
            }else{
                if($v['transport_area_id']){
                    $ress[$v['transport_type']]=$v;
                }
            }
        }
        $money=0;
        //获取对应类型的运费
        foreach ($ress as $k=>$v){
            if($v['transport_type']==$transport_type){
                $money=$v['transport_fee'];
            }
            if($money==0){
                $money=$v['transport_fee'];
            }
        }
        //如果获取不到对应类型的运费,则自动转换物流类型

        return $money;
    }
}

