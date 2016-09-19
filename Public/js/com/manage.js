/**
*网站共用的基础方法
*@author zhouquan
*/
(function($){
	Date.prototype.format = function (fmt) { //author: meizz 
		fmt = fmt || "";
		fmt = fmt.replace(/Y/ig,"y").replace(/H/ig,"h").replace(/D/ig,"d").replace(/S/ig,"s");
	    var o = {
	        "M+": this.getMonth() + 1, //月份 
	        "d+": this.getDate(), //日 
	        "h+": this.getHours(), //小时 
	        "m+": this.getMinutes(), //分 
	        "s+": this.getSeconds(), //秒 
	        "q+": Math.floor((this.getMonth() + 3)/3), //季度 
	        "S": this.getMilliseconds() //毫秒 
	    };
	    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
	    for (var k in o)
	    if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
	    return fmt;
	};
	var timeer_interval = null
	var Module = {
		/**
		* 封装ajax请求
		*/
		ajax: function(param) {
			var funError = param.error ;
			param.dataType = param.dataType || "json";
			param.type = param.type || 'POST';
			param.timeout = param.timeout || gConfig.timeout * 3000;
			param.error = function(e){
				parent.layer.closeAll('loading');
				//console.error('[ERROR]['+param.url+']:', e.stack, e);
				setTimeout(function(){
					parent.layer.msg("网络异常", {
						icon: 5
					});
				}, 500);
				if(funError){
					funError(e);
				}else{
					//param.success && param.success({code:1, msg:"请求信息异常",error: e});
				}
			};
			$.ajax(param);
		},
		/**
		 * 常用api接口
		 */
		getApi: function(api, data, callback){
			var pid = parent.layer.load(360);
			Module.ajax({
				url: gConfig.apiurl + api,
				data: data,
				success: function(result){
					parent.layer.close(pid);
					var resultText = result.resultText || {};
					//code = 1 是正常
					//code = 0 是失败
					//code = -404 java接口没有响应
					//code = -500 JAVA接口异常
					if(result.code == 1){
						$.wrongMsg(resultText.message || "操作成功", 1);
						callback && callback(resultText);
					}else if(result.code == 0){
						//失败
						$.wrongMsg(resultText.message || "操作失败");
					}else if(result.code == -404){
						//java接口没有响应
						$.wrongMsg(resultText.message || resultText);
					}else if(result.code == -500){
						//java接口异常
						$.wrongMsg(resultText.message || resultText);
					}else{
						$.wrongMsg(resultText.message || "操作失败");						
					}
				}
			});
		},
		/**
		*	把url序列化为对象
		*	如: name=zhouquan&age=22&sex=1 => {name:"zhouquan", age: 22, sex: 1};
		*/
		serializeJson: function(search) {
			search = (search || "").replace(/^\s*\?/, "");
			var arrs = search.split("&");
			var result = {};
			for (var i in arrs) {
				var vals = arrs[i].split("=");
				result[decodeURIComponent(vals[0])] = decodeURIComponent(vals[1]);
			}
			return result;
		},
		getParam: function(){
			var search = location.search.replace(/^\s*\?/, "");
			return Module.serializeJson(search);
		},
		/**
		* 格式化日期
		*/
		formatDate: function(value, format){
			var date = value;
			if(!(value instanceof Date)){
				date = new Date(date);
			}
			return date.format("yyyy-MM-dd hh:mm:ss");
		},
		/** 
		* 实现setTimeout的功能
		*/
		cycle: function(fun, cycleTime){
			(function handle(){
				setTimeout(function(){
					fun();
					handle();
				}, cycleTime)
			})();
		},
		/** 
		* 验证码倒计时
		*/
		countdown: function(url, data, obj, count){
			if(me.hasClass("btn-disable")) return;
			var countdown = count,
				pid = parent.layer.load(360);
			$.post(gConfig.apiurl + url, data, function (result){
				parent.layer.close(pid);
				clearInterval(timeer_interval);
				timeer_interval = setInterval(function () {
					if(countdown == 0) {
						obj.text("重新发送").removeClass("btn-disable");
						countdown = 60;
						clearInterval(timeer_interval);
					}else{
						obj.text("重新发送(" + countdown + "s)").addClass("btn-disable");
						countdown--;
					}
				}, 1000);
			});
		}
	};
	/** 
	* 错误提示
	*/
	$.wrongMsg = function(str, icon, time){
		var str = str || "操作失败",
			icon = icon || 2,
			time = time || 1500;
		parent.layer.msg(str, {
			icon: icon,
			time: time
		});
	};
	$.wrongTip = function(obj, str, icon, time){
		var str = str || "操作失败",
			icon = icon || [1, "#ff9900"],
			time = time || 2000;
		layer.tips(str, obj, {
			tips: icon,
			time: time,
			maxWidth: 250
		});
	};
	/** 
	* 调用css
	*/
	$.getCss = function(url){
		var node = document.createElement("link");
		node.href = url;
		node.async = "async";
		node.rel = "stylesheet";
		node.text = "text/css";
		document.body.appendChild(node);
		return node;
	};
	/** 
	* 点击关闭浮动层
	*/
	$.clickHide = function(obj){
		$(document).on("click.clickHide",function(e){
			if($(e.target).closest(obj).length > 0) return;
			$(obj).fadeOut("fast");
			$(document).off(".clickHide");
		});
	};
	/** 
	*价格 只能输入数字和点
	*/
	$("body").on("keyup", "input.js-money", function(){
		var val = $(this).val(),
			maxNum = parseFloat($(this).data("max")) || "";
		if(maxNum && parseFloat(val) > maxNum){
			$(this).val(val.substr(0,val.length-1));
			return;
		}
		if(val.indexOf(".")>=0) val = val.substring(0,val.indexOf(".") + 3);
		$(this).val(val.replace(/[^\d.]/g,""));
		if(maxNum && parseFloat(val) > maxNum) $(this).val(val.substr(0,val.length-1));
	});
	/**
	 *联系电话
	 */
	$("body").on("keyup", "input.js-homephone", function(){
		var val = $(this).val(),
			maxNum = parseFloat($(this).data("max")) || "";
		if(maxNum && parseFloat(val) > maxNum){
			$(this).val(val.substr(0,val.length-1));
			return;
		}
		$(this).val(val.replace(/[^\d-]/g,""));
		if(maxNum && parseFloat(val) > maxNum) $(this).val(val.substr(0,val.length-1));
	});
	/** 
	*只能数字整数
	*/
	$("body").on("keyup", "input.js-number, textarea.js-number", function(){
		var val = $(this).val(),
			maxNum = parseInt($(this).data("max")) || "";
		if(maxNum && parseInt(val) > maxNum){
			$(this).val(val.substr(0,val.length-1));
			return;
		}
		$(this).val(val.replace(/[^\d]/g,""));
	});
	/**
	 *只能数字或字母
	 */
	$("body").on("keyup", "input.js-numletter", function(){
		var val = $(this).val(),
			reg = /^[A-Za-z0-9]*$/;
		if(!reg.test(val)) {
			$(this).val(val.substr(0,val.length-1).replace(/[\u4e00-\u9fa5]/g,""));
			return;
		}
	});
	/**
	 * 不能输入特殊符号
	 */
	$("body").on("keyup","input.js-teshu",function(){
		var val = $(this).val();
		    reg = new RegExp("[`~!@#$^&%*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]")
		if(reg.test(val)){
			$(this).val(val.replace(reg,""));
			return;
		}
	})
	/** 
	*手机号码
	*/
	$.isTelephone = function(str){
		var reg = /^0?1[0-9]\d{9}$/;
		if(reg.test(str)){
			return true;
		}
		return false;	
	}
	/**
	/*智能浮动定位*
	**/
	$.fn.smartFloat = function() {
		var position = function(element) {
			var top = element.offset().top;
			$(window).scroll(function() {
				var scrolls = $(this).scrollTop();
				if (scrolls > top) {
					element.addClass("smart-float");
					function refreshSize(){
						var $width = element.closest("div").width();
						element.css("width",$width);
					}
					refreshSize();
					window.onresize = refreshSize;
				}else {
					element.removeClass("smart-float");  
				}
			});
		};
		return $(this).each(function() {
			position($(this));                         
		});
	};
	/**
	/*删除数组里的某个元素
	调用：var emp = ['abs','dsf','sdf','fd'];$.removeArr(emp, "fd");
	**/
	$.removeArr = function(arrs, val) {
		var index = arrs.indexOf(val);
		if(index > -1){
			arrs.splice(index, 1);
		}
	};
	/**
	/*时间戳*
	调用：getTimeFormat("2015-06-28 23:59:59"), 显示1435507199000
	**/
    getTimeFormat = function(day){
        var re = /(\d{4})(?:-(\d{1,2})(?:-(\d{1,2}))?)?(?:\s+(\d{1,2}):(\d{1,2}):(\d{1,2}))?/.exec(day);
        return new Date(re[1],(re[2]||1)-1,re[3]||1,re[4]||0,re[5]||0,re[6]||0).getTime();
    };
	/**
	 /*查看物流浮屠*
	 **/
	var time_delay = null;
	$(".js-shipping").live({
		mouseenter: function() {
			var me = $(this),
				order_sn = me.data("sn") || "",
				h = me.innerHeight();
			clearTimeout(time_delay);
			$(".shipping-float").hide();
			if(me.siblings(".shipping-float").length){
				me.siblings(".shipping-float").show();
			}else{
				var $float = '<div class="shipping-float js-shippingFloat"><i class="san"></i></div>';
				me.after($float);
				me.siblings(".js-shippingFloat").css("top",
					h + 5).append('<p class="tc mt40 js-load"><i class="loading-ic"></i> 加载中...</p>');
				$.post(gConfig.apiurl + "/index.php?act=ajax_api&op=getExpress", {
						order_sn: order_sn
					},function(result){
						me.siblings(".js-shippingFloat").find(".js-load").remove();
						var resultText = result.resultText || {},
							message = resultText.message || "获取数据失败",
							data = resultText.data || {},
							info = data.info || {},
							name = info.name || "暂无信息",
							code = info.code || "暂无信息",
							$h = '<div class="h"><p>物流：' + name + '</p><p>运单号：' + code + '</p></div>',
							$c = ['<div class="c">'];
						if(result.code == 1){
							var list = data.list || [];
							if(list.length < 1){
								me.siblings(".js-shippingFloat").append('<p class="tc mt40">' + message + '</p>');
							}else{
								$.each(list, function (i, item) {
									var time = item.time || 0,
										text = item.text || "";
									$p = '<p class="cf"><span class="time">' + time + '</span>' + text + '</p>';
									$c.push($p);
								});
								$c.push('</div>');
								me.siblings(".js-shippingFloat").append($h + $c.join(""));
							}
						}else if(result.code == 401){
							me.siblings(".js-shippingFloat").append('<p class="tc mt40">暂无物流信息</p>');
						}else{
							me.siblings(".js-shippingFloat").append('<p class="tc mt40">' + message + '</p>');
						}
					},
					"json"
				);
			}
		},
		mouseleave: function () {
			var me = $(this);
			clearTimeout(time_delay);
			time_delay = setTimeout(function () {
				me.siblings(".shipping-float").hide();
			}, 200);
		}
	});
	$(".js-shippingFloat").live({
		mouseenter: function(){
			clearTimeout(time_delay);
		},
		mouseleave: function(){
			$(this).hide();
		}
	});
	define("com/manage", function(require, exports, module) {
		module.exports = Module;
	});
})(jQuery);