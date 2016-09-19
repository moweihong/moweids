(function($){
	var Module = {
		base: function(param){
			param = param || {};
			var pickID = param.pickID || "imgFileBtn",
				objID = param.objID || "fileList",
				$list = $("#" + objID),
				storage = param.storage || "temp";
			seajs.use("jquery/webuploader", function(){
				if ( !WebUploader.Uploader.support() ) {
					layer.alert( 'Web Uploader 不支持您的浏览器！如果你使用的是IE浏览器，请尝试升级 flash 播放器');
					return;
					throw new Error( 'WebUploader does not support the browser you are using.' );
				}
				var isSingle = param.isSingle || false,
					isCover = param.isCover || false,
					isBanner =  param.isBanner || false,
					// 优化retina, 在retina下这个值是2
					ratio = window.devicePixelRatio || 1,
					// 缩略图大小
					thumbnailWidth = (param.thumbnailWidth || 200) * ratio,
					thumbnailHeight = (param.thumbnailHeight || 200) * ratio,
					fileSizeLimit = param.fileSizeLimit || 50,
					fileSingleSizeLimit = param.fileSingleSizeLimit || 2,
					accept = param.accept ||  {
							title: "请选择正确类型图片",
							extensions: "gif,jpg,jpeg,png",
							mimeTypes: "image/*"
						};
				if(isSingle){
					var fileNumLimit = 100;
				}else{
					var fileNumLimit = param.fileNumLimit || 5;
				}
				var uploader = WebUploader.create({
					pick: {
						id: "#" + pickID
					},
					formData: {
						storage: storage
					},
					auto: true, // 自动上传
					compress: false,
					swf: gConfig.jsurl + "/jquery/webuploader/Uploader.swf", // swf文件路径
					prepareNextFile: true,
					//chunked: true,
					//chunkSize: 512 * 1024,
					accept: accept,
					thumb: {
						width: thumbnailWidth,
						height: thumbnailHeight,
						quality: 80,
						allowMagnify: true,
						crop: true,
						type: "image/jpeg"
					},
					fileNumLimit: fileNumLimit,
					fileSizeLimit: fileSizeLimit * 1024 * 1024, // 20 M
					fileSingleSizeLimit: fileSingleSizeLimit * 1024 * 1024 // 2 M
				});
				// 当有文件添加进来的时候
				uploader.on("fileQueued", function(file){
					if(isSingle){ //单个图片
						if(pickID == "signageFileBtn"){
							var $panel = '<p class="file-panel"><a class="icon-view js-viewImg" title="预览"></a><a class="icon-cancel js-cancel" title="删除"></a></p>';
						}else{
							var $panel = '<p class="file-panel"><a class="icon-view js-viewImg" title="预览"></a></p>';
						}
						var $li = $(
								'<div id="' + file.id + '" class="file-item thumbnail">' + $panel +
									'<img width="' + thumbnailWidth + '" height="' + thumbnailHeight + '" />' +
								'</div>'
								),
							$img = $li.find('img');
						$list.html( $li );
					}else{
						if($(".file-item", $list).length >= fileNumLimit){
							$.wrongMsg("最多只能上传" + fileNumLimit + "张图片");
							return;
						}
						var $li = $(
								'<div id="' + file.id + '" class="file-item thumbnail">' +
									'<p class="file-panel">'+
										'<a class="icon-view js-viewImg" title="预览"></a>' +
										'<a class="icon-cancel js-cancel" title="删除"></a>' +
									'</p>'+
									'<img width="' + thumbnailWidth + '" height="' + thumbnailHeight + '" />' +
								'</div>'
								),
							$img = $li.find('img');
						$list.append( $li );
					}
					if(file.type == "application/pdf"){
						$li.html('<p class="file-panel"><a class="icon-download js-download" title="下载" target="_blank"></a></p><img width="200" height="200" src="' + gConfig.jsurl + "/../images/doc/psf.png" + '" /><p class="info"></p>')
					}
					// 创建缩略图
					uploader.makeThumb(file, function(error, src){
						if (error) {
							return;
						}
						$.post(
							gConfig.apiurl + "/index.php?m=shop&c=webpreview",{
								storage: storage,
								src: src
							},
							function(data){
								var data = JSON.parse(data)
								if(data.result){
									if(storage == "easypay"){ //授信
										$img.attr("src", gConfig.sitehost + data.result);
									}else{
										$img.attr("src", data.result);
									}
								}else{
									$img.replaceWith('<p style="font-size:14px;text-align:center;">预览出错</p>');
								}
							}
						);
						
					}, thumbnailWidth, thumbnailHeight);
					
					/*删除图片*/
					$list.on("click", ".js-cancel", function(){
						uploader.removeFile(file, true);
					});
				});
				
				// 文件上传过程中创建进度条实时显示。
				uploader.on("uploadProgress", function( file, percentage ) {
					var $li = $("#" + file.id ),
						$percent = $li.find('.file-progress span');
					// 避免重复创建
					if ( !$percent.length ) {
						$percent = $('<p class="file-progress"><span></span></p>').appendTo( $li ).find('span');
					}
					$percent.css( 'width', percentage * 100 + '%' );
				});
			
				// 文件上传成功，给item添加成功class, 用样式标记上传成功。
				uploader.on("uploadSuccess", function( file ) {
					$("#" + file.id).addClass("upload-state-done");
				});
			
				// 文件上传失败，现实上传出错。
				uploader.on( 'uploadError', function( file ) {
					var $li = $( '#'+file.id ),
						$error = $li.find('div.error');
					// 避免重复创建
					if ( !$error.length ) {
						$error = $('<div class="file-error"></div>').appendTo( $li );
					}
					$error.text('上传失败');
				});
				
				// 接收服务器数据
				uploader.on( 'uploadAccept', function(object, ret) {
					if(!ret.id){
						$("#fileList .file-item:last").remove();
						layer.alert("不支持该图片格式!");
					}else{
						if(storage == "easypay" || storage == "credit"){ //授信 & 征信
							var rel_src = gConfig.sitehost + ret.result;
						}else{
							var rel_src = ret.result;
						}
						var file_type = ret.file_type || "";
						if(file_type == "pdf"){
							$("#" + ret.id).find(".js-download").attr("href", rel_src);
						}else{
							$("#" + ret.id).find("img").attr("data-rel", rel_src);
						}
					}
				});
			
				// 完成上传完了，成功或者失败，先删除进度条
				uploader.on( 'uploadComplete', function( file ) {
					$("#" + file.id).find(".file-progress").remove();
					if(isCover){
						$("#" + file.id).append('<p class="info"><label><input type="radio" name="cover" class="ver-2" value="1" /> 设为封面</label></p>');
						$(".file-item:eq(0)", $list).find(".info input").attr("checked", true);
					}else if(isBanner){
						$("#" + file.id).append('<p class="info"><label><input type="radio" name="cover" class="ver-2" value="1" /> 设为首页</label></p><p class="chained">链接地址：<input type="text" value="http://" placeholder="例如：http://www.baidu.com" class="text js-url" /></p>');
						$(".file-item:eq(0)", $list).find(".info input").attr("checked", true);
					}else{
						$("#" + file.id).find(".info").html(file.name);
					}
				});
				// 错误信息
				uploader.onError = function(code){
					console.log(code)
					if(code == "Q_EXCEED_NUM_LIMIT"){
						$.wrongMsg("最多只能上传" + fileNumLimit + "张图片");	
					}
					if(code == "F_EXCEED_SIZE"){
						$.wrongMsg("图片大小不能超过" + fileSingleSizeLimit + "M");	
					}
					if(code == "F_DUPLICATE"){
						$.wrongMsg("图片已经重复");	
					}
					if(code == "Q_TYPE_DENIED"){
						$.wrongMsg("图片格式不支持");
					}
				};
			});
			//图片悬停/离开
			$($list).on("mouseenter", ".file-item", function(){
				$(".file-panel", this).stop().animate({height: 25});
			}).on("mouseleave", ".file-item", function(){
				$(".file-panel", this).stop().animate({height: 0});
			})
			/*
			 *删除上传图片
			 */
			$($list).on("click", ".js-cancel", function(){
				$(this).closest(".file-item").remove();
			});
			//图片预览
			$($list).on("click", ".js-viewImg", function(){
				var sImg = $(this).closest(".file-item").find("img"),
					src = sImg.data("rel"),
					bImg = '<img src="' + src + '" style="max-width:800px;max-height:600px;vertical-align:middle;" />';
				$(bImg).load(function(){
					parent.layer.open({
						type: 1,
						title: false,
						area: ['auto', 'auto'],
						//closeBtn: 0,
						shadeClose: true,
						content: '<p style="min-width:300px;min-height:300px;line-height:280px;padding:20px;text-align:center;">' + bImg + '</p>'
					});
				});
			});
		}
	}
	$.getCss(gConfig.jsurl + "/jquery/webuploader/style.css");
	define("jquery/upload", function(require, exports, module) {
		module.exports = Module;
	});
})(jQuery);