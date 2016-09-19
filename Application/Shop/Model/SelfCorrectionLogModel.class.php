<?php
/**
 * 地区模型
 *
 *
 *
 *

 */
namespace Shop\Model;
use Think\Model;

class SelfCorrectionLogModel extends Model{

    
    public function __construct() {
        parent::__construct('self_correction_log');
    }

    /*
     * 写入操作日志
     */
    public function wirteSelfLog($type){
        $arr['type'] = $type;
        switch ($type) {
            case SELF_GC_ID_NOTMATCH:
                $arr['operation_desc'] = "gc id 在goods 和 goods_common 中不一致";
                break;
            case SELF_GC_NAME_NOTMATCH:
                $arr['operation_desc'] = "gc_name 在 goods_common 表中和gc_id 不匹配";
                break;
            case SELF_LONEY_GOODS:
                $arr['operation_desc'] = "孤立goods表数据";
                break;
            case SELF_LONEY_GOODSCOMMON:
                $arr['operation_desc'] = "孤立的goods_common数据";
                break;
            case SELF_UNIQUE_MEMBER_MOBILE:
                $arr['operation_desc'] = "member 表 mobile 唯一性检查";
                break;
            case SELF_UNIQUE_MEMBER_MID:
                $arr['operation_desc'] = "member 表 mid 唯一";
                break;
            case SELF_UNIQUE_MEMBER_MEMBER_NAME:
                $arr['operation_desc'] = "member 表 member_name 唯一";
                break;
            case SELF_SELLER_NO_STORE:
                $arr['operation_desc'] = "有seller 没有store ";
                break;      

            case SELF_STORE_NO_SELLER:
                $arr['operation_desc'] = "有store没有seller ";
                break;               
            case SELF_SELLER_NO_STORE:
                $arr['operation_desc'] = "有seller 没有store ";
                break;               
            case SELF_STOREOPEN_NO_SELLER:
                $arr['operation_desc'] = "开店成功但是没有seller ";
                break;               
            case SELF_GC_NOT_EXIST:
                $arr['operation_desc'] = "商品挂载在一个不存在或者被删除的目录下 ";
                break;               
            case SELF_ACCOUNT_ERROR:
                $arr['operation_desc'] = "账户错误";
                break;   
            case SELF_IF_OFFLINE_NOTMATCH:
                $arr['operation_desc'] = "goods表和goods_common表的is_offline 不匹配";
                break;
            case SELF_GOODS_STATE_NOTMATCH:
                $arr['operation_desc'] = "商品状态不匹配(goods goods_common)";
                break;  
            case SELF_GOODS_VERIFY_NOTMATCH:
                $arr['operation_desc'] = "商品审核状态不匹配(goods goods_common)";
                break;              
            case SELF_GOODS_STORE_NOTMATCH:
                $arr['operation_desc'] = "商品和上传商品店铺类型不匹配";
                break;  
            case SELF_GOODS_COMMON_STORE_NOTMATCH:
                $arr['operation_desc'] = "goods_common和上传商品店铺类型不匹配";
                break;                  
            default:
                $arr['operation_desc'] = "其它";
                break;
        }
        $arr['time'] = time();
        // echo encode_json($arr);
        // die;
        return $this->insert($arr);
    }

    /*
     * 获取操作日志
     */
    public function getSelfLog($type){
        $condition['type'] = $type;
        return $this->where($condition)->order('time DESC')->limit(1)->select();
    }
}