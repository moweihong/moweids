<?php
/*
 * 自检
 */
namespace Admin\Controller;
use Think\Controller;
class CoorController extends Controller {

	/*
     * 
     */
    public function index() {
        $this->display();
    }

    /*
     * 检查goods表和goods_common 表gc_id 是否一致
     * 把不一致的数据读出来
     */
    public function goodsGcId(){
        //$this->lastUpdate(SELF_GC_ID_NOTMATCH);
        $goods_model = Model('shop/goods');
        $sql = "SELECT g.`goods_id`,g.`goods_name`,g.`goods_commonid`,g.`gc_id` AS gc_id_g,c.`gc_id` AS gc_id_c, c.`gc_name` FROM allwood_goods_common AS c ,allwood_goods AS g WHERE c.`goods_commonid` = g.`goods_commonid` AND c.`gc_id`!= g.`gc_id` AND g.`goods_state` =1 ";
        $res = $goods_model ->query($sql);   
        if(IS_POST){
            //给出提示
            //将所有goods_common表中的gc_id 赋值到goods 表中
            foreach ($res as $key => $value) {
                $update['gc_id']       = $value['gc_id_c'];
                $condition['goods_id'] = $value['goods_id'];
                $goods_model->editGoods($update, $condition);
            }
            
            ////$this->log2(SELF_GC_ID_NOTMATCH, $res);
            //showMessage('success');
        }
        $this->assign('list', $res);
        $this->display();
        
    }



    /*
     * 检查 goods_common 表中gc_name 是否一致
     */
    public function checkGcName(){
        $goods_class_model = Model('shop/GoodsClass');
        $goods_common_model = Model('shop/GoodsCommon');
        $goods_model = Model('shop/goods');
        $field="goods_commonid,gc_id,gc_name";
        $condition['goods_state'] = array('NEQ', 10);
        $list = $goods_common_model->where($condition)->field($field)->limit(100)->select();
        foreach ($list as $key => $value) {
            $tmp = $goods_class_model->getGoodsClassLineForTag($value['gc_id']);
             if(strcmp($tmp['gc_tag_name'], $value['gc_name']===0)){
                unset($list[$key]);
                continue;
            }
            $value['gc_name_goods_class'] = $tmp['gc_tag_name']?strval($tmp['gc_tag_name']):"-100";

            $list[$key] = $value;
        }
        if(IS_POST){
            //给出提示
            //将所有goods_common表中的gc_id 赋值到goods 表中
            // foreach ($list as $key => $value) {
            //     //如果gc_name 为 空 ，强制下架
            //     if(strcmp($value['gc_name_goods_class'], "-100") == 0){
            //         $update['goods_state'] = 10;
            //         $condition['goods_commonid'] = $value['goods_commonid'];
            //         $goods_model->editGoodsCommon($update, $condition);
            //         continue;
            //     }

            //     //更新正确的gc_name 上去
            //     $update['gc_name']       = $value['gc_name_goods_class'];
            //     $condition['goods_commonid'] = $value['goods_commonid'];
            //     //修改goods_common
            //     if(!$goods_model->editGoodsCommon($update, $condition)){
            //         log::w('false');
            //     }else{
                    

            //     }
            // }
            // //$this->log2(SELF_GC_NAME_NOTMATCH, $list);
            // showMessage('success');
        }
        $this->assign('list', $list);
        $this->display();
       
    }

    /*
     * 检查 goods.goods_commonid 不存在于goods_common表中数据
     */
    public function goodsNotExist(){
        $this->lastUpdate(SELF_LONEY_GOODS);
        $goods_model = Model('shop/goods');
        $sql = "SELECT `goods_id`,`goods_name`,`goods_commonid` FROM allwood_goods WHERE goods_commonid  NOT IN (SELECT goods_commonid FROM allwood_goods_common WHERE 1) and goods_state <> 10;";
        $res = $goods_model ->query($sql); 
        if(IS_POST){
            foreach ($res as $key => $value) {
                $update['goods_state'] = 10;
                $condition['goods_id'] = $value['goods_id'];
                $goods_model->editGoods($update, $condition);
            }
            //$this->log2(SELF_LONEY_GOODS, $res);
            showMessage('success');
        }  
        $this->assign('list', $res);
        $this->display();
    }


    /*
     * 有goods_common 下面没有挂数据
     */
    public function zeroGoods(){
        $this->lastUpdate(SELF_LONEY_GOODSCOMMON);
        $goods_model = Model('shop/goods');
        $sql = "SELECT goods_commonid,goods_name FROM allwood_goods_common WHERE goods_commonid NOT IN (SELECT  DISTINCT goods_commonid FROM allwood_goods WHERE 1) and goods_state<> 10;";
        $res = $goods_model ->query($sql);
        if(IS_POST){
            foreach ($res as $key => $value) {
                $update['goods_state'] = 10;
                $condition['goods_commonid'] = $value['goods_commonid'];
                $goods_model->editGoodsCommon($update, $condition);
            }
            //$this->log2(SELF_LONEY_GOODSCOMMON, $res);
            showMessage('success');
        }    
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * member 表唯一性检查
     */
    public function memberUniqueMobile(){
        $member_model = Model('shop/member');
        $sql = "SELECT * FROM (SELECT COUNT(*) AS `count`,`mobile`,`member_id` FROM allwood_member GROUP BY `mobile`) AS a WHERE a.`count` > 1;";
        $res = $member_model ->query($sql);   
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * member 表唯一性检查
     */
    public function memberUniqueMid(){

        $member_model = Model('shop/member');

        $sql = "SELECT * FROM (SELECT COUNT(*) AS `count`,`mid`,`member_id` FROM allwood_member GROUP BY `mid`) AS a WHERE a.`count` > 1;";
        $res = $member_model ->query($sql);
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * member 表唯一性检查
     */
    public function memberUniqueName(){
        $member_model = Model('shop/member');
        $sql = "SELECT * FROM (SELECT COUNT(*) AS `count`,`member_name`,`member_id` FROM allwood_member GROUP BY `member_name`) AS a WHERE a.`count` > 1;";
        $res = $member_model ->query($sql);   
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * seller表和store表绑定关系  store表中没记录
     */
    public function sellerStore(){
        $this->lastUpdate(SELF_SELLER_NO_STORE);
        $member_model = Model('shop/member');
        $sql = "SELECT * FROM  (SELECT s.`member_id` AS member_id_s,t.`member_id` AS member_id_t,s.`tesu_deleted`,s.`seller_name`,t.`store_name` FROM allwood_seller AS s LEFT JOIN allwood_store AS t ON s.`member_id`=t.`member_id` )  AS a WHERE  (a.`member_id_t` IS NULL ) and (a.`tesu_deleted`<>1)";
        $res = $member_model ->query($sql);  
        if(IS_POST){
            foreach ($res as $key => $value) {
                $update['tesu_deleted'] = 1;
                $condition['member_id'] = $value['member_id_s'];
                //delStore
                Model('shop/seller')->editSeller($update, $condition);
            }
            //$this->log2(SELF_SELLER_NO_STORE, $res);
            showMessage('success');
        } 
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * store 表和seller 表的绑定管理  seller 表中没记录
     */
    public function storeSeller(){
        $this->lastUpdate(SELF_STORE_NO_SELLER);
        $member_model = Model('shop/member');
        $sql = "SELECT * FROM  (SELECT s.`member_id` AS member_id_s,t.`member_id` AS member_id_t,s.`seller_name`,s.`tesu_deleted`,t.`store_name` FROM  allwood_store AS t LEFT JOIN allwood_seller AS s  ON s.`member_id`=t.`member_id` )  AS a WHERE  (a.`member_id_s` IS NULL) and (a.`tesu_deleted`<>1) ";
        $res = $member_model ->query($sql);  
        if(IS_POST){
            foreach ($res as $key => $value) {
                $update['tesu_deleted'] = 1;
                $condition['member_id'] = $value['member_id'];
                //delStore
                Model('shop/store')->editStore($update, $condition);
            }
            //$this->log2(SELF_STORE_NO_SELLER, $res);
            showMessage('success');
        } 
        $this->assign('list', $res);
        $this->display();
    }

    /*
     *开店成功但是没有seller
     */
    public function openStoreSucc(){
        $this->lastUpdate(SELF_STOREOPEN_NO_SELLER);
        $member_model = Model('shop/member');
        $sql = "SELECT * FROM (SELECT j.`company_name`,j.`member_id`,s.`seller_name`,j.`joinin_state`,s.`tesu_deleted` FROM allwood_store_joinin AS j LEFT JOIN allwood_seller AS s ON j.`member_id` = s.`member_id` ) AS a  WHERE (a.`seller_name` IS NULL) AND (a.`joinin_state`<>40 ) AND (a.`joinin_state`<>12 ) AND (a.`joinin_state`<>30 ) and (a.`tesu_deleted`<>1)";
        $res = $member_model ->query($sql); 
        if(IS_POST){
            foreach ($res as $key => $value) {
                $update['joinin_state'] = 12;
                $condition['member_id'] = $value['member_id'];
                Model('shop/store_joinin')->modify($update, $condition);
            }
            //$this->log2(SELF_STOREOPEN_NO_SELLER, $res);
            showMessage('success');
        }     
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * 正常或者下架的商品挂靠在一个不存在或者已经被删除的类目下
     */
    public function goodsGlassNotExist(){
        $this->lastUpdate(SELF_GC_NOT_EXIST);
        $member_model = Model('shop/member');
        $goods_model = Model('shop/goods');
        $sql = "SELECT * FROM (SELECT j.`goods_id`,j.`goods_commonid`,j.`goods_name`,j.`goods_state`,j.`goods_verify`,j.`goods_storage`,s.`gc_name`,s.`tesu_deleted`,j.`tesu_deleted` as `tesu_deleted2` FROM allwood_goods AS j LEFT JOIN allwood_goods_class AS s ON j.`gc_id` = s.`gc_id` WHERE j.`goods_state` <> 10 ) AS a  WHERE (a.`gc_name` IS NULL AND a.`tesu_deleted2`<>1 ) OR ( a.`tesu_deleted` = 1  AND a.`tesu_deleted2`<>1 )";
        $res = $member_model ->query($sql);  
        if(IS_POST){
            foreach ($res as $key => $value) {
                $update['goods_state'] = 10;
                $condition['goods_id'] = $value['goods_id'];
                $goods_model->editGoods($update, $condition);

                $condition2['goods_commonid'] = $value['goods_commonid'];
                $goods_model->editGoodsCommon($update, $condition2);
            }
            //$this->log2(SELF_GC_NOT_EXIST, $res);
            showMessage('success');
        }  
        $this->assign('list', $res);
        $this->display();

    }

    /*
     * 账户明细
     */
    public function accountCorr(){
        $this->display();
    }


    /*
     *  goods 表 和goods_common 表中的 is_offline 不一致
     */
    public function isOffline(){
        //SELECT c.goods_commonid,c.goods_name,g.is_offline,c.is_offline FROM allwood_goods_common AS c LEFT JOIN (SELECT * FROM allwood_goods WHERE 1 GROUP BY goods_commonid ) AS g ON c.goods_commonid = g.goods_commonid WHERE c.is_offline<>g.is_offline
        $this->lastUpdate(SELF_IF_OFFLINE_NOTMATCH);
        $member_model = Model('shop/member');
        $sql = "SELECT c.`goods_commonid`,c.`goods_name`,g.`is_offline` as `is_offline_goods`,c.`is_offline` as `is_offline_goods_common` FROM allwood_goods_common AS c LEFT JOIN (SELECT * FROM allwood_goods WHERE 1 GROUP BY goods_commonid ) AS g ON c.`goods_commonid` = g.`goods_commonid` WHERE c.`is_offline`<>g.`is_offline`; ";
        $res = $member_model ->query($sql);
        if(IS_POST){
            foreach ($res as $key => $value) {
                $update['is_offline'] = $value['is_offline_goods_common'];
                $condition['goods_commonid'] = $value['goods_commonid'];
                Model('shop/goods')->editGoods($update, $condition);
            }
            //$this->log2(SELF_IF_OFFLINE_NOTMATCH, $res);
            showMessage('success');
        }
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * goods 表 和 goods_common 表中 goods_state 不一致
     */
    public function goodsState(){
        $this->lastUpdate(SELF_GOODS_STATE_NOTMATCH);
        $member_model = Model('shop/member');
        $sql = "SELECT c.`goods_commonid`,c.`goods_name`,g.`goods_state` as `goods_state_goods`,c.`goods_state` as `goods_state_goods_common` FROM allwood_goods_common AS c LEFT JOIN (SELECT * FROM allwood_goods WHERE 1 GROUP BY goods_commonid ) AS g ON c.`goods_commonid` = g.`goods_commonid` WHERE c.`goods_state`<>g.`goods_state`; ";
        $res = $member_model ->query($sql);
        if(IS_POST){
            foreach ($res as $key => $value) {
                $update['goods_state'] = $value['goods_state_goods_common'];
                $condition['goods_commonid'] = $value['goods_commonid'];
                Model('shop/goods')->editGoods($update, $condition);
            }
            //$this->log2(SELF_GOODS_STATE_NOTMATCH, $res);
            showMessage('success');
        } 
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * goods表 和 goods_common 表中 goods_verify 不一致
     */
    public function goodsVerify(){
        $this->lastUpdate(SELF_GOODS_VERIFY_NOTMATCH);
        $member_model = Model('shop/member');
        $sql = "SELECT c.`goods_commonid`,c.`goods_name`,g.`goods_verify` as `goods_verify_goods`,c.`goods_verify` as `goods_verify_goods_common` FROM allwood_goods_common AS c LEFT JOIN (SELECT * FROM allwood_goods WHERE 1 GROUP BY goods_commonid ) AS g ON c.`goods_commonid` = g.`goods_commonid` WHERE c.`goods_verify`<>g.`goods_verify`; ";
        $res = $member_model ->query($sql); 
        if(IS_POST){
            foreach ($res as $key => $value) {
                $update['goods_verify'] = $value['goods_verify_goods_common'];
                $condition['goods_commonid'] = $value['goods_commonid'];
                Model('shop/goods')->editGoods($update, $condition);
            }
            //$this->log2(SELF_GOODS_VERIFY_NOTMATCH, $res);
            showMessage('success');
        }  
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * 上传商品的类别和店铺类别不匹配
     * store.com_type = 1  经销商  is_offline = 0 1
     *   store.com_type = 3 工厂  is_offline = 1 2
     */
    public function goodsStoreNotMatch(){
        $this->lastUpdate(SELF_GOODS_STORE_NOTMATCH);
        $member_model = Model('shop/member');
        $sql = "SELECT g.`goods_id`,g.`goods_name`,g.`is_offline`,s.`store_name`,s.`com_type`,g.`tesu_deleted`,g.`goods_state` FROM allwood_goods AS g LEFT JOIN  allwood_store AS s ON g.`store_id` = s.`store_id` WHERE (s.`com_type`=1 AND g.`is_offline` = 2 and g.`tesu_deleted`<>1 AND g.`goods_state`<>10 ) OR (s.`com_type` = 3 AND g.`is_offline` = 0 and g.`tesu_deleted`<>1 AND g.`goods_state`<>10 );";
        $res = $member_model ->query($sql);   
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * 上传商品goods_common 的类别和 店铺类别不一致
     */
    public function goodsCommonStoreNotMatch(){
        $this->lastUpdate(SELF_GOODS_COMMON_STORE_NOTMATCH);
        $member_model = Model('shop/member');
        $sql = "SELECT c.`goods_commonid`,c.`goods_name`,c.`is_offline`,s.`store_name`,s.`com_type`,c.`tesu_deleted`,c.`goods_state` FROM allwood_goods_common AS c LEFT JOIN  allwood_store AS s ON c.`store_id` = s.`store_id` WHERE (s.`com_type`=1 AND c.`is_offline` = 2 AND c.`tesu_deleted`<>1  AND c.`goods_state`<>10) OR (s.`com_type` = 3 AND c.`is_offline` = 0 AND c.`tesu_deleted`<>1 AND c.`goods_state`<>10);";
        $res = $member_model ->query($sql); 
        if(IS_POST){
            //如果com_type = 2
            
        }   
        $this->assign('list', $res);
        $this->display();
    }

    /*
     * 类型合并
     */
    public function typeMerge(){
        if(IS_POST){
            //判断属性的有效性
            $from_attr_id = $_POST['fromid']            ;
            $to_attr_id = $_POST['toid'];
            if(!intval($from_attr_id)){
                showMessage('from id must be digit!');
            }
            if(!intval($to_attr_id)){
                showMessage('to id must be digit!');
            }

            $from_attr = Model('shop/attribute')->where(array('attr_id'=>$from_attr_id))->select();
            $to_attr = Model('shop/attribute')->where(array('attr_id'=>$to_attr_id))->select();
            $from_attr = $from_attr[0];
            $to_attr = $to_attr[0];
            if(is_null($from_attr)){
                showMessage('from id can not be empty!');
            }
            if(is_null($to_attr)){
                showMessage('to id can not be empty!');
            }
        
            $attr_id_delta = $to_attr['attr_id'] - $from_attr['attr_id'];

            $condition['attr_id'] = $from_attr_id;
            $condition2['attr_id'] = $to_attr_id;
            $first_from_attr_value = Model('shop/attribute_value')->where($condition)->order('attr_value_id asc')->select();
            $first_to_attr_value = Model('shop/attribute_value')->where($condition2)->order('attr_value_id asc')->select();

            if(empty($first_from_attr_value)){
                showMessage(' from attr value is empty !');
            }

            if(empty($first_to_attr_value)){
                showMessage(' to attr value is empty !');
            }

            $first_from_attr_value = $first_from_attr_value[0];
            $first_to_attr_value = $first_to_attr_value[0];

            $attr_value_id_delta = $first_to_attr_value['attr_value_id'] - $first_from_attr_value['attr_value_id'];

            try{
                Model('shop/attribute')->beginTransaction();
                //修改
                //修改属性 表 和类型的关联
                $sql = 'update allwood_attribute set `attr_id` = '.$to_attr['attr_id'].'  where `attr_id` = '.$from_attr['attr_id'].';';
                if(!(Model('shop/goods')->execute($sql))){
                    Model('shop/attribute')->rollback();
                    echo 'first faile';
                    die;
                }

                //修改商品的属性类型
                $sql2 = 'update allwood_goods_attr_index  set `attr_value_id` = `attr_value_id` + '.$attr_value_id_delta.', `attr_id` = '.$to_attr['attr_id'].'  where `attr_id` = '.$from_attr['attr_id'];
                if(!(Model('shop/goods')->execute($sql2))){
                    Model('shop/attribute')->rollback();
                    echo 'second faile';
                    die;
                }

                Model('shop/attribute')->commit();
                echo 'success';
                die;
            }catch(Exception $e){
                Model('shop/attribute')->rollback();
                echo 'fail';
                die;
                showMessage('error, roll back');
            }
            
            showMessage('success');
            
        } 
        $this->display();
    }

    public function mergeInBatch(){
        if(IS_POST){
            $from_attr_id = $_POST['fromid'];
            $to_attr_id = $_POST['toid'];
            $from_attr_id = str_replace(" ", "", $from_attr_id);
            $from_attr = explode(',', $from_attr_id);
            //echo $to_attr_id.'  ##  '.json_encode($from_attr);
            //die;
            if(intval($to_attr_id) === false){
                showMessage('to attribute id must be digit !');
            }

            foreach ($from_attr as $key => $value) {
                if(intval($value) === false)
                    continue;

                $this->merge1($value, intval($to_attr_id));
            }

            
        } 
        $this->display();
    }

    /*
     * 检查 store_joinin store 的 province_id,city_id,area_id 是否一致
     */
    public function provinceCityArea(){
        $this->lastUpdate(SELF_PROVINCE_CITY_AREA_NOTMATCH);
        $member_model = Model('shop/store');
        $sql = "SELECT j.`province_id` as `j_province_id`,j.`city_id` as `j_city_id`,j.`area_id` as `j_area_id`,s.`store_name`,s.`member_id`,s.`com_type`,s.`tesu_deleted`,s.`province_id` as `s_province_id`,s.`city_id` as `s_city_id`,s.`area_id` as `s_area_id` FROM allwood_store_joinin AS j LEFT JOIN  allwood_store AS s ON j.`member_id` = s.`member_id` WHERE j.`province_id`<>s.`province_id`  OR j.`city_id`<>s.`city_id` OR j.`area_id`<>s.`area_id`;";
        $res = $member_model ->query($sql);
        if(IS_POST){
            $store_model = Model('shop/store');
            foreach ($res as $key => $value) {
                $condition['member_id'] = $value['member_id'];
                $update['province_id'] = $value['j_province_id'];
                $update['city_id']     = $value['j_city_id'];
                $update['area_id']     = $value['j_area_id'];
                echo json_encode($update).' ##'.json_encode($condition);
                $store_model->editStore($update, $condition);
            }
            showMessage('success');

        }   
        $this->assign('list', $res);
        $this->display();
    }

    private function merge1($from_attr, $to_attr){
        //判断属性的有效性
        $from_attr_id = $from_attr;
        $to_attr_id = $to_attr;
        if(!intval($from_attr_id)){
            return false;
        }
        if(!intval($to_attr_id)){
            return false;
        }

        $from_attr = Model('shop/attribute')->where(array('attr_id'=>$from_attr_id))->select();
        $to_attr = Model('shop/attribute')->where(array('attr_id'=>$to_attr_id))->select();
        $from_attr = $from_attr[0];
        $to_attr = $to_attr[0];
        if(is_null($from_attr)){
            return false;
        }
        if(is_null($to_attr)){
            return false;
        }
    
        $attr_id_delta = $to_attr['attr_id'] - $from_attr['attr_id'];

        $condition['attr_id'] = $from_attr_id;
        $condition2['attr_id'] = $to_attr_id;
        $first_from_attr_value = Model('shop/attribute_value')->where($condition)->order('attr_value_id asc')->select();
        $first_to_attr_value = Model('shop/attribute_value')->where($condition2)->order('attr_value_id asc')->select();

        if(empty($first_from_attr_value)){
            return false;
            showMessage(' from attr value is empty !');
        }

        if(empty($first_to_attr_value)){
            return false;
            showMessage(' to attr value is empty !');
        }

        $first_from_attr_value = $first_from_attr_value[0];
        $first_to_attr_value = $first_to_attr_value[0];

        $attr_value_id_delta = $first_to_attr_value['attr_value_id'] - $first_from_attr_value['attr_value_id'];

        try{
            Model('shop/attribute')->beginTransaction();
            //修改
            //修改属性 表 和类型的关联
            $sql = 'update allwood_attribute set `attr_id` = '.$to_attr['attr_id'].', `attr_name` = "'.$to_attr['attr_name'].'"  where `attr_id` = '.$from_attr['attr_id'].';';
            if(!(Model('shop/goods')->execute($sql))){
                Model('shop/attribute')->rollback();
                return false;
                echo 'first faile';
                die;
            }

            //修改商品的属性类型
            $sql2 = 'update allwood_goods_attr_index  set `attr_value_id` = `attr_value_id` + '.$attr_value_id_delta.', `attr_id` = '.$to_attr['attr_id'].'  where `attr_id` = '.$from_attr['attr_id'];
            if(!(Model('shop/goods')->execute($sql2))){
                Model('shop/attribute')->rollback();
                return false;
                echo 'second faile';
                die;
            }

            Model('shop/attribute')->commit();
            return true;
            echo 'success';
            die;
        }catch(Exception $e){
            Model('shop/attribute')->rollback();
            return false;
            echo 'fail';
            die;
            showMessage('error, roll back');
        }

        return true;
        
        showMessage('success');
    }

    private function log2($type, $arr){
        if(empty($arr))
           return;
        Model('shop/self_correction_log')->wirteSelfLog($type); 
    }

    private function lastUpdate($type){
        $arr = Model('shop/self_correction_log')->getSelfLog($type);    
        $this->assign('last_update', $arr[0]) ;
    }




}