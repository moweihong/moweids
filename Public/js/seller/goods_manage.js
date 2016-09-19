//商品列表
//date: 2016.06.06
//author zhouquan
(function($){
	define("s/goods_manage",function(require, exports, module){
		require("jquery/layer");
		require("jquery/paging");
		require("com/global");
		var pagemold = $("#pagemold").val(),
			apiurl = $("#apiurl").val(),
			sortby = "",
			s_goods_name = "",
			goods_serial = "",
			stc_id = "",
			f_price = "",
			l_price = "";
		var manage = require("com/manage");
		var Module = {
			paging: new Paging(),
			pagesize: 10,
			init: function(){
				Module.getData();
				/*
				 *分页
				 */
				Module.paging.init({
					target	:"#jspage",
					pagesize: Module.pagesize,
					count	: $("#count").val(),
					current	: 1,
					toolbar	: true,
					callback:function(page, size, count){
						Module.getData({
							page: page,
							size: size
						});
					}
				});
				/*
				 *搜索
				 */
				$("#container").on("click", ".js-queryBtn", function(){
					s_goods_name = $.trim($("#goods_name").val());
					goods_serial = $.trim($("#goodscode").val());
					stc_id = $("#stc_id").val();
					f_price = $.trim($("input[name=f_price]").val());
					l_price = $.trim($("input[name=l_price]").val());
					Module.getData({isRender: true});
				});
				/*
				 *库存升降
				 */
				$("#container").on("click", ".js-storage", function(){
					if($(this).hasClass("disable")) return;
					if($("i", this).hasClass("ic-down-arrow")){
						$("i", this).removeClass("ic-down-arrow").addClass("ic-up-arrow");
						sortby = "total_goods_storage desc";
					}else{
						$("i", this).removeClass("ic-up-arrow").addClass("ic-down-arrow");
						sortby = "total_goods_storage asc";
					}
					Module.getData({isRender: true});
				});
				/*
				 *销量升降
				 */
				$("#container").on("click", ".js-salenum", function(){
					if($(this).hasClass("disable")) return;
					if($("i", this).hasClass("ic-down-arrow")){
						$("i", this).removeClass("ic-down-arrow").addClass("ic-up-arrow");
						sortby = "total_goods_salenum desc";
					}else{
						$("i", this).removeClass("ic-up-arrow").addClass("ic-down-arrow");
						sortby = "total_goods_salenum asc";
					}
					Module.getData({isRender: true});
				});
				/*
				 *发布时间升降
				 */
				$("#container").on("click", ".js-addtime", function(){
					if($(this).hasClass("disable")) return;
					if($("i", this).hasClass("ic-down-arrow")){
						$("i", this).removeClass("ic-down-arrow").addClass("ic-up-arrow");
						sortby = "goods_time desc";
					}else{
						$("i", this).removeClass("ic-up-arrow").addClass("ic-down-arrow");
						sortby = "goods_time asc";
					}
					Module.getData({isRender: true});
				});
			},
			/*
			 *获取商品数据
			 */
			getData: function(info){
				var info = info || {};
				manage.ajax({
					url: gConfig.apiurl + apiurl,
					data: {
						type				: pagemold,
						sortby				: sortby,
						goods_name			: s_goods_name,
						goods_serial		: goods_serial,
						stc_id				: stc_id,
						f_price				: f_price,
						l_price				: l_price,
						curpage				: info.page || 1,
						pagesize			: info.size || Module.pagesize
					},
					success: function(result){
						$("#container .loading-bar").remove();
						$("#container .no-data").remove();
						var resultText = result.resultText || {},
							total = resultText.total || 0,
							curpage = resultText.curpage || 1,
							message = resultText.message || "获取数据失败";
						if(result.code == 1){
							$("#container #jspage").add("#container .js-tableTop").add("#container .table").show();
							var data = resultText.goods_list || [],
								html = [],
								x;
							for(x in data){
								var goods_id = data[x].goods_id || "",
									goods_commonid = data[x].goods_commonid || "",
									goods_name = data[x].goods_name || "",
									spec_value = data[x].spec_value || "",
									goods_image = data[x].goods_image || "",
									gc_name = data[x].gc_name || "",
									goods_price = data[x].goods_price || "",
									total_goods_storage = data[x].total_goods_storage || "",
									total_goods_salenum = data[x].total_goods_salenum || "",
									goods_time = data[x].goods_time || 0,
									goods_serial = data[x].goods_serial;
								goods_time = new Date(parseInt(goods_time)*1000).format("yyyy-MM-dd hh:mm:ss");
								if(pagemold == "sale"){
									var $a = '<a href="javascript:void(0);" class="link js-del">下架</a>',
										td_gc_name = '';
								}else{
									var edit_href = gConfig.apiurl + "/index.php?act=seller_center&op=edit_goods&commonid=" + goods_commonid,
										$a = '<a href="' + edit_href + '" class="link" target="_blank">编辑</a> <a href="javascript:void(0);" class="link js-del">上架</a><a href="javascript:void(0);" class="link js-del">删除</a>',
										td_gc_name = '<td>' + gc_name + '</td>';
								}
								var $tr = '<tr data-id="' + goods_commonid + '">\
									<td style="text-align:right;border-right:none 0;"><input type="checkbox" class="v-mid js-check" /></td>\
									<td style="border-left:none 0;">\
										<div class="goods-td cf">\
											<a href="' + gConfig.apiurl + "/index.php?act=goods&op=index&goods_id=" + goods_id + '" class="pic" target="_blank"><img src="'+goods_image+'" width="60px" height="60px" alt="" /></a>\
											<p class="h40"><a href="' + gConfig.apiurl + "/index.php?act=goods&op=index&goods_id=" + goods_id + '" class="cor-blue" target="_blank">' + goods_name + '</a></p>\
											<p class="standard">规格：' + spec_value + '</span></p>\
										</div>\
									</td>\
									' /*+ td_gc_name */+ '\
									<td>' + goods_price + '</td>\
									<td>' + total_goods_storage + '</td>\
									<td>' + total_goods_salenum + '</td>\
									<td>' + goods_time + '</td>\
									<td>' + goods_serial + '</td>\
									<td>' + $a + '</td>\
								</tr>';
								html.push($tr);
							}
							$("#container .table tbody").html(html.join(""));
							try{
								if(info.isRender){
									Module.paging.render({target:'#jspage', pagesize:Module.pagesize, count:total, current:1});
								}
							}catch(e){}
						}else if(result.code == 401){
							$("#container .table tbody").empty();
							$("#container #jspage").add("#container .js-tableTop").add("#container .table").hide();
							$("#container .table").after('<p class="no-data" style="margin-top:-1px;">暂无数据</p>');
						}else{
							$("#container .table tbody").empty();
							$("#container .table").after('<p class="no-data" style="margin-top:-1px;">' + message + '</p>');
						}
					},
					error: function(){
						$("#container .no-data").remove();
						$("#container .loading-bar").remove();
						$("#container .table tbody").empty();
						$("#container #jspage").add("#container .js-tableTop").add("#container .table").hide();
						$("#container .table").after('<p class="no-data" style="margin-top:-1px;">网络异常，请刷新页面</p>');
					}
				});
			}
		}
		module.exports = Module;
	});
	seajs.use("s/goods_manage", function(module){
		module.init();
	});
})($);