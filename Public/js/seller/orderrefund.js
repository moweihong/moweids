//订单退款
//date: 2016.06.12
//author zhouquan
(function($){
	define("s/orderrefund",function(require, exports, module){
		require("jquery/layer");
		var manage = require("com/manage");
		var param = manage.getParam();
		var orderID = param.order_id || $("#orderID").val() || "",
			refundID = param.refund_id || $("#refundID").val() || "",
			goodsID = param.goods_id || $("#goodsID").val() || "";
		var Module = {
			init: function(){
				/*
				 *卖家同意退款
				 */
				$(".js-orderRefund").on("click", ".js-agreeRefund", function(){
					layer.open({
						type: 2,
						title:"同意退款",
						area: ['630px', '290px'],
						content: gConfig.apiurl + "/index.php?act=seller_center&op=tpl_refund_agree&refund_id=" + refundID
					});
				});
				/*
				 *卖家拒绝退款
				 */
				$(".js-orderRefund").on("click", ".js-refuseRefund", function(){
					layer.open({
						type: 2,
						title:"拒绝退款",
						area: ['450px', '320px'],
						content: gConfig.apiurl + "/index.php?act=seller_center&op=tpl_goodsmoneyback_refuserefund&refund_id=" + refundID
					});
				});
				/*
				 *卖家同意退货退款并发送退货地址
				 */
				$(".js-goodsmoneyback").on("click", ".js-agreeBtn", function(){
					layer.open({
						type: 2,
						title:"同意退货退款",
						area: ['630px', '380px'],
						content: gConfig.apiurl + "/index.php?act=seller_center&op=tpl_goodsmoneyback_agree&refund_id=" + refundID
					});
				});
				/*
				 *卖家拒绝退货退款
				 */
				$(".js-goodsmoneyback").on("click", ".js-rejectBtn", function(){
					layer.open({
						type: 2,
						title:"拒绝退货",
						area: ['630px', '410px'],
						content: gConfig.apiurl + "/index.php?act=seller_center&op=tpl_goodsmoneyback_deny&refund_id=" + refundID
					});
				});
				/*
				 *卖家已收到退货，同意退款
				 */
				$(".js-goodsmoneyback").on("click", ".js-agreeReturns", function(){
					layer.open({
						type: 2,
						title:"同意退款",
						area: ['630px', '290px'],
						content: gConfig.apiurl + "/index.php?act=seller_center&op=tpl_goodsmoneyback_goodsreceived&refund_id=" + refundID
					});
				});
				/*
				 *卖家拒绝确认收货
				 */
				$(".js-goodsmoneyback").on("click", ".js-rejectReturns", function(){
					layer.open({
						type: 2,
						title:"拒绝收货",
						area: ['630px', '460px'],
						content: gConfig.apiurl + "/index.php?act=seller_center&op=tpl_goodsmoneyback_refusereceive&refund_id=" + refundID
					});
				});
				/*
				 *发表留言
				 */
				$("#container").on("click", ".js-comments", function(){
					var linking = {
						act			: "seller_center",
						op			: "tpl_leave_message",
						order_id	: orderID,
						refund_id	: refundID,
						goods_id	: goodsID
					}
					layer.open({
						type: 2,
						title:"发表留言",
						area: ['600px', '400px'],
						content: gConfig.apiurl + "/index.php?" + $.param(linking)
					});
				});
				/*
				 *协商记录-图片放大
				 */
				$("#container").on("click", ".js-magnify", function(){
					var me = $(this),
						src = me.data("rel"),
						bImg = '<img src="' + src + '" style="max-width:800px;max-height:600px;vertical-align:middle;" />';
					$(bImg).load(function(){
						parent.layer.open({
							type: 1,
							title: false,
							area: ['auto', 'auto'],
							shadeClose: true,
							content: '<p style="padding:20px;text-align:center;">' + bImg + '</p>'
						});
					})
				});
			},
			/*
			 * 发送数据-
			 * */
			sendApply: function(api, herf, info){
				var info = info || {},
					data = $("#form").serialize();
				data = manage.serializeJson(data);
				var newdata =  $.extend(data, info);
				manage.getApi(api, newdata, function(){
					setTimeout(function(){
						window.location = gConfig.apiurl + herf;
					},2000);
				});
			}
		}
		module.exports = Module;
	});
	seajs.use("s/orderrefund", function(module){
		module.init();
	});
})($);