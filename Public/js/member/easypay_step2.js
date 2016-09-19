//授信资料-提交申请资料
//date: 2016.06.07
//author zhouquan
(function($){
    define("m/easypay_step2",function(require, exports, module){
        require("jquery/layer");
		require("jquery/laydate");
        require("jquery/form");
        var upload = require("jquery/upload");
        var manage = require("com/manage");
        var Module = {
            init: function(){
                var form = $("#form").form();
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
					addressInit("cmbProvince1", "cmbCity1", "cmbArea1", infoProvince, infoCity, infoArea);
					addressInit("cmbProvince2", "cmbCity2", "cmbArea2", infoProvince2, infoCity2, infoArea2);
                });
                /*
                 * 身份证正面照
                 * */
                upload.base({
                    pickID: "cardFrontalBtn",
                    objID: "cardFrontalList",
                    isSingle: true,
					fileSingleSizeLimit: "1000",
                    thumbnailWidth: "180",
                    thumbnailHeight: "115",
                    storage: "easypay"
                });
                /*
                 * 身份证反面照
                 * */
                upload.base({
                    pickID: "cardReverseBtn",
                    objID: "cardReverseList",
                    isSingle: true,
					fileSingleSizeLimit: "1000",
                    thumbnailWidth: "180",
                    thumbnailHeight: "115",
                    storage: "easypay"
                });
                /*
                 * 其他增信资料
                 * */
                upload.base({
                    pickID: "augmentedBtn",
                    objID: "augmentedList",
					fileSingleSizeLimit: "1000",
                    thumbnailWidth: "180",
                    thumbnailHeight: "115",
                    fileNumLimit: "300",
                    storage: "easypay"
                });
                /*
                 *姓名只能输入中文
                 **/ 
                $('.js-usrname').blur(function(){
                	var reg = /^[\u4e00-\u9fa5]+$/;
                		me = $(this).val();
                	if(!reg.test(me)){
                		$(this).siblings(".error-msg").show();
                		return;
                	}else{
						$(this).siblings(".error-msg").hide();
					}
                })
				/*
                 * 下拉框必选框
                 * */
				$(".js-requiredSel").on({
					 "change": function(){
						if($(this).val() == 0){
							$(this).siblings(".error-msg").show();
						}else{
							$(this).siblings(".error-msg").hide();
						}
					}
				});
				/*
				 * 日期失去焦点
				 * */
				$(".js-laydate").blur(function(){
					$(this).siblings(".error-msg").hide();
				});
				 /*
                 * 联系人电话
                 * */
				$(".js-contact").on("blur", ".js-phone", function(){
					var val = $(this).val();
					if($.isTelephone(val)){
						 $(this).siblings(".error-msg").hide();
					}else{
						 $(this).siblings(".error-msg").show();
					}
				 });
				/*
				 * 弹窗-上传图片资料指引
				 * */
				$(".js-uploadGuide").click(function(){
					layer.open({
						type: 1,
						shade: false,
						title: false,
						area: ['800px', '460px'],
						content: $('.js-guidePop')
					});
				});
				/*
				 * 勾选确认所填资料
				 * */
				$("body").on("click", ".js-isAffirm", function(){
					if($(this).is(":checked")){
						$(".js-nextBtn").removeClass("btn-disable").addClass("btn-info");
					}else{
						$(".js-nextBtn").removeClass("btn-info").addClass("btn-disable");
					}
				});
				/*
                 * 下一步
                 * */
                $("body").on("click", ".js-nextBtn", function(){
                    var me = $(this),
						isPass = true,
						bondsmaninf_list = [],
						id_card_front_pic_s = $("#cardFrontalList img").attr("src") || "",
						id_card_front_pic_m = $("#cardFrontalList img").data("rel") || "",
						id_card_reverse_pic_s = $("#cardReverseList img").attr("src") || "",
						id_card_reverse_pic_m = $("#cardReverseList img").data("rel") || "",
						other_pic_list = [];
                    if(!form.valid()){
						if($(".js-isAffirm").is(":checked")){
							$.wrongMsg("必填项不能为空");
						}
						return false;
					}
                    var myusername = $("#username").val();
                    if(Module.strlen(myusername) > 5){
                    	$("#username").val("");
                    	$("#username").focus();
						$.wrongMsg("姓名长度不合法");
						isPass = false;
						return;	
					}
					if(!$("input[name=sex]:checked").val()){
                        $("input[name=sex]").focus();
                        $.wrongMsg("请选择性别");
                        return;
                    }
					if(!$("input[name=marital]:checked").val()){
                        $("input[name=marital]").focus();
                        $.wrongMsg("请选择婚姻状态");
                        return;
                    }
                    if($("#idCard").val().length < 15){
                        $("#idCard").focus();
                        $.wrongMsg("请输入正确的身份证号码");
                        return;
                    }
					$(".js-requiredSel").each(function(){
						if($(this).val() == 0){
							$(this).siblings(".error-msg").show();
						}
					});
                    if($("#cmbCity1").val() == 0){
                        $("#cmbCity1").focus();
                        $.wrongMsg("请选择完整户籍");
                        return;
                    }
					if($("#cmbArea2").val() == 0){
                        $("#cmbArea2").focus();
                        $.wrongMsg("请选择完整现住址");
                        return;
                    }
					if($("select[name=diploma]").val() == 0){
                        $("select[name=diploma]").focus();
                        $.wrongMsg("请选择学历");
                        return;
                    }
					$(".js-laydate").each(function(){
						var val = $(this).val();
						if(!val){
							$(this).siblings(".error-msg").show();
							$.wrongMsg("请选择时间");
							isPass = false;
						}
					});
					if(!isPass) return;
					$(".js-contact").each(function(){
						var rel_usrname = $.trim($(".js-name", this).val()),
							relation_id = $.trim($(".js-relation", this).val()),
							relation = $.trim($(".js-relation option:selected", this).text()),
							mobile_phone = $.trim($(".js-phone", this).val());
						if(!rel_usrname){
							$(".js-name", this).focus();
							$.wrongMsg("请输入联系人的姓名");
							isPass = false;
							return;	
						}
						
						if(relation_id == "请选择"){
							$(".js-relation", this).focus();
							$.wrongMsg("请选择与联系人的关系");
							isPass = false;
							return;	
						}
						if(!mobile_phone || !$.isTelephone(mobile_phone)){
							$(".js-phone", this).focus();
							$.wrongMsg("请输入正确的联系人手机号");
							isPass = false;
							return;	
						}
						var bondsmaninf_obj = {
							rel_usrname: rel_usrname,
							relation: relation,
							relation_id: relation_id,
							rel_mobile_phone: mobile_phone
						}
						bondsmaninf_list.push(bondsmaninf_obj);
					});
					if(!id_card_front_pic_s || !id_card_front_pic_m){
						$.wrongMsg("请上传身份证正面照");
						return;	
					}
					if(!id_card_reverse_pic_s || !id_card_reverse_pic_m){
						$.wrongMsg("请上传身份证反面照");
						return;	
					}
					var id_card_front_pic = {
						s_pic: id_card_front_pic_s,
						m_pic: id_card_front_pic_m
					};
					var id_card_reverse_pic = {
						s_pic: id_card_reverse_pic_s,
						m_pic: id_card_reverse_pic_m
					}
					$("#augmentedList .file-item").each(function(){
						var s_pic = $("img", this).attr("src") || "",
							m_pic = $("img", this).data("rel") || "";
						var other_pic_obj = {
							s_pic: s_pic,
							m_pic: m_pic
						}
						other_pic_list.push(other_pic_obj);
					});
					if(!$(".js-isAffirm").is(":checked")){
						$.wrongMsg("请勾选本人确认所填资料真实有效");
						return;
					}
					if(!isPass) return;
                    var data = $("#form").serialize();
                    data = manage.serializeJson(data);
					data.usr_native = $("#cmbProvince1 option:selected").text() + $("#cmbCity1 option:selected").text();
					data.add_areainfo = $("#cmbProvince2 option:selected").text() + $("#cmbCity2 option:selected").text() + $("#cmbArea2   option:selected").text();
                    data.bondsmaninf_list = JSON.stringify(bondsmaninf_list);
                    data.id_card_front_pic = JSON.stringify(id_card_front_pic);
                    data.id_card_reverse_pic = JSON.stringify(id_card_reverse_pic);
                    data.other_pic_list = JSON.stringify(other_pic_list);
					delete data.relation;
					manage.getApi("/easypay/save_application", data, function(result){
						var result = result || {},
							url = result.url || gConfig.apiurl;
						setTimeout(function(){
							location.href = url;
						},3000);
					});
                });
            },
			/**
			 返回包含中文字符的长度
			 **/
			strlen: function(str){
				var len = 0;
				if(!str) return len;
				for (var i=0; i<str.length; i++) {
					var c = str.charCodeAt(i);
					//单字节加1
					if ((c >= 0x0001 && c <= 0x007e) || (0xff60<=c && c<=0xff9f)) {
						len++;
					}else {
						len+=2;
					}
				}
				return len;
			}
        };
        module.exports = Module;
    });
    seajs.use("m/easypay_step2", function(module){
        module.init();
    });
})($);