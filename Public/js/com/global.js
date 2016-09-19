/**
* 全局公用
*@author zhouquan
*/
(function($){
	define("com/global",function(require, exports, module){
		var manage = require("com/manage");
		var Module = {
			init: function(){
				/*
				*删除
				*/
				$(".table").on("click", ".js-del", function(){
					var me = $(this),
						txt = me.data("msg") || me.text(),
						$tr = me.closest("tr"),
						id = $tr.data("id"),
						data = {id: id},
						url = $("#deleteUrl").val(),
						msg =  $("#deleteUrl").data("msg") || "列表",
						isreload = $("#deleteUrl").data("isreload") || "yes";
					msg = '<h3 class="tit">确认要' + txt + '该' + msg + '吗?</h3>';
					layer.confirm(msg, function(index){
						confirm_operation(index, url, data, $tr, isreload);
					});
				});
				/*
				*批量删除
				*/
				$("body").on("click", ".js-batchDel", function(){
					var me = $(this),
						txt = me.data("msg") || me.text(),
					 	msg =  $("#deleteUrl").data("msg") || "列表",
						$inpCheck = $(".table .js-check:checked").not(":disabled"),
						$tr = $inpCheck.closest("tr"),
						id_arr = [];
					if($inpCheck.length < 1){
						$.wrongMsg("请选择" + msg);
						return;
					}
					id_arr = $inpCheck.map(function(){
						return $(this).closest("tr").data("id");
					}).get().join(",");
					var data = {id: id_arr};
					var url = $("#deleteUrl").val(),
						isreload = $("#deleteUrl").data("isreload") || 1;
					msg = '<h3 class="tit">确认要' + txt + '选中的' + $inpCheck.length + "个" + msg + '吗?</h3>';
					layer.confirm(msg, function(index){
						confirm_operation(index, url, data, $tr, isreload);
					});
				});
				var isClick = false;
				function confirm_operation(index, url, data, $tr, isreload){
					if(isClick) return;
					isClick = true;
					manage.ajax({
						url: gConfig.apiurl + url,
						data: data,
						success: function(result){
							isClick = false;
							var resultText = result.resultText || {},
								icon = 2;
							if(result.code == 1){
								icon = 1;
								if(isreload == "yes"){
									location.reload();
								}else{
									var $type = $("#deleteUrl").data("type") || "";
									if($type == "goodslist"){ //在售中的商品&仓库中的商品
										seajs.use("s/goodslist", function(Goodslist){
											Goodslist.getData({isRender: true});
										});
									}else if($type == "shipping"){ //运费模板
										$tr.closest("table").remove();
									}else{
										$tr.remove();
									}
								}
							}
							$.wrongMsg(resultText.message || "操作失败", icon);
						},
						error: function(){
							isClick = false;
						}
					});
				};
				/*
                *全选
                */
				$("#container").on("change", ".js-allCheck", function(){
					var me = $(this),
						allcheck = $(".js-allCheck").not(":disabled"),
						checkbox = $(".js-check").not(":disabled");
					if(me.is(":checked")){
						checkbox.add(allcheck).prop("checked", true);
					}else{
						checkbox.add(allcheck).prop("checked", false);
					}
				});
				$("#container").on("change", ".js-check", function(){
					var me = $(this),
						len = $(".js-check").length,
						checkLen = $(".js-check:checked").length,
						allcheck = $(".js-allCheck");
					if(me.is(":checked")){
						if(len == checkLen){
							allcheck.prop("checked", true);
						}else{
							allcheck.prop("checked", false);
						}
					}else{
						allcheck.prop("checked", false);
					}
				});
			}
		};
		module.exports = Module;
	});
	seajs.use("com/global", function(module){
		module.init();
	});
})($);