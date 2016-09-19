//商品列表
//date: 2016.06.06
//author zhouquan
(function($){
	define("s/goodslist",function(require, exports, module){
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
			l_price = "",
            s_salenum = "",
            e_salenum = "",                        
			is_offline = $("#is_offline").val() ? $("#is_offline").val() : 0;
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
					s_salenum = $.trim($("input[name=s_salenum]").val());
					e_salenum = $.trim($("input[name=e_salenum]").val());
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
						s_salenum			: s_salenum,
						e_salenum			: e_salenum,
						curpage				: info.page || 1,
						pagesize			: info.size || Module.pagesize,
						is_offline          : is_offline
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
									goods_state = data[x].goods_state,
									goods_stateremark = data[x].goods_stateremark,
									goods_verify = data[x].goods_verify,
									goods_verifyremark = data[x].goods_verifyremark,
									goods_store_id = data[x].store_id,
									goods_store_url = data[x].url,
									goods_serial = data[x].goods_serial;
								goods_time = new Date(parseInt(goods_time)*1000).format("yyyy-MM-dd hh:mm:ss");
								var edit_href = gConfig.apiurl + "/StoreCommon/editGoods?commonid=" + goods_commonid,
									edit = '<a href="' + edit_href + '" class="link" target="_blank">编辑</a>',
									del = '<a href="javascript:void(0);" class="link js-del">删除</a>';
								if(pagemold == "sale"){
									var $a = '<a href="javascript:void(0);" class="link js-del">下架</a>'+ edit,
										td_gc_name = '';
								}else if (pagemold == "inventory"){
									$a = '<a href="javascript:void(0);" class="link js-del">上架</a>'+ edit,
										td_gc_name = '<td>' + gc_name + '</td>';
								}else{
									$a = goods_verify != 10 ? edit : '';
								}
								var str = '';
								str += '<tr data-id="' + goods_commonid + '">';
								str += '<td style="text-align:right;border-right:none 0;"><input type="checkbox" class="v-mid js-check" /></td>';
								str += '<td style="border-left:none 0;">';
								str += '<div class="goods-td cf">';
								str += '<a href="' + gConfig.apiurl + goods_store_url + '" class="pic" target="_blank"><img src="'+goods_image+'" width="60px" height="60px" alt="" /></a>';
								str += '<p class="h40"><a href="' + gConfig.apiurl  + goods_store_url + '" class="cor-blue" target="_blank">' + goods_name + '</a></p>';
								str += '<p class="standard">规格：' + spec_value + '</span></p></div></td>';
								str += td_gc_name;
								str += '<td>' + goods_price + '</td>';
								str += '<td>' + total_goods_storage + '</td>';
								if (pagemold != 'verify'){
									str += '<td>' + total_goods_salenum + '</td>';
								}
								str += '<td class="word-nor">' + goods_time + '</td>';
								if (pagemold != 'verify'){
									str += '<td>' + goods_serial + '</td>';
								}else{
									if (goods_verify == 10){
										var state = '待审核';
									}else if(goods_verify == 0){
										state = '未通过';                                        
									}
                                    goods_verifyremark = goods_verifyremark != null ? goods_verifyremark : '';
									if (goods_state == 10){
										state = '违规下架';
                                        goods_verifyremark = goods_stateremark != null ? goods_stateremark : '';
                                        $a = edit;
									}
									
									str += '<td>'+ state +'</td>';
									str += '<td>'+ goods_verifyremark +'</td>';
								}
								str += '<td width=200>' + $a + '</td>';
								str += '</tr>';
								html.push(str);
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
	seajs.use("s/goodslist", function(module){
		module.init();
	});
})($);