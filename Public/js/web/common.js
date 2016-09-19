//前端公用
//date: 2016.07.12
//author zhouquan
(function($){
	define("web/common",function(require, exports, module){
		require("jquery/layer");
		var manage = require("com/manage");
		var param = manage.getParam();
		var Module = {
			init: function(){
			}
		}
		module.exports = Module;
	});
	seajs.use("web/common", function(module){
		module.init();
	});
})($);

/*
**登录弹窗
*/
function login_dialog(){
	layer.open({
		type:2,
		title:false,
		area:['414px','384px'],
		content:gConfig.apiurl + "/shop/login/tpllogin"
	});
}

//收藏店铺js
function collect_store(fav_id,jstype,jsobj){
    $.get('/login', function(result){
        if(result=='0'){
            login_dialog();
        }else{
            var url = '/member/favoritesstore';
            $.getJSON(url, {'fid':fav_id}, function(data){
                var resultText = data.resultText || {};
                if (data.code){
                if(jstype == 'count'){
                    $.wrongMsg("收藏成功", 1);
                    $('[nctype="'+jsobj+'"]').each(function(){
                        $(this).html(parseInt($(this).text())+1);
                    });
                }
                if(jstype == 'succ'){
                    $.wrongMsg("收藏成功", 1);
                }
            }
            else
            {
                $.wrongMsg(resultText.message || "收藏失败");
            }
            });
        }
    });
}
/*
**收藏商品
*/
function collect_goods(fav_id, jstype, jsobj){
    $.get('/login', function(result){
        if(result=='0'){
            login_dialog();
        }else{
            $.getJSON("/member/favoritesgoods", {
				fid: fav_id
			}, function(result){
                var resultText = result.resultText || {};
                if (result.code){
					$.wrongMsg("收藏成功", 1);
                	if(jstype == "count"){
						$(jsobj).html(parseInt($(jsobj).text()) + 1);
                	}
            	}else{
					$.wrongMsg(resultText.message || "收藏失败");
				}
            });
        }
    });
}