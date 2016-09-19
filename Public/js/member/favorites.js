//收藏商品&收藏店铺
//date: 2016.06.01
//author zhouquan
(function($){
	define("m/favorites",function(require, exports, module){
		require("jquery/layer");
		require("com/global");
		var manage = require("com/manage");
		var Module = {
			init: function(){
				var favorType = $("#favorType").val() || "";
				/*
				 店铺--上新&热销切换
				 * */
				$(".js-tab").on("click", ".tag", function(){
					var me = $(this),
						n = me.index(),
						$li = me.closest("li");
					if(me.hasClass("curr")) return;
					me.addClass("curr").siblings(".tag").removeClass("curr");
					$(".js-goods:eq(" + n + ")", $li).show().siblings(".js-goods").hide();
				});
				/*
				多选取消收藏
				* */
				$(".js-favorBar").on("click", ".js-unfavorite", function(){
					var $inpCheck = $(".js-check:checked");
					if(favorType == "store"){
						var msg = "店铺";
					}else{
						var msg = "商品";
					}
					if($inpCheck.length < 1){
						$.wrongMsg("请选择" + msg);
						return;
					}
					var id_arr = [];
					id_arr = $inpCheck.map(function(){
						return $(this).closest("li").data("favid");
					}).get().join(",");
					var data = {
						fav_id	: id_arr,
						type	: favorType
					};
					Module.confirm_handle("确认选中的" + msg + "要取消收藏吗？",data);
				});
				/*
				单选取消收藏
				* */
				$(".js-favorList").on("click", ".js-unfavorite", function(){
					var me = $(this),
						$li = me.closest("li"),
						fav_id = $li.data("favid"),
						data = {
							fav_id	: fav_id,
							type	: favorType
						};
					if(favorType == "store"){
						var msg = "店铺";
					}else{
						var msg = "商品";
					}
					Module.confirm_handle("确认该" + msg + "要取消收藏吗？",data);
				});
			},
			confirm_handle: function(msg,data) {
				layer.confirm(msg, function(index){
					manage.getApi("/index.php?c=member&a=favor_store", data, function(){
						setTimeout(function(){
							location.reload();
						},2000);
					});
				})
			}
		}
		module.exports = Module;
	});
	seajs.use("m/favorites", function(module){
		module.init();
	});
})($);