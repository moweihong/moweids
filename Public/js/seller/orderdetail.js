//订单详情
//date: 2016.06.12
//author zhouquan
(function($){
	define("s/orderdetail",function(require, exports, module){
		require("jquery/layer");
		var manage = require("com/manage");
		var param = manage.getParam();
		var order_id = param.order_id || $("#orderID").val() || "";
		var Module = {
			init: function(){
				/*
				 * 修改价格
				 * */
				$(".js-orderDetail").on("click", ".js-modifyPrice", function(){
					layer.open({
						type: 2,
						title:"修改价格",
						area: ['630px', '300px'],
						content: gConfig.apiurl + "/shop/storeCommon/tplmakeprice?order_id=" + order_id
					});
				});
				/*
				 * 修改收货地址
				 * */
				$(".js-orderDetail").on("click", ".js-modifAddr", function(){
					layer.open({
						type: 2,
						title:"修改收货地址",
						area: ['750px', '520px'],
						content: gConfig.apiurl + "/shop/Factory/modifyreciverinfo?order_id=" + order_id
					});
				});
				/*
				* 关闭订单
				* */
				$(".js-orderDetail").on("click", ".js-cancel", function(){
					var status = 0,
						is_supplier = $(this).hasClass('supplier'),
						is_factory = $(this).hasClass('factory'),
						is_member = $(this).hasClass('member'),
						msg = '<div style="height:50px;"><select class="js-cancelSle" style="width:200px;"><option value="0">请选择一个关闭订单理由</option>',
						factory_msg = '<option value="无法联系上买家">无法联系上买家</option><option value="买家误拍或重拍了">买家误拍或重拍了</option><option value="买家无诚意完成交易">买家无诚意完成交易</option><option value="已经缺货无法交易">已经缺货无法交易</option>',
						supplier_msg = '<option value="我不想买了">我不想买了</option><option value="信息填错，想重拍">信息填错，想重拍</option><option value="卖家缺货">卖家缺货</option><option value="拍错了">拍错了</option><option value="其他原因">其他原因</option>';
					//判断角色，挂载原因
					if(is_supplier){
						status = 1;
						msg += supplier_msg;
					}else if (is_factory || is_member){
						status = is_factory ? 2 : 3;
						msg += factory_msg;
					}
					msg += '</select></div>';

					layer.confirm(msg, {title:"关闭订单"}, function(index){
						var state_info = $(".js-cancelSle").val();
						if(state_info == 0){
							$.wrongTip($(".layui-layer-btn0"), "请选择取消订单原因");
							return;
						}
						manage.getApi("/index.php?m=shop&c=storeCommon&a=ajax_order_cancel", {
							order_id		: order_id,
							state_info		: state_info,
							status_flag 	: status,
							form_submit		: "ok"
						}, function(){
							setTimeout(function(){
								location.reload();
							},2000);
						});
					})
				});
				/*
				* 卖家备注
				 * */
				$(".js-orderDetail").on("click", ".js-remark", function(){
					var buyer_message = $.trim($(".js-sellerMessage").text());
					if(buyer_message == "-") buyer_message = "";
					var msg = '<textarea id="sellerMessage" placeholder="最多可输入200字" maxlength="200">' + buyer_message + '</textarea>';
					layer.alert(msg, {title:"卖家备注"}, function(index){
						var seller_remark = $.trim($("#sellerMessage").val());
						manage.getApi("/index.php?m=shop&c=Factory&a=seller_remark", {
							order_id		: order_id,
							seller_remark	: seller_remark
						}, function(){
							$(".js-sellerMessage").text(seller_remark);
						});
					})
				});
				/*
				 *发货
				 */
				$(".js-orderDetail").on("click", ".js-send", function(){
					layer.open({
						type: 2,
						title:"商品发货",
						area: ['750px', '300px'],
						content: gConfig.apiurl + "/shop/factory/tplsend?order_id=" + order_id
					});
				});
				/*
				 *修改物流
				 */
				$("#container").on("click", ".js-modifyExpress", function(){
					layer.open({
						type: 2,
						title:"修改物流",
						area: ['750px', '300px'],
						content: gConfig.apiurl + "/index.php?act=seller_center&op=tpl_order_send&order_id=" + order_id
					});
				});
				/*
				 *卖家确认收货
				 */
				$(".js-orderDetail").on("click", ".js-receiptBuyer", function(){
					var msg = '确认收货后，全木行将把货款结算给卖家<h4 class="fs16 fb cor-black mt10">确认您已收到货品？</h4>';
					Module.sendReceipt(msg, "/index.php?act=seller_order&op=order_receive", {order_id: order_id});
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
				});
			}
		}
		module.exports = Module;
	});
	seajs.use("s/orderdetail", function(module){
		module.init();
	});
})($);