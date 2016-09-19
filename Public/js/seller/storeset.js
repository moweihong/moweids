//店铺设置
//date: 2016.06.04
//author zhouquan
(function($){
	define("s/storeset",function(require, exports, module){
		require("jquery/layer");
		require("jquery/form");
		var upload = require("jquery/upload");
		var manage = require("com/manage");
		var Module = {
			init: function(){
				var formBaise = $("#formBaise").form(),
					formContact = $("#formContact").form(),
					formRefund = $("#formRefund").form();
				/*
				 * 地图搜索
				 * */
				var map = new BMap.Map("map"), //在指定的容器内创建地图实例
					lng = "",
					lat = "";
				map.setDefaultCursor("crosshair");//设置地图默认的鼠标指针样式
				map.enableScrollWheelZoom();//启用滚轮放大缩小，默认禁用。
				map.centerAndZoom(new BMap.Point(lng, lat), 10);
				map.addControl(new BMap.NavigationControl());
				function iploac(result){
					var cityName = result.name;
					map.setCenter(cityName);
				}
				var myCity = new BMap.LocalCity();
				myCity.get(iploac);
				$("#container").on("click", ".js-findBtn", function(){
					var province = $("#cmbProvince option:selected").text(),
						city = $("#cmbCity option:selected").text(),
						area = $("#cmbArea option:selected").text(),
						where = $.trim($("#where").val());
					if(!where){
						$.wrongMsg("请输入详细地址");
						return;
					}
					if($("#cmbArea").val() == 0){
						var address = where;
					}else{
						var address = province + where;
					}
					var local = new BMap.LocalSearch(map, {
						renderOptions:{map: map}
					});
					local.search(address);
				});
				if($("#where").val()){
					setTimeout(function(){
						$(".js-findBtn").trigger("click");
					},2000)
				}
				/*
				 * 城市三级联动
				 * */
				seajs.use("com/adarray", function() {
					var infoProvince = $("#infoProvince").val() || "",
						infoCity = $("#infoCity").val() || "",
						infoArea = $("#infoArea").val() || "",
						infoProvince2 = $("#infoProvince2").val() || "",
						infoCity2 = $("#infoCity2").val() || "",
						infoArea2 = $("#infoArea2").val() || "";
					$("#cmbArea").length && addressInit("cmbProvince", "cmbCity", "cmbArea", infoProvince, infoCity, infoArea);
					$("#cmbArea2").length && addressInit("cmbProvince2", "cmbCity2", "cmbArea2", infoProvince2, infoCity2, infoArea2);
				});
				/*
				 * 图片上传
				 * */
				if($("#com_type").val() != 2){
					var thumbnailWidth = 200,
						thumbnailHeight = 80;
				}else{
					var thumbnailWidth = 238,
						thumbnailHeight = 238;
				}
				upload.base({
					storage: "store",
					isSingle: true,
					fileSingleSizeLimit: 0.2,
					thumbnailWidth: thumbnailWidth,
					thumbnailHeight: thumbnailHeight
				});
				/*
				 * tab切换
				 * */
				$(".js-tabTit").on("click", "a", function(){
					layer.closeAll();
					var me = $(this),
						n = me.index();
					me.addClass("curr").siblings("a").removeClass("curr");
					$(".js-tabCont:eq(" + n + ")").show().siblings(".js-tabCont").hide();
				});
				var num = location.hash.replace(/^\s*#\s*/,"") || 0;
				$(".js-tabTit a:eq(" + num + ")").trigger("click");
				/*
				 * 验证店铺名称
				 * */
				$("#storeName").on({
					"focus": function() {
						layer.closeAll('tips');
					},
					"blur": function() {
						var me = $(this),
							store_name = $.trim(me.val());
						manage.ajax({
							url: gConfig.apiurl + "/StoreCommon/checkStorename",
							data: {
								store_name: store_name
							},
							success: function (result) {
								var resultText = result.resultText || {};
								if (result.code != 1) {
									$.wrongTip(me, resultText.message || "该店铺名称已存在", "", 9999999);
								}
							}
						});
					}
				});
				/*
				 * 保存基本信息
				 * */
				$("#container").on("click", ".js-saveBasic", function(){
					if(!formBaise.valid()) return false;
					var store_logo = $("#fileList img").attr("src") || "",
						storeName = $.trim($("#storeName").val()) || 30,
						data = $("#formBaise").serialize();
					data = manage.serializeJson(data);
					if(storeName.length < 2 || storeName.length > 30){
						$.wrongMsg("店铺名称长度2到30个字");
						return;
					}
					if($("#fileList .file-item").length < 1){
						$.wrongMsg("请上传店铺logo图片");
						return;
					}
					if(!store_logo){
						$.wrongMsg("店铺logo图片上传失败");
						return;
					}
					data.store_logo = store_logo;
					manage.getApi("/StoreCommon/storeSettingAjax?type=base", data, function(result){
						 if(result.data.is_update){  //true店铺有修改
							$("#storeName").closest("div.rel").replaceWith(storeName);
						 }
					});
				});
				/*
				 * 联系我们
				 * */
				$("#container").on("click", ".js-saveContact", function(){
					if(!formContact.valid()) return false;
					if(!$.isTelephone($.trim($("#tel").val()))){
						$.wrongMsg("请填写正确的手机号码");
						return;
					}
					if($("#cmbArea").val() == 0 || !$.trim($("input[name=store_address]").val())){
						$.wrongMsg("请填写完整的联系地址");
						return;
					}
					Module.sendData($("#formContact"), "/StoreCommon/storeSettingAjax?type=connect");
				});
				/*
				 * 在线客服
				 * */
				$("#container").on("click", ".js-saveService", function() {
					var service_num = 0;
					$(".js-serviceSet").each(function(){
						var serviceName = $.trim($(".js-serviceName", this).val()),
							serviceTools = $.trim($(".js-serviceTools", this).val());
						if(serviceName && serviceTools) service_num++
					});
					if(service_num == 0){
						$.wrongMsg("请至少设置一个在线客服");
						return;
					}
					var data = $("#formService").serialize();
					manage.getApi("/StoreCommon/storeSettingAjax?type=service", data);
				});
				/*
				 * 退货设置
				 * */
				$("#container").on("click", ".js-saveRefund", function(){
					if(!formRefund.valid()) return false;
					if(!$.trim($("#formRefund .js-phone").val())){
						$.wrongMsg("请填写联系电话");
						return;
					}
					if($("#cmbArea2").val() == 0){
						$.wrongMsg("请选择联系地址");
						return;
					}
					Module.sendData($("#formRefund"), "/StoreCommon/storeSettingAjax?type=refund", "refund");
				});
			},
			/*
			 * 发送数据--联系我们&退货设置
			 * */
			sendData: function($form, api, type){
				var type = type || "",
					phone_codes = $.trim($(".js-codes", $form).val()),
					phone_num = $.trim($(".js-phone", $form).val()),
					province = $(".js-province option:selected", $form).text(),
					city = $(".js-city option:selected", $form).text(),
					area = $(".js-area option:selected", $form).text(),
					data = $form.serialize();
				data = manage.serializeJson(data);
				if(type == "refund"){ //退货设置
					data.refund_tel = phone_codes + "-" + phone_num;
					data.refund_area_info = province + city + area;
				}else{
					data.store_tel = phone_codes + "-" + phone_num;
					data.store_area_info = province + city + area;
				}
				manage.getApi(api, data);
			}
		}
		module.exports = Module;
	});
	seajs.use("s/storeset", function(module){
		module.init();
	});
})($);