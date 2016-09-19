(function($){
    define("s/startshop",function(require, exports, module){
        require("jquery/layer");
        require("jquery/form");
        require("jquery/laydate");
        var upload = require("jquery/upload");
        var manage = require("com/manage");
        var Module = {
            init: function(){
                /*第三步提交资料*/
                var form = $("#form").form();
                /*
                * 营业执照 
                **/
                 upload.base({
                    pickID: "businessLicense",
                    objID: "businessLicenseList",
                    isSingle: true,
					fileSingleSizeLimit: "1000",
                    thumbnailWidth: "103",
                    thumbnailHeight: "103",
                    storage: "store_joinin"
                });
                /*
                * 组织机构代码 
                **/
                 upload.base({
                    pickID: "OrganizationCode",
                    objID: "OrganizationCodeList",
                    isSingle: true,
					fileSingleSizeLimit: "1000",
                    thumbnailWidth: "103",
                    thumbnailHeight: "103",
                    storage:"store_joinin"
                });                
                /*
                * 身份证正面照
                **/
                upload.base({
                    pickID: "cardFrontalBtn",
                    objID: "cardFrontalList",
                    isSingle: true,
					fileSingleSizeLimit: "1000",
                    thumbnailWidth: "103",
                    thumbnailHeight: "103",
                    storage: "store_joinin"
                });
                /*
                 * 身份证反面照
                 * */
                upload.base({
                    pickID: "cardReverseBtn",
                    objID: "cardReverseList",
                    isSingle: true,
					fileSingleSizeLimit: "1000",
                    thumbnailWidth: "103",
                    thumbnailHeight: "103",
                    storage: "store_joinin"
                });
                 /*
                 * 城市三级联动
                 * */
                seajs.use("com/adarray", function() {
                    var infoProvince = $("#infoProvince").val() || "",
                        infoCity = $("#infoCity").val() || "",
                        infoArea = $("#infoArea").val() || "";
                    if($("#cmbArea").length) addressInit("cmbProvince", "cmbCity", "cmbArea", infoProvince, infoCity, infoArea);
                });
				/*
                 * 第一步阅读协议
                 * */
				$("#input_apply_agreement").change(function() {
                    if($(this).prop("checked")) {
                       $("#btn_apply_agreement_next").removeClass("btn-disable");
                    }else{
                        $("#btn_apply_agreement_next").addClass("btn-disable");
                    }
                });
                $("#btn_apply_agreement_next").click(function() {
                    if($("#input_apply_agreement").prop("checked")) {
                        window.location.href = "/startBusiness/step2";
                    } else {
                        $.wrongMsg('请阅读并同意协议');
                    }
                });
				/*
                 * 第二步选 公司类型
                 * */
                $(".js-comType").click(function(){
					var me = $(this),
						type = me.data("type");
					me.addClass("com-type-checked").siblings(".js-comType").removeClass("com-type-checked");
                    if(type == 1){ //家居经销商
                        $(".js-nextBtn").attr("href", gConfig.apiurl + "/startBusiness/enterorindiv");
                    }else if(type == 2){ //装修公司
                       $(".js-nextBtn").attr("href", gConfig.apiurl + "/startBusiness/step3?com_type=2&business_type=1");
                    }else{ //品牌家具厂
						 $(".js-nextBtn").attr("href", gConfig.apiurl + "/startBusiness/step3?com_type=3&business_type=1");
                    }
				});
				/*
                 * 第二步选择企业类型
                 * */
                $(".js-storeType").click(function(){
					var me = $(this),
						type = me.data("type");
					me.addClass("store-type-checked").siblings(".js-storeType").removeClass("store-type-checked");
                    if(type == 1){ //企业
                        $(".js-nextBtn").attr("href", gConfig.apiurl + "/startBusiness/step3?com_type=1&business_type=1");
                    }else{ //个体工商户
                        $(".js-nextBtn").attr("href", gConfig.apiurl + "/startBusiness/step3?com_type=1&business_type=2");
                    }
				});
                /**
                 * 身份证 // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X 
                 */
                $("body").on("blur", ".js-idcart", function(){
                    var val = $.trim($(this).val()),
                        reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
                    if(reg.test(val)){
                        $(this).siblings(".error-msg").hide();
                    }else{
                        $(this).siblings(".error-msg").show();
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
                $("body").on("click", ".js-submitBtn",function(){
                    var  id_card_front_pic_s = $("#cardFrontalList img").data("rel") || "",
                         id_card_reverse_pic_s = $("#cardReverseList img").data("rel") || "",
                         id_businessLicenseList = $('#businessLicenseList img').data("rel") || "",
                         id_OrganizationCodeList =$('#OrganizationCodeList img').data("rel") ||"",
                         idcart_number = $.trim($(".js-idcart").val()),
                         reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
                    if(!form.valid()){
                        $.wrongMsg("必填项不能为空");
                        return false;
                    }
                    if($("#cmbArea").val() == 0){
                        $("#cmbArea").focus();
                        $.wrongMsg("请选择完整现住址");
                        return true;
                    }
                    if(!reg.test(idcart_number)){
                        $("#idCard").focus();
                        return;
                    }
                    if(!id_card_front_pic_s){
                        $.wrongMsg("请上传身份证正面照");
                        return;
                    }
                    if(!id_card_reverse_pic_s){
                        $.wrongMsg("请上传身份证反面照");
                        return;
                    }
                    if(!id_businessLicenseList || !id_OrganizationCodeList){
                        if($('input[name="business_type"]').val() == 1){
                            $.wrongMsg("请上传图片");
                            return;
                        }
                    }
                    var data=$("#form").serialize();
                    data = manage.serializeJson(data); 
                    data.representive_id_front_electronic = id_card_front_pic_s;//身份证正
                    data.representive_id_back_electronic = id_card_reverse_pic_s;//身份证反
                    data.business_licence_number_electronic = id_businessLicenseList;//营业执照
                    data.organization_code_electronic = id_OrganizationCodeList;//组织机构证
                    data.company_address = $("#cmbProvince option:selected").text()+" "+$("#cmbCity option:selected").text()+" "+$("#cmbArea option:selected").text();//市//省
                   // data.company_city_id=$("#cmbCity option:selected").text();//市
                   // data.company_area_id=$("#cmbArea option:selected").text();//区
                    manage.getApi('/StartBusiness/saveProfile',data,function(result){
                        var result=result || {},
                            url=result.url|| gConfig.apiurl;
                        setTimeout(function(){
                            location.href = url;
                        },1000);
                    })
                });
            }
        };
        module.exports = Module;
    });
    seajs.use("s/startshop", function(module){
        module.init();
    });
})($);