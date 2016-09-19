//订单退款
//date: 2016.06.07
//author zhouquan
(function($){
	define("m/orderrefund",function(require, exports, module){
		require("jquery/layer");
		var upload = require("jquery/upload");
		var manage = require("com/manage");
		var param = manage.getParam();
		var orderID = param.order_id || $("#orderID").val() || "",
			refundID = param.refund_id || $("#refundID").val() || "",
			goodsID = param.goods_id || $("#goodsID").val() || "",
			order_type = $("input:hidden[name='order_type']").val() || "",
			order_state = $("input:hidden[name='order_state']").val() || "";
		var Module = {
			init: function(){
				/*
				 * 申请服务 & 是否收货
				 * */
				var refundCause = $("#refundCause").val() || 0,
					returns_message_sel = '<option value="0">请选择退款退货原因</option><option value="质量问题">质量问题</option><option value="实物与描述不符">实物与描述不符</option><option value="假冒品牌">假冒品牌</option><option value="不喜欢">不喜欢</option><option value="卖家发错货">卖家发错货</option>option value="其他">其他</option>',
					refund_message_sel1  = ' <option value="0">请选择仅退款原因</option><option value="快递一直未送达">快递一直未送达</option><option value="空包裹/少货">空包裹/少货</option><option value="未按约定时间发货">未按约定时间发货</option><option value="快递无跟踪记录">快递无跟踪记录</option><option value="多拍/排错/不想要">多拍/排错/不想要</option><option value="其他">其他</option>',
					refund_message_sel2  = '<option value="0">请选择仅退款原因</option><option value="质量问题">质量问题</option><option value="商品与描述不符">商品与描述不符</option><option value="假冒品牌">假冒品牌</option><option value="不喜欢">不喜欢</option><option value="卖家发错货">卖家发错货</option><option value="其他">其他</option>';
				if($(".js-isReceiving").length && $("select[name=refund_type]").length && $("select[name=buyer_message]").length){
					apply_sel();
				}
				$(".js-goodsmoneyback").on("change", "select[name=refund_type], input[name=isDelivery]", function(){
					apply_sel();
				});
				function apply_sel(){
					if($("select[name=refund_type]").val() == 1){ //仅退款
						$(".js-isReceiving").show();
						if(!$("input[name=isDelivery]:checked").length){
							$(".js-cause").hide();
						}else if($("input[name=isDelivery]:checked").val() == 1){ //已收货
							$(".js-cause").show();
							$("select[name=buyer_message]").html(refund_message_sel2);
						}else if($("input[name=isDelivery]:checked").val() == 2){ //未收货
							$(".js-cause").show();
							$("select[name=buyer_message]").html(refund_message_sel1);
						}
						if (order_type == 2) {//分期购
							$("#easypay_goodsmoneyback_tips1").hide();
							$("#easypay_goodsmoneyback_tips2").hide();
						}
					}else{ //退货退款
						$(".js-isReceiving").hide();
						$(".js-cause").show();
						$("select[name=buyer_message]").html(returns_message_sel);
						if (order_type == 2){//分期购
							$("#easypay_goodsmoneyback_tips1").show();
							$("#easypay_goodsmoneyback_tips2").show();
						}
					}
					$("select[name=buyer_message]").val(refundCause);
				}
				var selected_val = $("select[name=buyer_message] option:selected").val();
				if (order_type == 2){
					Module.show_max_money(selected_val);
				}
				/*
				 * 退款原因
				 * */
				$(".js-goodsmoneyback").on("change", "select[name=buyer_message]", function(){
					refundCause = $(this).val();
				});
				/*
				 * 图片上传
				 * */
				upload.base({
					storage: "voucher",
					fileSingleSizeLimit: 1
				});

				if (order_type == 2){//分期购
					$("select[name=buyer_message]").change(function(){
						Module.show_max_money($(this).children('option:selected').val());
					});
				}

				/*
				* 提交仅退款申请
				* */
				$(".js-orderRefund").on("click", ".js-submitBtn", function(){
					if($("select[name=buyer_message]").val() == 0){
						$.wrongMsg("请选择退款原因");
						return false;
					}
					var linking = {
						act		: "member",
						op		: "goodsmoneyback_processing",
						order_id: orderID
					}
					Module.sendApply("/index.php?act=member&op=apply_for_refund", "/index.php?" + $.param(linking));
				});
				/*
				 * 提交退货退款申请
				 * */
				$(".js-goodsmoneyback").on("click", ".js-submitBtn", function(){
					var refundMoney = $(".js-refundMoney").text() || 0;
					if($("select[name=refund_type]").val() == 1 && !$("input[name=isDelivery]:checked").length){
						$.wrongMsg("请选择是否收货");
						return false;
					}
					if($("select[name=buyer_message]").val() == 0){
						$.wrongMsg("请选择退款原因");
						return false;
					}
					if(!$("input[name=refund_amount]").val()){
						$.wrongMsg("请输入退款金额");
						return false;
					}
					if(parseFloat($("input[name=refund_amount]").val()) > parseFloat(refundMoney)){
						$.wrongMsg('退款金额不能大于<b class="cor-red">' + refundMoney + '</b>元');
						return false;
					}
					if(parseFloat($("input[name=refund_amount]").val()) <= 0.00){
						$.wrongMsg('退款金额必须大于0.00元');
						return false;
					}
					if($("select[name=refund_type]").val() == 1){ //仅退款
						var obj = {is_received: $("input[name=isDelivery]:checked").val()};
					}else{ //退货退款
						var obj = {is_received: ""};
					}
					var linking = {
						act		: "member",
						op		: "goodsmoneyback_processing",
						order_id: orderID,
						goods_id: goodsID
					}
					Module.sendApply("/index.php?act=member&op=apply_for_goodsmoneyback", "/index.php?" + $.param(linking), obj);
				});
				/*
				 * 提交退货信息
				 * */
				$(".js-goodsmoneyback").on("click", ".js-submitExpress", function(){
					if($("select[name=express_id]").val() == 0){
						$.wrongMsg("请选择物流公司");
						return false;
					}
					if(!$.trim($("input[name=invoice_no]").val())){
						$.wrongMsg("请输入物流单号");
						return false;
					}
					var linking = {
						act		: "member",
						op		: "order_detail",
						order_id: orderID
					}
					Module.sendApply("/index.php?act=member&op=goodsmoneyback_sendgoods", "/index.php?" + $.param(linking));
				});
				/*
				 * 撤销退款申请 & 撤销退货退款申请
				 * */
				$("#container").on("click", ".js-recallRefund, .js-recallReturns", function(){
					Module.sendRecall("/index.php?act=member&op=cancel_refund_apply", {refund_id: refundID});
				});
				/*
				 *发表留言
				 */
				$("#container").on("click", ".js-comments", function(){
					var linking = {
						act			: "member",
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
			//制保留2位小数，如：2，会在2后面补上00.即2.00
			toDecimal : function toDecimal2(x) {
				var f = parseFloat(x);
				if (isNaN(f)) {
					return false;
				}
				var f = Math.round(x*100)/100;
				var s = f.toString();
				var rs = s.indexOf('.');
				if (rs < 0) {
					rs = s.length;
					s += '.';
				}
				while (s.length <= rs + 2) {
					s += '0';
				}
				return s;
			},
			show_max_money:function (selected_val) {
				var order_amount = parseFloat($('.order_amount').text()),//消费金额
					interest_total = parseFloat($('.interest_total').text()),//利息
					factorage = parseFloat(Module.toDecimal($('.factorage').text())),//手续费
					format_amount = 0.00,
					max_back_amount = 0.00;
				if (order_state == 30){//已发货
					if (selected_val == '不喜欢') {//买家责任
						max_back_amount = order_amount;
					}else{
						if (interest_total != 0.00){//不免息
							max_back_amount = order_amount + interest_total + factorage;
						}else {
							max_back_amount = order_amount + factorage;
						}
					}
				}else if (order_state == 40){
					if (selected_val == '不喜欢') {//买家责任
						if (interest_total != 0.00) {//不免息
							max_back_amount = order_amount;
						}else{
							max_back_amount = order_amount - interest_total;
						}
					}else{
						if (interest_total != 0.00){//不免息
							max_back_amount = order_amount + interest_total + factorage;
						}else {
							max_back_amount = order_amount + factorage;
						}
					}
				}else{//未发货
					if (selected_val == '未按约定时间发货' || selected_val == '缺货'){//商家责任
						if (interest_total != 0.00){//不免息
							max_back_amount = order_amount + interest_total + factorage;
						}else {
							max_back_amount = order_amount + factorage;
						}
					}else {
						if (interest_total != 0.00){//不免息
							max_back_amount = order_amount;
						}else {
							max_back_amount = order_amount - interest_total;
						}
					}
				}

				max_back_amount = selected_val == '0' ? 0.00 : max_back_amount;
				format_amount = max_back_amount.toFixed(2);
				$('.max_back_amount').text(format_amount);
				$('input[name=refund_amount]').val(format_amount);
				$('.js-refundMoney').text(format_amount);
			},
			/*
			 * 发送数据--申请仅退款 & 申请退货退款 * 提交退货信息
			 * */
			sendApply: function(api,herf,info){
				var info = info || {},
					voucher = [],
					data = $("#form").serialize();
				data = manage.serializeJson(data);
				var applypage = data.applypage || "";
				$("#fileList .file-item").each(function(){
					var s_pic = $("img", this).attr("src") || "",
						m_pic = $("img", this).data("rel") || "";
					var upload_obj = {
						s_pic: s_pic,
						m_pic: m_pic
					}
					voucher.push(upload_obj);
				});
				data.voucher = voucher;
				var newdata =  $.extend(data, info);
				manage.getApi(api, newdata, function(result){
					setTimeout(function(){
						if(applypage == "apply_for_goodsmoneyback" || applypage == "apply_for_refund"){
							window.location = gConfig.apiurl + herf + "&refund_id=" + result.refund_id;
						}else{
							window.location = gConfig.apiurl + herf;
						}
					},1000);
				});
			},
			/*
			 * 发送数据--撤销仅退款申请 & 撤销退货退款申请
			 * */
			sendRecall: function(api, data){
				layer.confirm('<p>撤销后，本次申请将关闭，你还可以再次申请。</p><p>确定取消吗？</p>', {
					btn: ['确定','再想想'],
					title: '撤销申请'
				}, function(){
					manage.getApi(api, data, function(){
						setTimeout(function(){
							location.href = "index.php?act=member&op=orders";
						},1000);
					});
				});
			}
		}
		module.exports = Module;
	});
	seajs.use("m/orderrefund", function(module){
		module.init();
	});
})($);