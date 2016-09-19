//发布商品 & 编辑商品
//date: 2016.06.16
//author zhouquan
(function($){
	define("s/goodsadd",function(require, exports, module){
		require("jquery/layer");
		require("jquery/form");
		var upload = require("jquery/upload");
		var manage = require("com/manage");
		var param = manage.getParam();
		var type_id = param.type_id || $("#typeid").val() || "",
			gc_id = param.gc_id || $("#gcid").val() || "",
			ct_1 = param.ct_1 || $("#ct1").val() || "",
			ct_id = param.ct_id || $("#ctid").val() || "",
			form = $("#form").form();
		var Module = {
			init: function () {
				/*
				 * 图片上传
				 * */
				upload.base({
					storage: "goods",
					isCover: true
				});
				/*
				 * 富文本编辑器
				 * */
				var ue = UE.getEditor("g_body", {
					toolbars: [['fontfamily', 'fontsize', '|', 'bold', 'italic', 'underline', 'forecolor', 'backcolor', '|', 'justifyleft', 'justifycenter', 'justifyright', 'justifyjustify', '|', 'lineheight', '|', 'insertorderedlist', 'insertunorderedlist', '|', 'simpleupload', 'insertimage']],
					enableAutoSave: false,//关闭自动保存
					elementPathEnabled: false, //关闭元素路径
					wordCount: false, //关闭字数统计
					autoHeightEnabled: false, //关闭自动长高
					initialFrameHeight: 300,
					initialFrameWidth: "100%",
					initialStyle: "p{line-height:1em;font-size: 14px;}"
				});
				/*
				 * 字数限制
				 * */
				$(".js-textCount").each(function(){
					var me = $(this),
						$input = me.closest("span").siblings("input.text"),
						len = $input.val().length,
						maxleng = $input.attr("maxlength") || 30;
					if(len > maxleng) len = maxleng;
					me.text(len);
					$input.keyup(function(){
						len = $(this).val().length;
						if(len > maxleng) len = maxleng;
						me.text(len);
					})
				})
				/*
				 * 商品属性
				 * */
				$('.js-attrSelect').each(function () {
					Module.attr_selected($(this));
				});
				$(".add-goods-box").on("change", ".js-attrSelect", function (){
					Module.attr_selected($(this));
				});
				/*
				 * 选择交易属性
				 * */
				$(".add-goods-box").on("change", ".js-specCheck", function (){
					Module.trade_attr();
				});
				/*
				 * 添加交易属性
				 * */
				var specInp = "";
				$(".add-goods-box").on("click", ".js-specAdd", function (){
					$(this).hide().siblings(".js-specAdd2").show();
				}).on("click", ".js-specVerify", function (){ //确认
					var me = $(this),
						sp_id = me.data("spid"),
						$specAddLine = me.closest(".js-specAddLine"),
						newspec = $.trim($(".js-newspec", $specAddLine).val());
					if(!newspec){
						$.wrongMsg("请填写规格名称");
						return;
					}
					var pid = layer.load(360);
					manage.ajax({
						url: gConfig.apiurl + "/StoreCommon/ajaxAddSpec",
						data: {
							name	: newspec,
							gc_id	: gc_id,
							sp_id	: sp_id
						},
						success: function(result){
							layer.close(pid);
							var resultText = result.resultText || {},
								data = resultText.data || {},
								value_id = data.value_id;
							if(result.code == 1){
								$specAddLine.prev(".js-checkLine").append('<span class="mr20">\
										<input type="checkbox" class="v-mid mr5 js-specCheck" typename="sp_val[' + sp_id + '][' + value_id + ']" data-id="' + value_id + '" data-val="' + newspec + '" />\
										<input type="text" class="text js-specInp" data-id="' + value_id + '" value="' + newspec + '" placeholder="请填写规格名称" maxlength="30" />\
									</span>');
								$(".js-newspec", $specAddLine).val("");
								me.closest(".js-specAdd2").hide().siblings(".js-specAdd").show();
							}else{
								$.wrongMsg(resultText.message || "操作失败");
							}
						}
					});
				}).on("click", ".js-specCancel", function (){ //取消
					$(this).closest(".js-specAdd2").hide().siblings(".js-specAdd").show();
				}).on("focus", ".js-specInp", function (){ //规格输入框聚集
					specInp = $.trim($(this).val());
				}).on("blur", ".js-specInp", function (){ //规格输入框失集
					var me = $(this),
						id = me.data("id"),
						val = $.trim(me.val());
					if(!val){
						val = specInp;
						me.val(val);
					}
					me.siblings(".js-specCheck").data("val", val);
					$(".js-specTable tbody em[data-id=" + id+ "]").text(val);
				});
				/*
				 * 交易属性-输入价格失去焦点
				 * */
				$(".add-goods-box").on("blur", ".js-specPrice", function (){
					Module.price_least();
				});
				/*
				 * 交易属性-输入库存失去焦点
				 * */
				$(".add-goods-box").on("blur", ".js-specStorage", function (){
					Module.storages_count();
				});
				/*
				 * 保存&预览
				 * */
				$(".add-goods-box").on("click", ".js-saveBtn", function(){
					if(!form.valid()){
						$.wrongMsg("必填项不能为空");
						return false;
					}
					var isPass = true;
					if($("#fileList .file-item").length < 1){
						$.wrongMsg("请上传最少一张商品图片");
						return;
					}
					if($("input[name=iscustom]").length && !$("input[name=iscustom]:checked").val()){
						$.wrongMsg("请选择是否可定制");
						return;
					}
					if($("input[name=iscommend]").length && !$("input[name=iscommend]:checked").val()){
						$.wrongMsg("请选择是否推荐");
						return;
					}
					if($("input[name=freight]").length && !$("input[name=freight]:checked").val()){
						$.wrongMsg("请选择运费模板");
						return;
					}
					$(".js-specTable .js-required").each(function(){
						var me = $(this),
							val = $.trim(me.val());
						if(!val){
							$.wrongMsg("请填写价格和库存");
							isPass = false;
							return false;
						}
					});
					if(!isPass) return;
					var goods_images = [];
					$("#fileList .file-item").each(function(){
						var $input = $(".info input", this),
							m_pic = $("img", this).data("rel") || "";
						if($input.is(":checked")){
							var fengmian = 1;
						}else{
							var fengmian = 0;
						}
						var images_obj = {
							m_pic: m_pic,
							fengmian: fengmian
						}
						goods_images.push(images_obj);
					});
					var me = $(this),
						g_state = me.data("mold"),
						data = $("#form").serialize();
					data = manage.serializeJson(data);
					delete data.cover;
					data.type_id = type_id;
					data.gc_id = gc_id;
					data.gc_name = $(".js-gcategory").text();
					data.ct1 = ct_1;
					data.cate_id = ct_id;
					data.b_id = $("select[name=b_name] option:selected").data("id") || "";
					data.goods_images = goods_images;
					data.transport_title = $("input[name=freight]:checked").data("name");
					data.editorValue = ue.getContent();
					data.g_state = g_state;
					data.g_vat = 0;
					data.g_freight = 0;
					data.plate_top = 0;
					data.plate_bottom = 0;
					if($("input[name=iscustom]").length) data.iscustom = $("input[name=iscustom]:checked").val();
					if($("input[name=iscommend]").length) data.iscustom = $("input[name=iscommend]:checked").val();
					$(".js-specCheck:checked").each(function(){
						var key = $(this).attr("typename"),
							val = $(this).data("val");
						data[key] = val;
					});
                    var cur_action =  $("#cur_action").val();

					//添加体验店
					var brickstore=$("input[name='brickstore']:checked").val();
					if(brickstore){
						data.brickstore = brickstore;
					}                   
                    
					var pid = layer.load(360);
					if (g_state == 2) { //预览
						manage.ajax({
							url:  $("#apiUrlPreview").val(),
							data: data,
							async:false,
							success: function(result) {
								layer.close(pid);
								var resultText = result.resultText || {};
								if(result.code== 1){
									var url=$('#apiUrlPreview').val()+'?randkey='+resultText.data.randkey;
									window.open(url);
								}
							}
						});
					}else{
						manage.ajax({
							url:  $("#apiUrl").val(),
							data: data,
							success: function(result) {
								layer.close(pid);
								var resultText = result.resultText || {};
								if(result.code == 1){
									if(cur_action == "addGoodsStep2"){ //新增商品
										if (g_state == 0) { //保存到仓库
											layer.confirm('<div class="tc" style="width:350px;"><h3 class="fs16 cor-black">商品保存成功！</h3><p class="cor-9 mt10">3秒自动跳转到新增商品！</p></div>',
												{
													btn: ['继续新增商品', '仓库中的商品'] //按钮
												}, function () {
													location.reload();
												}, function () {
													window.location = gConfig.apiurl + "/StoreCommon/getGoodsList?type=inventory";
												}
											);
										}else{ //发布
											layer.confirm('<div class="tc" style="width:350px;"><h3 class="fs16 cor-black">商品保存并发布成功！</h3><p class="cor-9 mt10">3秒自动跳转到新增商品！</p></div>',
												{
													btn: ['继续新增商品', '在售中的商品'] //按钮
												}, function () {
													location.reload();
												}, function () {
													window.location = gConfig.apiurl + "/StoreCommon/getGoodsList?type=sale";
												}
											);
										}
										setTimeout(function () {
											layer.closeAll('dialog');
											location.reload();
										}, 5000);
									}else{ //编辑商品
										//$.wrongMsg(resultText.message || "编辑成功" , 1);
										if (g_state == 0) { //保存到仓库
											layer.confirm('<div class="tc" style="width:350px;"><h3 class="fs16 cor-black">商品编辑成功！</h3><p class="cor-9 mt10">3秒自动关闭窗口！</p></div>',
												{
													btn: ['关闭窗口', '仓库中的商品'] //按钮
												}, function () {
													window.close();
												}, function () {
													window.location = gConfig.apiurl + "/StoreCommon/getGoodsList?type=inventory";
												}
											);
										}else{ //发布
											layer.confirm('<div class="tc" style="width:350px;"><h3 class="fs16 cor-black">商品编辑并发布成功！</h3><p class="cor-9 mt10">3秒自动关闭窗口！</p></div>',
												{
													btn: ['关闭窗口', '在售中的商品'] //按钮
												}, function () {
													window.close();
												}, function () {
													window.location = gConfig.apiurl + "/StoreCommon/getGoodsList?type=sale";
												}
											);
										}
										setTimeout(function () {
											window.close();
										}, 5000);
									}
								}else{
									$.wrongMsg(resultText.message || "操作失败");
								}
							}
						});
					}
				});
			},
			/*
			 * 商品属性下拉框
			 * */
			attr_selected: function(obj) {
				var id = $("option:selected", obj).data('id'),
					name = obj.data('name').replace(/__NC__/g, id);
				obj.attr('name', name);
			},
			/*
			 * 交易属性
			 * */
			trade_attr: function(){
				var spid="", name_arr = [];
				$(".js-specCheck:checked").closest(".js-checkLine").each(function(){
					var new_arr = [];
					if(spid != 1) {spid = $(this).data("spid")};
					$(".js-specCheck:checked", this).each(function(){
						var me =$(this),
							id = me.data("id"),
							val = me.data("val");
						if(name_arr.length){
							$.each(name_arr, function(i, dom){
								new_arr.push({
									spid: spid,
									text: dom.text + '* <span class="js-specText"><input type="hidden" value="' + val + '" /><em data-id="' + id + '">' + val + '</em></span> ',
									newid: dom.newid + id
								});
							});
						}else{
							new_arr.push({
								spid: spid,
								text: ' <span class="js-specText"><input type="hidden" value="' + val + '" /><em data-id="' + id + '">' + val + '</em></span> ',
								newid: "i_" + id
							});
						}
					});
					name_arr = new_arr;
				});
				if(name_arr.length > 0){
					var tr_html = [];
					$.each(name_arr, function(i, item){
						var spid = item.spid || "",
							text = item.text || "",
							newid = item.newid || "",
							tr_h = '<tr data-id="' + newid + '">';
						if (spid == 1) {
							tr_h += '<input type="hidden" name="spec[' + newid + '][color]" class="js-trInp" value="" />';
						}
						tr_h += '<td>' + text + '</td>';
						tr_h += '<td><input class="text js-money js-required js-specPrice" type="text" name="spec[' + newid + '][price]" maxlength="15" /></td>';
						tr_h += '<td><input class="text js-number js-required js-specStorage" type="text" name="spec[' + newid + '][sku]" maxlength="10" /></td>';
						tr_h += '</tr>';
						tr_html.push(tr_h);
					});
					$(".js-specTable tbody").html(tr_html.sort().join(""));
					$(".js-specTable").show();
					$(".js-specTable  .js-specText").each(function(){
						var me = $(this),
							$tr = me.closest("tr"),
							newid = $tr.data("id"),
							spec_id = $("em", me).data("id");
						$("input", me).attr("name", "spec[" + newid + "][sp_value][" + spec_id + "]");
					});
					$(".js-specTable  .js-trInp").each(function(){
						var me = $(this),
							$tr = me.closest("tr"),
							spec_id = $("td:eq(0) span:eq(0) em", $tr ).data("id");
						me.val(spec_id);
					});
					$("#gprice").add("#gstorage").attr("readonly", "readonly").removeAttr("required").val("");
					$("#gprice").siblings(".error-msg").hide();
					$("#gstorage").siblings(".error-msg").hide();
				}else{
					$(".js-specTable").hide();
					$(".js-specTable tbody").empty();
					$("#gprice").add("#gstorage").attr("required", "required").removeAttr("readonly").val("");
				}
				form = $("#form").form();
			},
			/*
			 * 交易属性-价格最小值
			 * */
			price_least: function(){
				var price_arr = [];
				$(".js-specTable tbody tr").each(function(){
					var specPrice = $(".js-specPrice", this).val();
					if(!specPrice) specPrice = 0;
					price_arr.push(specPrice)
				});
				var min_price = Math.min.apply(Math, price_arr)
				$("#gprice").val(Math.floor(min_price*100)/100);
			},
			/*
			 * 交易属性-库存计算
			 * */
			storages_count: function(){
				var s_count = 0;
				$(".js-specTable tbody tr").each(function(){
					var specStorage = $(".js-specStorage", this).val();
					if(!specStorage) specStorage = 0;
					s_count += parseFloat(specStorage);
				});
				$("#gstorage").val(s_count);
			}
		}
		module.exports = Module;
	});
	seajs.use("s/goodsadd", function(module){
		module.init();
	});
})($);