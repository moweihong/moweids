//收货地址
//date: 2016.06.02
//author zhouquan
(function($){
	define("m/address",function(require, exports, module){
		require("jquery/layer");
		require("jquery/form");
		require("com/global");
		var manage = require("com/manage");
		var Module = {
			init: function(){
				if($("#cmbCity").length){
					seajs.use("com/adarray", function() {
						addressInit("cmbProvince", "cmbCity", "cmbArea");
					});
				}
				var form = $("#form").form();
				/*
				* 新增地址
				* */
				$("#form").on("click", ".js-saveBtn", function(){
					if(!form.valid()) return false; 
					var me = $(this),
						$form = me.closest("form"),
						$tbody = $("#container tbody");
					Module.sendData($form, "/index.php?c=member&a=save_address", function(address_id, data){
						if(data.is_default == 1){ //默认
							var $a = '<a href="javascript:void(0);" class="link js-del">删除</a> -<a href="javascript:void(0);" class="link js-edit">修改</a> -<a href="javascript:void(0);" class="link ">默认地址</a>';
							Module.addSetText();
						}else{
							var $a = '<a href="javascript:void(0);" class="link js-del">删除</a> -<a href="javascript:void(0);" class="link js-edit">修改</a> -<a href="javascript:void(0);" class="link js-set">设为默认</a>';
						}
						var $tr = '<tr data-id="' + data.address_id + '">\
										<td>' + data.true_name + '</td>\
										<td>' + data.area_info + '</td>\
										<td>' + data.address + '</td>\
										<td>' + data.zip_code + '</td>\
										<td>' +data.mob_phone + '</td>\
										<td>' + $a + '</td>\
									</tr>';
						$tbody.prepend($tr);
						$("#form")[0].reset();
					});
				});
				/*
				 * 修改地址
				 * */
				$("#container").on("click", ".js-edit", function(){
					var me = $(this),
						$tr = me.closest("tr"),
						id = $tr.data("id");
					layer.open({
						type: 2,
						title:"编辑收货地址",
						area: ['550px', '550px'],
						content: gConfig.apiurl + "/index.php?m=shop&c=member&a=tplchangeaddress&address_id="+ id,
					});
				});
				/*
				 * 设为默认
				 * */
				$("#container").on("click", ".js-set", function(){
					var me = $(this),
						$tr = me.closest("tr"),
						id = $tr.data("id");
					if(me.hasClass("disable")) return;
					manage.getApi("/index.php?c=member&a=default_address", {
						address_id: id
					}, function(){
						Module.addSetText();
						me.text("默认地址").addClass("disable").removeClass("cor-blue");
					});
				});
			},
			/*
			 api新增&编辑接口
			 * */
			sendData: function(obj, api, callback){
				if($("select[name=area_id]",obj).val() == 0){
					$("select[name=area_id]").focus();
					$.wrongMsg("请选择所在地区");
					return;
				}
				if(!$.trim($("textarea[name=address]", obj).val())){
					$("textarea[name=address]").focus();
					$.wrongMsg("请输入详细地址");
					return;
				}
				var province = $("select[name=province_id] option:selected", obj).text(),
					city = $("select[name=city_id] option:selected", obj).text(),
					area = $("select[name=area_id] option:selected",obj).text(),
					area_info = province + city + area,
					address = $.trim($("textarea[name=address]",obj).val()),
					true_name = $.trim($("input[name=true_name]", obj).val()),
					zip_code = $.trim($("input[name=zip_code]", obj).val()),
					mob_phone = $.trim($("input[name=mob_phone]", obj).val()),
					phone_codes = $.trim($(".js-codes", obj).val()),
					phone_num = $.trim($(".js-phone", obj).val()),
					data = obj.serialize();
				data = data.replace(/\+/g," ");
				data = manage.serializeJson(data);
				if($(".js-isDefault", obj).is(":checked")){
					var is_default = 1;
				}else{
					var is_default = 0;
				}
				data.area_info = area_info;
				data.tel_phone = phone_codes + "-" + phone_num;
				data.is_default = is_default;
				var pid = parent.layer.load(360);
				manage.ajax({
					url: gConfig.apiurl + api,
					data: data,
					success: function(result){
						parent.layer.close(pid);
						var resultText = result.resultText || {};
						if(result.code == 1){
							var address_id = resultText.address_id || "";
							data.address_id = address_id;
							$.wrongMsg(resultText.message || "操作成功", 1);
							callback && callback(address_id, data);
						}else{
							$.wrongMsg(resultText.message || "操作失败");
						}
					}
				});
			},
			/*
			 * 增加“设为默认”
			 * */
			addSetText: function(){
				parent.$("#container tbody tr").each(function(){
					$(".js-set", this).text("设为默认").addClass("cor-blue").removeClass("disable");
				});
			}
		}
		module.exports = Module;
	});
	seajs.use("m/address", function(module){
		module.init();
	});
})($);