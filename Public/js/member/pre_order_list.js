//预订单
//date: 2016.08.07
//author zhouquan
(function($){
	define("m/pre_order_list",function(require, exports, module){
		require("jquery/layer");
		require("com/global");
		var manage = require("com/manage");
		var Module = {
			init: function(){
				/*
				* 初始化
				* */
				if($(".js-preOrder tbody tr").length == 0){
					$(".js-calculateBar").hide().after('<p class="no-data">暂无预订单记录</p>');
				}
				/*
				**勾选店铺使店铺下的订单选中
				* */
				$(".js-preOrder").on("change", ".js-allCheck", function(){
					if($(this).is(":checked")){
						$(".js-partCheck").prop("checked", true);
					}else{
						$(".js-partCheck").prop("checked", false);
					}
					setTimeout(function(){
						Module.calculate();
					}, 200);
				});
				$(".js-preOrder").on("change", ".js-partCheck", function(){
					var me = $(this),
						shop_id= me.closest("tr").attr("id"),
						allcheck = $(".js-allCheck");
					if(me.is(":checked")){
						$(".js-preOrder tr[for-id=" + shop_id + "] .js-check").prop("checked", true);
						var len = $(".js-check").length,
							checkLen = $(".js-check:checked").length;
						if(len == checkLen){
							allcheck.prop("checked", true);
						}else{
							allcheck.prop("checked", false);
						}
					}else{
						$(".js-preOrder tr[for-id=" + shop_id + "] .js-check").prop("checked", false);
						allcheck.prop("checked", false);
					}
					Module.calculate();
				});
				$(".js-preOrder").on("change", ".js-check", function(){
					var me = $(this),
					    for_id = me.closest("tr").attr("for-id"),
					    len = $(".js-preOrder [for-id=" + for_id + "] .js-check").length,
						checkLen = $(".js-preOrder [for-id=" + for_id + "] .js-check:checked").length,
						partCheck = $(".js-preOrder [id=" + for_id + "] .js-partCheck");
					if(me.is(":checked")){
						if(len == checkLen){
							partCheck.prop("checked", true);
						}else{
							partCheck.prop("checked", false);
						}
					}else{
						partCheck.prop("checked", false);
					}
					Module.calculate();
				});
				/*
				 ** 增加数量
				 * */
                                //ajax同步
                                $.ajaxSetup({  
                                  async : false  
                                });                                
				$(".js-figureInput").on("click", ".js-increase", function () {
		        	var quantity = $(this).siblings(".js-number"),
		            	num = parseInt(quantity.val());
					if(num >= 999999) return;
					quantity.val(num + 1);
                                        var id=$(this).parents('td').parent('tr').data('id');
                                        var goods_id=$(this).parents('td').parent('tr').data('goods_id');
                                        $.post('index.php?act=member&op=pre_order',{'id':id,'goods_num':quantity.val(),'goods_id':goods_id},function(data){
                                                           data=eval("("+data+")");
                                                            if(data.status==-1){
                                                                layer.alert(data.msg);
                                                                quantity.val(data.num);
                                                            }                                       
                                        })
					Module.calculate();
		        });
				/*
				 ** 减少数量
				 * */
				$(".js-figureInput").on("click", ".js-decrease", function () {
					var quantity = $(this).siblings(".js-number"),
						num = parseInt(quantity.val());
					if(num > 1){
						quantity.val(num - 1);
                                                var id=$(this).parents('td').parent('tr').data('id');
                                                var goods_id=$(this).parents('td').parent('tr').data('goods_id');
                                                $.post('index.php?act=member&op=pre_order',{'id':id,'goods_num':quantity.val(),'goods_id':goods_id},function(data){
                                                })                                               
					}
					Module.calculate();
				});
				/*
				 ** 输入数量
				 * */
				$(".js-figureInput").on("blur", ".js-number", function () {
					var num = parseInt($(this).val());
					if (!num || num == 0) {
						$(this).val(1);
					}
                                        var pre_goods=$(this);
                                        var id=$(this).parents('td').parent('tr').data('id');
                                        var goods_id=$(this).parents('td').parent('tr').data('goods_id');
                                        $.post('index.php?act=member&op=pre_order',{'id':id,'goods_num':pre_goods.val(),'goods_id':goods_id},function(data){
                                                           data=eval("("+data+")");
                                                            if(data.status==-1){
                                                                layer.alert(data.msg);
                                                                pre_goods.val(data.num); 
                                                            }
                                                            
                                        })                                         
					Module.calculate();
				});
				/*
				 ** 结算按钮
				 * */
				$(".js-preOrder").on("click", ".js-balanceBtn", function () {
					if ($(".js-preOrder .js-check:checked").length == 0) {
						layer.alert('请选中要结算的商品');
						return false;
					}
                                        if($("#total_price").html()==0){
						layer.alert('结算价格不能为0');
						return false;                                            
                                        }
                                        var $inpCheck = $(".table .js-check:checked").not(":disabled");
					var id_arr = $inpCheck.map(function(){
						return $(this).closest("tr").data("goods_id");
					}).get().join(","); 
					var order_id_arr = $inpCheck.map(function(){
						return $(this).closest("tr").data("order_id");
					}).get().join(",");                                         
					var amount = $inpCheck.map(function(){
                                                var price=$(this).closest("tr").find('.js-price').html();
                                                var num=$(this).closest("tr").find('.js-number').val();
                                                var order_id=$(this).closest("tr").data("order_id");
                                                var store_id=$(this).closest("tr").data("store_id");
                                                var str=JSON.stringify({order_id:order_id,num:num,price:price,store_id:store_id});
                                                //var str=order_id+"|"+price*num;
						return str;
					}).get().join("|");
                                        $("#goods_id_str").val(id_arr);
                                        $("#order_id_str").val(order_id_arr);
                                        $("#amount").val(amount);
                                        $("#total_goods_price").val($("#total_price").html());
                                        $('#form_buy').submit();                                        
				});
			},
			/*
			 ** 计算所选商品数量及总价
			 * */
			calculate: function(){
				var $tr = $(".js-check:checked").closest("tr"),
					total_price = 0,
					total_quantity = 0;
				for(var i=0; i<$tr.length; i++){
					var trObj = $tr[i],
						price = parseFloat($(trObj).find(".js-price").text()),
						quantity = parseInt($(trObj).find("[name='quantity']").val());
					total_quantity += quantity;
					total_price += price*quantity;
				}
				$("#selected_quantity").text(total_quantity);
				$("#total_price").text(total_price);
			}
		}
		module.exports = Module;
	});
	seajs.use("m/pre_order_list", function(module){
		module.init();
	});
})($);