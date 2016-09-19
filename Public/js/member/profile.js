//我的资料
//date: 2016.06.03
//author zhouquan
(function($){
	define("m/profile",function(require, exports, module){
		require("jquery/layer");
		var upload = require("jquery/upload_cropper");
		var manage = require("com/manage");
		var Module = {
			init: function(){
				upload.base({
					storage: "avatar"
				});
				/*
				 * 修改密码
				 * */
				$("#container").on("click", ".js-modifyPassword", function(){
					layer.open({
						type: 2,
						title:"修改密码",
						area: ['550px', '320px'],
						content: gConfig.apiurl + "/shop/member/tplchangepwd"
					});
				});
				/*
				 * 验证QQ 
				 **/
				 $("#memberQQ").on("blur",function(){
				 	var re=/^[0-9]*$/;//只能输入数字
				 	if(!re.test($(this).val())){
						$("#memberQQ").val("");
				 	}
				 })
				/*
				 * 保存
				 * */
				$("#container").on("click", ".js-saveBtn", function(){
					var avatar = $(".js-avatar").attr("src"),
						username = $.trim($("#nickname").val()),
						data = $("#form").serialize();
					data = manage.serializeJson(data);
					if(!username) username = $(".js-account").text()
					data.member_sex = $("input[name=member_sex]:checked").val();
					manage.getApi("/index.php?&c=member&a=profile", data, function(){
						$(".side-bar .js-userava").attr("src", avatar);
						$(".side-bar .js-username").text(username);
					});
				});
			}
		}
		module.exports = Module;
	});
	seajs.use("m/profile", function(module){
		module.init();
	});
})($);