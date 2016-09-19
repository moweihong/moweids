<?php
/**
 * Created by PhpStorm.
 * User: martymei
 * Date: 2016/6/13
 * Time: 16:31
 */
namespace Shop\Model;
use Think\Model;
class GoodsShareModel extends Model
{
    public function __construct()
    {
        parent::__construct('goods_share');
    }

    /********************************
     * @param $shop_id
     * @param array $wh
     * @return mixed
     *显示店铺展示的商品
     */
    public function show_goods($shop_id,$wh=array()){
        if(count($wh)==0){
            $wh["shop_id"]=$shop_id;
            $wh["goods_status"]=1;
            $model = Model();
            $count=$this->where($wh)->count();
        }else{
            $wh["goods_share.shop_id"]=$shop_id;
            $wh["goods_share.goods_status"]=1;
            $model = Model();
            $count=$model->table("goods_share,goods,goods_share_seller")->on("goods_share.goods_id=goods.goods_id,goods_share_seller.goods_share_id=goods_share.id")->group("goods_share.goods_id")
                ->where($wh)->select();
            $count=count($count);
        }

        $field="goods_share.*,goods.goods_image,goods.goods_name,sum(goods_share_seller.goods_pay_price*goods_share_seller.percent) as r_total_money";
        $result=$model->table("goods_share,goods,goods_share_seller")->on("goods_share.goods_id=goods.goods_id,goods_share_seller.goods_share_id=goods_share.id")->group("goods_share.goods_id")
        ->field($field)->where($wh)->page(5,$count)->select();

        foreach($result as $key=>$val){
            $result[$key]["r_total_money"]=number_format($val["r_total_money"]/100,2);
        }

        return $result;
    }
    //获取没有展示的商品
    public function get_no_show_goods($shop_id,$map=array()){
        $wh["shop_id"]=$shop_id;
        $result=$this->where($wh)->field("goods_id")->select();
        $in=array();
        foreach($result as $key=>$val){
            $in[]=$val["goods_id"];
        }
        $map["goods_id"]=array("not in",$in);
        $map["store_id"]=$shop_id;
        $map["goods_state"]=1;//正常商品
        $model=new Model();
        $goods=$model->table("goods")->field("goods_image,goods_name,goods_price,goods_id")->where($map)->page(5)->select();
        return $goods;
    }

    /***************************
     * @param $shop_id
     * @return mixed
     * 获取没有展示的商品的种类
     */
    public function  get_no_show_goods_class($shop_id){
        $wh["shop_id"]=$shop_id;
        $result=$this->where($wh)->field("goods_id")->select();
        $in=array();
        foreach($result as $key=>$val){
            $in[]=$val["goods_id"];
        }
        $map["goods_id"]=array("not in",$in);
        $map["store_id"]=$shop_id;
        $model = Model();
        $goods_class=$model->table("goods,goods_class")->on("goods.gc_id=goods_class.gc_id")->field("goods_class.gc_id,goods_class.gc_name")->group("goods_class.gc_id")->where($map)->select();
        return $goods_class;
    }

    /***************************
     * @param $where
     * @param array $data
     * @return int|mixed
     * 产品上架
     */
    public function add_goods($where,$data=array()){
        $wh["goods_id"]=$where["goods_id"];
        $wh["store_id"]=$where["shop_id"];
        $model=new Model();
        $goods_info=$model->table("goods")->where($wh)->field("goods_price")->find();
        if(!empty($goods_info)){
            $goods_item=$model->table("goods_share")->where($where)->find();
            if(empty($goods_item)){
                $data["money"]=$goods_info["goods_price"];
                $data["add_time"]=time();
                $data["goods_status"]=1;
                $id=$this->insert($data);
            }else{
                $data["goods_status"]=1;
                $data["add_time"]=time();
                $id=$model->table("goods_share")->where($where)->update($data);
            }
        }else{
            $id=0;
        }

        return $id;
    }

    /*************************
     * @param $condition
     * @return mixed
     * 产品下架
     */
    public  function  down_goods($condition){
        $data["goods_status"]=0;
        $result=$this->where($condition)->update($data);
        return  $result;
    }

    /****************************
     * @param $where
     * @param $data
     * @return mixed
     * 更新数据
     */
    public function updateInfo($where,$data){
        $result=$this->where($where)->update($data);
        return $result;
    }

    /***********************
     * @param array $goods
     * @param string $type
     * @return string
     * 获取图片
     */
    private  function thumb($goods = array(), $type = ''){
    $type_array = explode(',_', ltrim(GOODS_IMAGES_EXT, '_'));
        if (!in_array($type, $type_array)) {
            $type = '240';
        }
        if (empty($goods)){
            return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
        }
        if (array_key_exists('apic_cover', $goods)) {
            $goods['goods_image'] = $goods['apic_cover'];
        }
        if (empty($goods['goods_image'])) {
            return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
        }
        $search_array = explode(',', GOODS_IMAGES_EXT);
        $file = str_ireplace($search_array,'',$goods['goods_image']);
        $fname = basename($file);
        //取店铺ID
        if (preg_match('/^(\d+_)/',$fname)){
            $store_id = substr($fname,0,strpos($fname,'_'));
        }else{
            $store_id = $goods['store_id'];
        }
        $file = $type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file);
        if (!file_exists(BASE_UPLOAD_PATH.'/'.ATTACH_GOODS.'/'.$store_id.'/'.$file)){
            return UPLOAD_SITE_URL.'/'.defaultGoodsImage($type);
        }
        $thumb_host = UPLOAD_SITE_URL.'/'.ATTACH_GOODS;
        return "http://".$_SERVER["SERVER_NAME"].'/data/upload/shop/store/goods/'.$store_id.'/'.$file;
    }

    /*******************************
     * @param $input
     * @return mixed
     * 获取店铺分享商品
     */
    public function getShowGoods($input){
        $search=array();
        if(isset($input["goods_name"])){
            if(is_numeric($input["goods_name"])){
                $search["goods_share.goods_id"]=$input["goods_name"];
            }else{
                $search["goods.goods_name"]=array('like',"%".$input["goods_name"]."%");
            }
        }
        if(isset($input["goods_type"])){
            $search["goods.gc_id"]=$input["goods_type"];
        }
     //   $search["goods_share_seller.status"]=array("neq",2);
        $filed="goods_share.id,goods.goods_id as goods_id ,goods.goods_name as goods_des ,goods.store_name,store.area_info as store_province, store.store_address as store_city,
        goods.goods_price,goods_share.share_money as commission_rate,sum(goods_share_seller.num) as orders_cnt,sum(goods_share_seller.r_money) as commission_total,
        goods_share.start_time,goods_share.end_time,goods_share.goods_status as putaway_status,store.store_id,goods.goods_image";
        $model=new Model();
        $where["start_time"]=array("elt",time());
        $where["end_time"]=array("egt",time());
        $where["goods_status"]=1;
        $result=$model->table("goods_share")->group("goods_id")->where($where)->select();
        $count=count($result);
        //计算limit
        $page=$input["page_no"];
        $per_page=$input["page_number"];
        $start=($page-1)*$per_page;
        $limit="{$start},{$per_page}";
        $start_time=strtotime("-1 month");
        $end_time=time();
        /*$info=$model->table("goods_share,goods,store,goods_share_seller")->on("goods_share.goods_id=goods.goods_id,goods.store_id=store.store_id,goods_share_seller.goods_share_id=goods_share.id")->
            group("goods_share_seller.goods_share_id")->where($search)->field($filed)->group("goods_id")->order("goods_share.id desc")->limit($limit)->select(); */

        $sql="select $filed from allwood_goods_share as goods_share join allwood_goods as goods on goods_share.goods_id=goods.goods_id
            join  allwood_store as store on goods.store_id=store.store_id
            left join (select * from allwood_goods_share_seller where status<>2 and addtime BETWEEN $start_time and $end_time) as goods_share_seller on goods_share_seller.goods_share_id=goods_share.id
           where goods_status=1 and start_time<=$end_time and end_time >=$end_time
           group by goods_share.id order by goods_share.id desc limit $limit ";
        $info=$model->query($sql);

        $now=time();
        foreach($info as $key=>$val){
            $info[$key]["goods_pic"]=$this->thumb(array("goods_image"=>$val["goods_image"],"store_id"=>$val["store_id"]));
            $info[$key]["store_id"]=$val["store_id"];
            $info[$key]["goods_link"]="http://".$_SERVER['SERVER_NAME']."/shop/index.php?act=goods&op=index&goods_id=".$val["goods_id"];
            $info[$key]["commission"]=number_format($val["goods_price"]*$val["commission_rate"]/100,2);
            $info[$key]["commission_total"]=number_format($val["commission_total"]*$val["goods_price"]*$val["commission_rate"]/100,2);
            $info[$key]["promotion_period"]=date("Y-m-d",$val["start_time"])."至".date("Y-m-d",$val["end_time"]);
            if(($now<$val["start_time"])||($now>$val["end_time"])){
                $info[$key]["putaway_status"]=1;
            }
            else if($val["putaway_status"]==0){
                $info[$key]["putaway_status"]=1;
            }else{
                $info[$key]["putaway_status"]=0;
            }
        }
        $list["code"]=0;
        $list["msg"]="OK";
        $list["page_total"]=ceil($count/intval($input["page_number"]));
        $list["record_total"]=$count;
        $list["goods_list"]=$info;
        return $list;
    }

    /********************************
     * @param $id
     * @return int
     * 判断商品是否在分销时候
     */
    public function isVaild($id){
        $where["id"]=$id;
        $info=$this->where($where)->field("start_time,end_time,goods_status")->find();
        $now=time();
        if(!$info["goods_status"]) return 0; //下架
        if(($now>$info["start_time"])&&($now<$info["end_time"])){
            return 1;
        }else{
            return 0;
        }
    }

    /**************************************
     * @param int $page_no
     * @param int $page_number
     * @param int $store_id
     * @param null $store_name
     * @return mixed
     * 获取店铺信息
     */
    public function getShareShop($page_no=1,$page_number=10,$store_id=0,$store_name=null){
        if($store_id>0){
            $where["member.mid"]=$store_id;
        }
        if(!empty($store_name)){
            $where["store.store_name"]=array("like","%".$store_name."%");
        }
        $where["goods_share.goods_status"]=1;
        $model=new Model();

        $count=$model->table("store,goods_share")->on("store.store_id=goods_share.shop_id")->where($where)->count();

        $start=($page_no-1)*$page_number;
        $lenth=$page_number;
        $limit="$start,$lenth";
        $field="store.store_id,store.store_name,store.member_name as store_contacts,store.store_tel as tel,
        store.store_time as reg_time,store_joinin.company_address_detail as store_address,store_joinin.major_business as business,count(goods_share.shop_id) as cps_cnt";
        $info["store_list"]=$model->table("store,goods_share,store_joinin,member")->on("store.store_id=goods_share.shop_id,store_joinin.member_id=store.member_id,member.member_id=store.member_id")
            ->group("goods_share.shop_id")->field($field)->where($where)->limit($limit)->select();;
        $info["record_total"]=$count;
        $info["page_total"]=ceil($count/$page_number);
        $info["msg"]="成功";
        $info["code"]=0;

        $model=new Model();
        $field="sc_id,sc_name";
        $good_class=$model->table("store_class")->field($field)->select();
        $class_info=array();
        foreach($good_class as $key=>$val){
            $class_info[$val["sc_id"]]=$val["sc_name"] ;
        }

        foreach($info["store_list"] as $key=>$val){
            if(!empty($val["business"])){
                $tmp=unserialize($val["business"]);
                $tmp1="";
                foreach($tmp as $key1=>$val1){
                    $tmp1.=$class_info[$val1]."-";
                }

                $info["store_list"][$key]["business"]=trim($tmp1,"-");
            }
        }
        return $info;
    }


}