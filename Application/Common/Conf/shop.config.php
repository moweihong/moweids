<?php
/*
 * 商家入驻状态定义
 */
define('DS', '');
define('BASE_ROOT_PATH', "");
define('SHOP_SITE_URL', '');
define('RESOURCE_SITE_URL', "");
define('UPLOAD_SITE_URL', '/data/upload');
define('ATTACH_GOODS', 'shop/store/goods');
define('ATTACH_COMMON', 'shop/common');
define('ATTACH_STORE','shop/store');
define('TIMESTAMP',time());

define('COMMON_LAYOUT', 'Application/Common/Layout/');

//新申请
define('STORE_JOIN_STATE_NEW', 10);
//完成付款
define('STORE_JOIN_STATE_PAY', 11);
//开店申请审核中
define('STORE_JOIN_STATE_VERIFYING', 12);
//初审成功
define('STORE_JOIN_STATE_VERIFY_SUCCESS', 20);
//初审失败
define('STORE_JOIN_STATE_VERIFY_FAIL', 30);
//付款审核失败
define('STORE_JOIN_STATE_PAY_FAIL', 31);
//开店成功
define('STORE_JOIN_STATE_FINAL', 40);
//默认颜色规格id(前台显示图片的规格)
define('DEFAULT_SPEC_COLOR_ID', 1);
/**
 * 商品图片
 */
define('GOODS_IMAGES_WIDTH', '60,240,360,1280');
define('GOODS_IMAGES_HEIGHT', '60,240,360,12800');
define('GOODS_IMAGES_EXT', '_60,_240,_360,_1280');
//全木行默认图片
define('DEFAULT_IMG', '05164733457866082.jpg');
/**
 *  订单状态
 */
//已取消
define('ORDER_STATE_CANCEL', 0);
//定价中
define('ORDER_STATE_MAKEPRICE', 1);
//已产生但未支付
define('ORDER_STATE_NEW', 10);
//已支付
define('ORDER_STATE_PAY', 20);
//已发货
define('ORDER_STATE_SEND', 30);
//已收货，交易成功
define('ORDER_STATE_SUCCESS', 40);
//卖家处理中（分期购专用状态）
define('ORDER_STATE_HANDLING', 50);
//订单结束后可评论时间，15天，60*60*24*15
define('ORDER_EVALUATE_TIME', 1296000);

define("DEFAILT_EMAIL","tesu@tesu.com");

/**
 * 买家处理状态
 */
//待处理
define('SELLER_TODO', 1);

//待处理
define('SELLER_YES', 1);

//待处理
define('SELLER_NO', 1);

/**
 * 退款状态
 */
//处理中
define('REFUND_REFUNDING', 1);

//等待管理员处理
define('REFUND_TODO', 2);

//退款完成
define('REFUND_COMPLETE', 3);

/**
 * 退款退货类型
 */
//无需退货
define('RETURNTYPE_GOODS', 1);

//需要退货
define('RETURNTYPE_NOGOODS', 2);


/**
 * 订单锁定状态
 */
//锁定
define('ORDER_NOLOCK', 1);
define('ORDER_LOCK', 1);

/**
 * 商品状态
 */
//待发货
define('GOODS_TODELIVER', 1);

//待收货
define('GOODS_TORECEIVE', 2);

//未收到
define('GOODS_NOTRECEIVED', 3);

//已收到
//define('GOODS_NOTRECEIVED', 4);

/*
refund type 
 */
define('REFUNDTYPE_MONEY', 1);
define('REFUNDTYPE_MONEYGOODS', 2);

/*
SELLER STATE
 */
define('SELLER_STATE_PROCESSING', 1);
define('SELLER_STATE_AGREE', 2);
define('SELLER_STATE_DISAGREE', 3);

/*
REFUND STATE 
 */
define('REFUND_STATE_PROCESSING', 1);
define('REFUND_STATE_REFUNDING', 2);
define('REFUND_STATE_COMPLETE', 3);

/**
 * GOODS STATE
 */
define('GOODSSTATE_TODELIVER', 1);
define('GOODSSTATE_TORECEIVE', 2);
define('GOODSSTATE_NOTRECEIVE', 3);
define('GOODSSTATE_RECEIVED', 4);

//投诉状态
//投诉状态(10-新投诉/20-投诉通过转给被投诉人/30-被投诉人已申诉/40-提交仲裁/99-已关闭)
define('COMPLAIN_STATE_NONE', 0);
define('COMPLAIN_STATE_NEW', 10);
define('COMPLAIN_STATE_APPEAL', 20);
define('COMPLAIN_STATE_TALK', 30);
define('COMPLAIN_STATE_VERDICT', 40);
define('COMPLAIN_STATE_CLOSE', 99);

define('BUYER_FAULT', 1);
define('SELLER_FAULT', 2);


//申请分期购额度状态
//冻结状态
define('APPLYSTATUS_FREEZE', 6);
//未申请
define('APPLYSTATUS_NONE', 0);
//提交资料
define('APPLYSTATUS_NEW', 1);
//初审通过
define('APPLYSTATUS_PROCESSING_1', 3);
//初审失败
define('APPLYSTATUS_PROCESSING_1_FAIL', 2);
//复审通过
define('APPLYSTATUS_PROCESSING_2', 5);
//复审失败
define('APPLYSTATUS_PROCESSING_2_FAIL', 4);

//普通订单
define('ORDER_TYPE_ORDINARY', 1);
//分期购订单
define('ORDER_TYPE_EASYPAY', 2);
//乐装订单
define('ORDER_TYPE_DECORATE', 3);
//工厂订单
define('ORDER_TYPE_FACTORY', 4);

//商家类型
define('COM_TYPE_DSITRIBUTOR', 1);
define('COM_TYPE_DECORATOR', 2);
define('COM_TYPE_FACTORY', 3);

//流水
define('LIUSHUI_TYPE_ORDER', 8);
define('LIUSHUI_TYPE_TIHUO', 7);
define('LIUSHUI_TYPE_RECHARGE', 2);
define('LIUSHUI_TYPE_WITHDRAW', 3);
define('LIUSHUI_TYPE_TRANSFER', 6);
define('LIUSHUI_TYPE_COMMISSION', 4);
define('LIUSHUI_TYPE_EASYPAY', 10);
define('LIUSHUI_TYPE_EASYDECO', 9);

//企业类型
define('BUSINESS_TYPE_ENTER', 1);
define('BUSINESS_TYPE_INDIV', 2);

//gc id 在goods 和 goods_common 中不一致
define('SELF_GC_ID_NOTMATCH', 1);
//gc_name 在 goods_common 表中和gc_id 不匹配
define('SELF_GC_NAME_NOTMATCH', 2);
//孤立goods表数据
define('SELF_LONEY_GOODS', 3);
//孤立的goods_common
define('SELF_LONEY_GOODSCOMMON', 4);
//member 表 mobile 唯一
define('SELF_UNIQUE_MEMBER_MOBILE', 5);
//member 表 mobile 唯一
define('SELF_UNIQUE_MEMBER_MID', 6);
//member 表 member_name 唯一
define('SELF_UNIQUE_MEMBER_MEMBER_NAME', 7);
//有seller 没有store 
define('SELF_SELLER_NO_STORE', 8);
//有store没有seller
define('SELF_STORE_NO_SELLER', 9);
//开店成功但是没有seller
define('SELF_STOREOPEN_NO_SELLER', 10);
//商品挂载在一个不存在或者被删除的目录下
define('SELF_GC_NOT_EXIST', 11);
//账户错误
define('SELF_ACCOUNT_ERROR', 12);
//goods表和goods_common表的is_offline 不匹配
define('SELF_IF_OFFLINE_NOTMATCH', 13);
//商品状态不匹配(goods goods_common)
define('SELF_GOODS_STATE_NOTMATCH', 14);
//商品审核状态不匹配(goods goods_common)
define('SELF_GOODS_VERIFY_NOTMATCH', 15);
//商品和上传商品店铺类型不匹配
define('SELF_GOODS_STORE_NOTMATCH', 16);
//goods_common和上传商品店铺类型不匹配
define('SELF_GOODS_COMMON_STORE_NOTMATCH', 17);
//store_joinin 和 store 表中的 province_id city_id area_id 不匹配
define('SELF_PROVINCE_CITY_AREA_NOTMATCH', 8);
define('MD5_KEY',md5('7f245124c9c2efa3f90563696d338d1f'));
/**
 * 聚信立流程码
 */
//再次输入短信验证码
define('JXL_SMSCODE_AGAIN', 10001);
//输入短信验证码
define('JXL_SMSCODE_INPUT', 10002);
//密码错误
define('JXL_PWD_ERROR', 10003);
//短信验证码错误
define('JXL_SMSCODE_ERROR', 10004);
//短信验证码失效系统已自动重新下发
define('JXL_SMSCODE_FAIL', 10006);
//简单密码或初始密码无法登录
define('JXL_PWD_FAIL', 10007);
//开始采集行为数据
define('JXL_SUCCESS', 10008);
//请用本机发送CXXD至10001获取查询详单的验证码
define('JXL_SENDSMS', 10017);
//短信码失效请用本机发送CXXD至10001获取查询详单的验证码
define('JXL_SENDSMS_AGAIN', 10017);
//错误信息
define('JXL_ERROR', 30000);
//请求超时
define('JXL_TIME_OUT', 0);

//开发模式
define('DEBUG', 0);
define('TEST', 1);
define('PRODUCT', 2);

//JAVA 接口
define('BORROW_USE_DECORATE', 8);
define('BORROW_USE_EASYPAY', 4);

//全木行注册登录验证码
//短信验证码过期时间
define('SMS_EXPIRED_TIME', 300);
//短信验证码发送时间限制
define('SMS_SEND_TIME', 120);

return array(
	//商家入驻状态
	'STORE_JOIN_STATE' => array(
		STORE_JOIN_STATE_VERIFYING			=> '待审核',
		STORE_JOIN_STATE_VERIFY_FAIL		=> '审核失败',
		STORE_JOIN_STATE_FINAL				=> '开店成功'
	),

	//店铺状态
	'STORE_TYPE' => array(
		'open' => '开启',
        'close' => '关闭',
        'expire' => '即将到期',
        'expired' => '已到期'
	),
	
	//聚信立状态
	'JXL_STATE' => array(
		JXL_SMSCODE_AGAIN		=> '再次输入短信验证码',
		JXL_SMSCODE_INPUT		=> '输入短信验证码',
		JXL_PWD_ERROR			=> '服务密码错误',
		JXL_SMSCODE_ERROR		=> '短信验证码错误',
		JXL_SMSCODE_FAIL		=> '短信验证码失效',
		JXL_PWD_FAIL			=> '简单密码或初始密码无法登录',
		JXL_SUCCESS				=> '开始采集行为数据',
		JXL_SENDSMS 			=> '请用本机发送CXXD至10001获取查询详单的验证码',
		JXL_SENDSMS_AGAIN		=> '短信码失效请用本机发送CXXD至10001获取查询详单的验证码',
		JXL_ERROR 				=> '网络异常',
		JXL_TIME_OUT			=> '请求超时'
	),
);

