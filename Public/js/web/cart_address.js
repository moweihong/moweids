//购物流程
//date: 2016.08.25
//author longwei
(function($){
	define("web/cart_address",function(require, exports, module){
		require("jquery/form");
		require("com/global");
		var manage = require("com/manage");
		var Module={
			init:function(){
				/*
               	**弹框-增加收货地址
               	 */
				$(".js-add-addr").on("click",function(){
					layer.open({
						type: 2,
						title:"新增收货地址",
						area: ['550px', '550px'],
						content: gConfig.apiurl + "/index.php?c=member&a=tplchangeaddress&frompage=buy"
					});
				});
               	/*
               	**弹框-编辑收货地址
               	 */
               	$('.js-bianji').live("click",function(event){
	            	if(event.target == this){
	               		event.stopPropagation(); //冒泡阻止   		
	               		var $li = $(this).closest('li'),
	               			id = $li.data("id");
	               		layer.open({
							type: 2,
							title:"编辑收货地址",
							area: ['550px', '550px'],
							content: gConfig.apiurl + "/index.php?c=member&a=tplchangeaddress&frompage=buy&address_id=" + id
						});
	               	}
               	});
               	/*
               	**删除收货地址
               	 */
               	$('.js-delAttr').live('click',function(event){
               		if(event.target == this){
               			event.stopPropagation(); //冒泡阻止
	               		var me = $(this),
	               			$li = me.closest('li'),
	               			id = $li.data("id");
	               		layer.confirm("确定要删除该条地址吗？", function(index){
							manage.getApi("/index.php?c=member&a=delete_address",{
								id: id
							}, function(result){
								$li.remove();
								$('.js-addr').html('');
		               			$('.js-addr-name').html('');
		               			$('.js-addr-phone').html('');
							});
							layer.close(index);
						});
					}
               	});
               	/*
               	**默认地址
               	 */
               	$('.js-select-address').live('click',function(){
               		var me = $(this),
               			addrname = me.find('.js-name').text(),
               			address = me.find('.js-address').text(),               			 
               			addrphone = me.find('.js-phone').text();
               		if(me.hasClass('add-default-address')){
               			//判断是不是收货地址，如果是收货地址点了没用，如果不是就设为收货地址
               		}else{
               			if(me.is('li')){
	               			var id = me.data("id");
		               		manage.getApi('/index.php?c=member&a=default_address',{
		               			address_id: id
		               		},function(){
		               			$('.js-add-address li').removeClass('add-default-address');
		               			me.addClass('add-default-address');
		               			// me.find('.js-select-address').html('收货地址');
		               			// me.siblings('li').find('.js-select-address').html('设为收货地址');
		               			$('.js-addr').html(address);
		               			$('.js-addr-name').html(addrname);
		               			$('.js-addr-phone').html(addrphone);
		               		});
	               		}else{
		           		    var $li = me.closest('li'),
		           				id = $li.data("id");
		               		manage.getApi('/index.php?c=member&a=default_address',{
		               			address_id: id
		               		},function(){
		               			$('.js-add-address li').removeClass('add-default-address');
		               			me.addClass('add-default-address');
		               			// me.html('收货地址');
		               			// $li.siblings('li').find('.js-select-address').html('设为收货地址');
		               			$('.js-addr').html(address);
		               			$('.js-addr-name').html(addrname);
		               			$('.js-addr-phone').html(addrphone);
		               		});
	               		}
               		}
               	});
               	/**
				 * 支付方式-工厂
				 */
				$(".js-add-pay li").live("click", function(){
					$(this).addClass("add-default").siblings("li").removeClass("add-default");
				});
               	/*
               	**提交订单-经销商
               	 */
               	$("#submitOrder").on("click", function(){
               		var address_id = $(".add-default-address").data("id") || "";
					if (!address_id){
						$.wrongMsg("请选择收货地址");
						return;
					}
					var data = $("#order_form").serialize();
					data = manage.serializeJson(data);
					var cartid_arr = [],
						pay_message = $('.js-pay-message').val();
					cartid_arr = $(".js-cartidArr").map(function(){
						return $(this).val();
					}).get();
					data.cart_id = cartid_arr;
					data.address_id = address_id;
					data.pay_message = pay_message;
					manage.getApi("/Shop/Buy/buyStep2", data, function(result){
						parent.layer.closeAll("dialog");
						var url = result.url || "";
						window.location = url;
               		});
				});
               	/*
               	**提交订单-工厂
               	*/
				$('#submitOrder2').on('click',function(){
					var address_id = $(".add-default-address").data("id") || "",
						payment_code = $('.js-add-pay .add-default').attr("payment_code") || "";
					if (!address_id){
						$.wrongMsg("请选择收货地址");
				        return;
				    }
					if(!payment_code){ //判断是否选择支付方式
						$.wrongMsg("您选择支付方式");
						return;
					}
				    $("#address_id").val(address_id);
				    $("#payment_code").val(payment_code);
				    $("#order_form").submit();
				});
			}
		}
		module.exports = Module;
	});
	seajs.use("web/cart_address", function(module){
		module.init();
	});
})($);