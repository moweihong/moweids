/**
* seajs配置
**/
(function() {
	var base = location.protocol + "//" + location.host;
	//全局配置参数
	window.gConfig = {
		environment	: "",//环境 local本地,调用测试数据
		timeout		: 60, //最大超时时间,单位秒,
		apiurl			: ALLWOOD_ROOT,
		sitehost		: ALLWOOD_HOST,
		jsurl			: ALLWOOD_JS
	};
	window.console = window.console || {
		log: function(msg){},
		error: function(msg){},
		warn: function(){}
	};// end
	seajs.config({
		base : base,
		alias : {
			/***********************插件模块*****************/
			"jquery/form"			: gConfig.jsurl + "/jquery/form.js?{version}",//表单验证插件
			"jquery/layer"			: gConfig.jsurl + "/jquery/layer/layer.js?{version}",//弹窗插件
			"jquery/laydate"		: gConfig.jsurl + "/jquery/laydate/laydate.js?{version}",//日期插件
			"jquery/paging"			: gConfig.jsurl + "/jquery/paging.js?{version}",//分页插件
			"jquery/cookie"			: gConfig.jsurl + "/jquery/cookie.js?{version}",//cookie插件
			"jquery/scrollbar"		: gConfig.jsurl + "/jquery/scrollbar.js?{version}",//滚动条美化
			"jquery/webuploader"	: gConfig.jsurl + "/jquery/webuploader/webuploader.js?{version}",//上传组件
			"jquery/upload"			: gConfig.jsurl + "/jquery/webuploader/upload.js?{version}",//封装上传插件
			"jquery/upload_cropper"	: gConfig.jsurl + "/jquery/webuploader/upload_cropper.js?{version}",//封装图片裁切
			"jquery/echarts"		: gConfig.jsurl + "/jquery/echarts.js?{version}",//图表库
			"jquery/flexslider"		: gConfig.jsurl + "/jquery/flexslider.js?{version}",//图片滚动
			"jquery/dialog"			: gConfig.jsurl + "/dialog/dialog.js?{version}",//提示框
			"jquery/masonry"		: gConfig.jsurl + "/jquery/masonry.js?{version}",//瀑布流
			"jquery/imageZoom"		: gConfig.jsurl + "/jquery/imageZoom.js?{version}",//图片放大
			/***********************网站基础共用的抽象组件*****************/
			"com/manage"			: gConfig.jsurl + "/com/manage.js?{version}",//基础共用模块
			"com/global"			: gConfig.jsurl + "/com/global.js?{version}",//全局共用,
			"com/adarray"			: gConfig.jsurl + "/com/adarray.js?{version}",//省市区数组
			"com/transport"			: gConfig.jsurl + "/com/transport.js?{version}",//物流模板
			"com/iplookup"			: "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js",//IP定位用户所在城市
			/***********************前端页面js*****************/
			"web/common"			: gConfig.jsurl + "/web/common.js?{version}",//前端公用
			"web/goods"				: gConfig.jsurl + "/web/goods.js?{version}",//商品详情页
			"web/cart_address"		: gConfig.jsurl + "/web/cart_address.js?{version}",//购物车收货地址
			/***********************买家中心页面js*****************/
			"m/orderlist"			: gConfig.jsurl + "/member/orderlist.js?{version}",//订单列表
			"m/orderdetail"			: gConfig.jsurl + "/member/orderdetail.js?{version}",//订单详情
			"m/orderrefund"			: gConfig.jsurl + "/member/orderrefund.js?{version}",//订单退款
			"m/profile"				: gConfig.jsurl + "/member/profile.js?{version}",//我的资料
			"m/address"				: gConfig.jsurl + "/member/address.js?{version}",//收货地址
			"m/favorites"			: gConfig.jsurl + "/member/favorites.js?{version}",//收藏商品&收藏店铺
			"m/pre_order_list"		: gConfig.jsurl + "/member/pre_order_list.js?{version}",//预订单列表
			"m/easypay_step2"		: gConfig.jsurl + "/member/easypay_step2.js?{version}",//授信资料-提交申请资料
			/***********************卖家中心页面js*****************/
			"s/storeset"			: gConfig.jsurl + "/seller/storeset.js?{version}",//店铺设置
			"s/goodsadd"			: gConfig.jsurl + "/seller/goodsadd.js?{version}",//发布商品
			"s/goodslist"			: gConfig.jsurl + "/seller/goodslist.js?{version}",//商品管理
			"s/goods_manage"		: gConfig.jsurl + "/seller/goods_manage.js?{version}",//线下商品管理
			"s/orderlist"			: gConfig.jsurl + "/seller/orderlist.js?{version}",//订单列表
			"s/orderdetail"			: gConfig.jsurl + "/seller/orderdetail.js?{version}",//订单详情
			"s/orderrefund"			: gConfig.jsurl + "/seller/orderrefund.js?{version}",//订单退款
			/***********************开店流程页面js****************/
			"s/startshop"			: gConfig.jsurl + "/seller/startshop.js?{version}"//提交资料
		},
		vars: {
			version:"20160828"
		}
	});
	seajs.use("com/manage");
})();