//商品详情页
//date: 2016.08.20
//author zhouquan
(function($){
	define("web/goods",function(require, exports, module){
		require("jquery/layer");
		var manage = require("com/manage");
		var param = manage.getParam();
		var islogin = $("#islogin").val() || "", //登录状态
			goods_id = param.goods_id || $("#goods_id").val(), //商品ID
			goods_commonid =$("#goods_commonid").val() || "", //商品commonID
			store_id =$("#store_id").val() || "", //商铺ID
			quantity = parseInt($(".js-goodsStock").text()) || 0; //库存数
		var Module = {
			init: function(){
				if(quantity <= 1) $(".js-increase").addClass("disable");
				/*
				**图片放大
				*/
				var goods_image_arr = eval("(" + $("#goodsImage").val() + ")");
				seajs.use("jquery/imageZoom", function(){
					var zoomController,
						zoomControllerUl,
						zoomControllerUlLeft = 0,
						shell = $(".js-imageZoom"),
						shellPanel = shell.parent().hide(),
						heightOffset = 120,
						minGallerySize = [418, 418],
						imageZoom = new ImageZoom({
							shell: shell,
							basePath: '',
							levelASize: [70, 70],
							levelBSize: [418, 418],
							gallerySize: minGallerySize,
							onBeforeZoom: function (index, level) {
								if (!zoomController) {
									zoomController = shell.find('div.controller');
								}
								var self = this,
									duration = 320,
									width = minGallerySize[0],
									height = minGallerySize[1],
									zoomFx = function () {
										self.ops.gallerySize = [width, height];
										self.galleryPanel.stop().animate({width: width, height: height}, duration);
										shellPanel.stop().animate({height: height + heightOffset}, duration);
										zoomController.animate({width: width + 2}, duration);
										shell.stop().animate({width: width}, duration);
									};
								if (level !== this.level && this.level !== 0) {
									if (this.level === 1 && level > 1) {
										height = Math.max(520, shellPanel.height());
										width = shellPanel.width();
										zoomFx();
									}
									else if (level === 1) {
										zoomFx();
									}
								}
							},
							onZoom: function (index, level, prevIndex) {
								if (index !== prevIndex) {
									if (!zoomControllerUl) {
										zoomControllerUl = zoomController.find('ul').eq(0);
									}
									var width = 86,
										ops = this.ops,
										count = ops.items.length,
										panelVol = ~~((zoomController.width() + 10) / width),
										minLeft = width * (panelVol - count),
										left = Math.min(0, Math.max(minLeft, -width * ~~(index - panelVol / 2)));
									if (zoomControllerUlLeft !== left) {
										zoomControllerUl.stop().animate({left: left}, 320);
										zoomControllerUlLeft = left;
									}
								} 
								shell.find('a.prev,a.next')[level < 3 ? 'removeClass' : 'addClass']('hide');
								shell.find('a.close').css('display', [level > 1 ? 'block' : 'none']);
							},
							items: goods_image_arr
						});
					shell.data("imageZoom", imageZoom);
					shellPanel.show();
				});
				/*
				**收藏商品
				*/
				$(".js-favorite").click(function(){
					var favorite=$('#islogin').val()||" ";
					if(favorite == " "){
						login_dialog(); return false;
					}else{
						collect_goods(goods_id, "count", ".js-collectNum");
					} 
				});
				/*
				**商品购买数量增加
				*/
				$('.js-increase').click(function () {
					if($(this).hasClass("disable")) return false
					var num = parseInt($(".js-quantity").val());
					num++;
					$(".js-quantity").val(num);
					$(".js-decrease").removeClass("disable");
					if(num >= quantity) $(this).addClass("disable");
				});	
				/*
				**商品购买数量减少
				*/
				$('.js-decrease').click(function () {
					if($(this).hasClass("disable")) return false
					var num = parseInt($(".js-quantity").val());
					num--;
					$(".js-quantity").val(num);
					$(".js-increase").removeClass("disable");
					if(num <= 1) $(this).addClass("disable");
				});
				/*
				**商品购买数量输入
				*/
				$('.js-quantity').blur(function () {
					$(".js-decrease").add(".js-increase").removeClass("disable");
					var num = parseInt($(this).val());
					if(!num || num <= 1){
						$(this).val(1);
						$(".js-decrease").addClass("disable");
					}
					if(num >= quantity){
						$(this).val(quantity);
						$(".js-increase").addClass("disable");
					}
				});
				/*
				**立即下单
				*/
				$(".js-goodsIntro").on("click", ".js-buynow", function(){
					if(islogin != 1){
					  login_dialog();
					  return;
					}
					$("#cartID").val(goods_id + "|" + $.trim($(".js-quantity").val()));
					$("#buynowForm").submit();
				});
				/*
				**添加购物车
				*/
				$(".js-goodsIntro").on("click", ".js-addcart", function(){
					if(islogin != 1){
					  login_dialog();
					  return;
					}
					var num = parseInt($(".js-quantity").val());
					if (num <= 0) return false;
					manage.getApi("/index.php?m=shop&c=cart&a=addCart", {
						goods_id: goods_id,
						quantity: num
					}, function(relust){
						parent.layer.closeAll("dialog");
						var num = relust.num || 0;
						$("#js-boldNum").text(num);
						$(".js-cartPop").fadeIn('fast');
					});
				});
				/*
				**上架到店铺
				*/
				$(".js-goodsIntro").on("click", ".js-added", function(){
					var me = $(this);
					if (quantity <= 0) return false;
					var pid = parent.layer.load(360);
					$.getJSON("/ShowFactory/upGoods", {
						goods_commonid: goods_commonid,
						goods_id: goods_id,
						store_id: store_id,
					}, function (relust) {
						parent.layer.close(pid);
                        var resultText = relust.resultText || {};
						if (relust.code == 1) {
							me.addClass("no-buynow").removeClass("js-added");
                            $.wrongMsg("商品上架成功！",1);
						}else {
                            $.wrongMsg(resultText.message || "上架失败！");
						}
					});
				});
				/*
				**关闭购物车浮层
				*/
				$(".js-cartPop").on("click", ".js-close", function(){
					$(".js-cartPop").fadeOut('fast');
				});
			}
		}
		module.exports = Module;
	});
	seajs.use("web/goods", function(module){
		module.init();
	});
})($);
window._bd_share_config = {
        "common": {
            "bdSnsKey": {},
            "bdText": "",
            "bdMini": "2",
            "bdPic":$("#imagesID").val(),
            "bdStyle": "0",
            "bdSize": "16"
        },
        "share": {},
        "selectShare": {"bdContainerClass": null, "bdSelectMiniList": ["qzone", "tsina", "tqq", "renren", "weixin"]}
    };
    with (document)0[(getElementsByTagName('head')[0] || body).appendChild(createElement('script')).src = 'http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion=' + ~(-new Date() / 36e5)];