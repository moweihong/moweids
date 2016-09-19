/**
 * @author zhouquan
 * form输入字符验证
 */
(function($){
   function Form($form){
	  var me = this;	  
	  me.$form = $($form);
	  me.error = {};
	  $("input", $form).each(function(i, ele){
		 var $ele = $(ele);
		 if($ele.attr("required")){
			me.error[$ele.attr("name")] = {
				ele: $ele,
				check: false
			};
		 }
	  });
	  function eHandle(e){
		 handle($(e.target || e.srcElement));
	  }
	  var isHandle = false;
	  function handle($ele){
	  	 if(!$.contains(me.$form[0], $ele[0])) return true;
		 if($ele[0].tagName != "INPUT") return true;
		 if(isHandle)return;
		 isHandle=true
		 var bool = false;
		 try{
			//值必填
      		 if ($ele.attr("required") !=  null) {
      			bool = me._required($ele);
      		 }else{
      			delete me.error[$ele.attr("name")];
      		 }
      		 var val = $.trim($ele.val());
      		 if(val != ""){//
      			if ($ele.attr("check-domain")!=null) {
         			bool = me._checkDomain($ele);
         		}
         		if ($ele.attr("check-number")!=null) {
         			bool = me._checkNumber($ele);
         		}
         		if ($ele.attr("check-email")!=null) {
         			bool = me._checkEmail($ele);
         		}
				if ($ele.attr("check-phone")!=null) {
         			bool = me._checkPhone($ele);
         		}
      		 }		     		 
		 }catch(e){
			console.log(e.stack, e);
		 }
		 isHandle = false;
		 return bool;
	  }
	  me._handle = handle;
	  function _submit(e){//表单提交时,触发的事件
		 var valid = me.valid();
		 me.$form.attr("valid", valid);
		 if( !valid ){
			var $first;
			$.each(me.error, function(i, res){
			   var $ele = $(res.ele);
			   if(!$first) $first = $ele;
			   handle($ele);
			});
			$first.focus();
			e.stopPropagation();
			e.preventDefault();
		 }else{
		 	var btn = $(e.target||e.srcElement)[0];
		 	btn.disabled = true;
		 	setTimeout(function(){
		 		btn.disabled = false;
		 	}, 2000)
		 }
	  }
	  me.$form.on({
		 //input: eHandle,
		 //change: eHandle,
		 submit: _submit
	  });
	  $("input", me.$form).live({
		 focus: function(e){
			$.trim($(this).val())!="" && eHandle(e);
		 },
		 blur: function(e){
			eHandle(e);
		 }
	  });
	  /*
	  $("[type=submit]", me.$form).on({
		 click: _submit
	  })
	  */
   }
   $.extend(Form.prototype, {
	  valid: function(){//验证form表单
		 var me = this;
		 for(var name in me.error){
			var res = me.error[name];
			if(res){
			   var bool = me._handle(res.ele);
			   if(bool!=true){
				  return bool;
			   }
			}
		 }
		 return true;
	  },
	  // 必填
	  _required : function($ele) {
		 var me = this,
		 	val = $.trim($ele.val());
		 var res = !/^\s*$/g.test(val), name = $ele.attr("name");
		 var $tip = $("[show='"+name+".check"+"']", me.$form);
		 res ? $tip.hide() : $tip.show();
		 if(!res){
			//$ele.focus();
			me.error[name] = {
				  ele: $ele,
				  check: false
			};
		 }else{
			delete me.error[name];
		 }
		 return res;
	  },
	  _checkNumber : function($ele) {
		 var me = this,
		 	val = $.trim($ele.val());
		 var res = /^[-\d]*$/.test(val), name=$ele.attr("name");
		 var $tip = $("[show='"+name+".check"+"']", me.$form);
		 res ? $tip.hide() : $tip.show();
		 if(!res){
			me.error[name] = {
				  ele: $ele,
				  check: false
			};
		 }else{
			delete me.error[name];
		 }
		 return res;
	  },
	  _checkEmail : function($ele) {
		 var me = this,
		 	val = $.trim($ele.val());
		 var res = /^[\.0-9a-zA-Z_-]+@[0-9a-zA-Z_-]+(\.[0-9a-zA-Z_-]+)+$/.test(val), name=$ele.attr("name");
		 var $tip = $("[show='"+name+".check"+"']", me.$form);
		 res ? $tip.hide() : $tip.show();
		 if(!res){
			me.error[name] = {
				  ele: $ele,
				  check: false
			};
		 }else{
			delete me.error[name];
		 }
		 return res;
	  },
	  _checkPhone : function($ele) {
		 var me = this,
		 	val = $.trim($ele.val());
		 var res =  /^0?1[0-9]\d{9}$/.test(val), name=$ele.attr("name");
		 var $tip = $("[show='"+name+".check"+"']", me.$form);
		 res ? $tip.hide() : $tip.show();
		 if(!res){
			//$ele.focus();
			me.error[name] = {
				  ele: $ele,
				  check: false
			};
		 }else{
			delete me.error[name];
		 }
		 return res;
	  }
   });
   $.fn.form = function(){
	  return new Form(this);
   };
   define("jquery/form", function(require, exports, module){
	  module.exports = $ ;
   });
})(jQuery);