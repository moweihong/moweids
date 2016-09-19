//订单详情
//date: 2016.06.07
//author zhouquan
(function($){
	define("m/orderdetail",function(require, exports, module){
		require("jquery/layer");
		var manage = require("com/manage");
		var param = manage.getParam();
		var order_id = param.order_id || "";
		var Module = {
			init: function(){
				/*
				* 取消订单
				* */
				$(".js-orderDetail").on("click", ".js-cancel", function(){
					var msg = '<div style="height:50px;"><select class="js-cancelSle" style="width:200px;"><option value="0">请选择一个取消订单理由</option><option value="我不想买了">我不想买了</option><option value="信息填错，想重拍">信息填错，想重拍</option><option value="卖家缺货">卖家缺货</option><option value="拍错了">拍错了</option><option value="其他原因">其他原因</option></select></div>';
					layer.confirm(msg, {title:"取消订单"}, function(index){
						var state_info = $(".js-cancelSle").val();
						if(state_info == 0){
							$.wrongTip($(".layui-layer-btn0"), "请选择取消订单原因");
							return;
						}
						manage.getApi("/index.php?c=member&a=ajaxCancelOrder", {
							order_id	: order_id,
							state_info	: state_info
						}, function(){
							setTimeout(function(){
								location.reload();
							},2000);
						});
					})
				});
				/*
				 * 补充留言
				 * */
				$(".js-orderDetail").on("click", ".js-message", function(){
					var buyer_message = $.trim($(".js-buyerMessage").text()),
						msg = '<textarea id="buyerMessage" placeholder="最多可输入200字" maxlength="200">' + buyer_message + '</textarea>';
					layer.alert(msg, {title:"补充留言"}, function(index){
						var order_message = $.trim($("#buyerMessage").val());
						manage.getApi("/index.php?c=member&a=supply_message", {
							order_id		: order_id,
							order_message	:order_message
						}, function(){
							$(".js-buyerMessage").text(order_message);
						});
					})
				});
				/*
				 *买家确认收货
				 */
				$(".js-orderDetail").on("click", ".js-receiptBuyer", function(){
					var msg = '确认收货后，全木行将把货款结算给卖家<h4 class="fs16 fb cor-black mt10">确认您已收到货品？</h4>';
					Module.sendReceipt(msg, "/index.php?c=member&a=order_receive", {order_id: order_id});
				});
			},
			/*
			 * 发送数据--确认收货
			 * */
			sendReceipt: function(msg, api, data){
				layer.alert(msg, {title:"确认收货"}, function(index){
					manage.getApi(api, data, function(){
						setTimeout(function(){
							location.reload();
						},2000);
					});
				})
			}
		}
		module.exports = Module;
	});
	seajs.use("m/orderdetail", function(module){
		module.init();
	});
})($);