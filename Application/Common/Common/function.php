<?php
/**
 * API实例化入口
 *
 * @param string $api_name 接口名称
 * @return obj 对象形式的返回结果
 */
function API($api = null){
	static $_cache = array();
	if (!is_null($api) && isset($_cache[$api])) return $_cache[$api];
	//$file_name = BASE_DATA_PATH.'/interface/'.$api.'.api.php';
	$file_name = APP_PATH.'/Common/library/'.$api.'.api.php';//die($file_name);
	$class_name = $api.'api';
	if (!file_exists($file_name)){
		return $_cache[$api] =  new \Org\Curl\CurlUtil($api);
	}else{
		require_once($file_name);
		if (!class_exists($class_name)){
			$error = 'API Error:  Class '.$class_name.' is not exists!';
			throw_exception($error);
		}else{
			import('Curl.CurlUtil');
			return $_cache[$api] = new $class_name();
		}
	}
}

function encode_json($str) {  
    return urldecode(json_encode(url_encode($str)));      
}  

/**
 * 取上一步来源地址
 *
 * @param
 * @return string 字符串类型的返回结果
 */
function getReferer(){
	return empty($_SERVER['HTTP_REFERER'])?'':$_SERVER['HTTP_REFERER'];
}


/** 
	*  
	*/  
function url_encode($str) {  
    if(is_array($str)) {  
        foreach($str as $key=>$value) {  
            $str[urlencode($key)] = url_encode($value);  
        }  
    } else {  
        $str = urlencode($str);  
    }  
      
    return $str;  
} 

/**
 * 消息提示，主要适用于普通页面AJAX提交的情况
 *
 * @param string $message 消息内容
 * @param string $url 提示完后的URL去向
 * @param stting $alert_type 提示类型 error/succ/notice 分别为错误/成功/警示
 * @param string $extrajs 扩展JS
 * @param int $time 停留时间
 */
function showDialog($message = '', $url = '', $alert_type = 'error', $extrajs = '', $time = 2){
	//在Thinkphp 中，改函数被$this->error($msg) 和 $this->success($msg)替代
	
}


/**
 * 获取当前页面完整URL地址
 */
function getUrl(){
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}

function test(){
	echo 'aaaa';
}


/**
 * 取得IP
 *
 *
 * @return string 字符串类型的返回结果
 */
function getIp(){
	if (@$_SERVER['HTTP_CLIENT_IP'] && $_SERVER['HTTP_CLIENT_IP']!='unknown') {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (@$_SERVER['HTTP_X_FORWARDED_FOR'] && $_SERVER['HTTP_X_FORWARDED_FOR']!='unknown') {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return preg_match('/^\d[\d.]+\d$/', $ip) ? $ip : '';
}

function _getcookie($name){
	if(empty($name)){return false;}
	if(isset($_COOKIE[$name])){
		return $_COOKIE[$name];
	}else{		
		return false;
	}
}

function _setcookie($name,$value,$time=0,$path='/',$domain=''){		
	if(empty($name)){return false;}
	$_COOKIE[$name]=$value;				//及时生效
	$s = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;
	if(!$time){
		return setcookie($name,$value,0,$path,$domain,$s);
	}else{
		return setcookie($name,$value,time()+$time,$path,$domain,$s);
	}
}

/**
 * 返回以原数组某个值为下标的新数据
 *
 * @param array $array
 * @param string $key
 * @param int $type 1一维数组2二维数组
 * @return array
 */
function array_under_reset($array, $key, $type=1){
	if (is_array($array)){
		$tmp = array();
		foreach ($array as $v) {
			if ($type === 1){
				$tmp[$v[$key]] = $v;
			}elseif($type === 2){
				$tmp[$v[$key]][] = $v;
			}
		}
		return $tmp;
	}else{
		return $array;
	}
}


/**
* 价格格式化
*
* @param int	$price
* @return string	$price_format
*/
 function ncPriceFormat($price) {
		$price_format	= number_format($price,2,'.','');
		return $price_format;
}


/*
 * 分页函数
 * $cout 总数
 * $pagesize 每页数量
 * return  分页
 */
function  getPage($count,$pagesize=10) {
    $page = new \Think\Page($count,$pagesize);
    $page -> lastSuffix = false;
    $page->setConfig('prev','上一页');
    $page->setConfig('next','下一页');
    $page->setConfig('last','末页');
    $page->setConfig('first','首页');
    $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
    $show = $page->show();
    return $show;
}
/**
 * 取得用户头像图片
 *
 * @param string $member_avatar
 * @return string
 */
function getMemberAvatar($member_avatar){
    if (empty($member_avatar)) {
        return UPLOAD_SITE_URL.DS.ATTACH_COMMON.DS.C('default_user_portrait');
    } else {
    	return C('host').$member_avatar;
    }
}

/**
 * 获得标准json输出
 * @return [type] [description]
 */
function get_standard_return(){
	$result['code'] = 1;
	
	$result['resultText']['message'] = "成功！";
	return $result;
}


/**
 * 
 * 读/写 缓存方法  
 *
 * H('key') 取得缓存
 * H('setting',true) 生成缓存并返回缓存结果
 * H('key',null) 清空缓存
 * H('setting',true,'file') 生成商城配置信息的文件缓存
 * H('setting',true,'memcache') 生成商城配置信息到memcache
 * @param string $key 缓存名称
 * @param string $value 缓存内容
 * @param string $type	缓存类型，允许值为 file,memcache,xcache,apc,eaccelerator，可以为空，默认为file缓存
 * @param int/null $expire 缓存周期
 * @param mixed $args 扩展参数
 * @return mixed
 */
function H($key){
      return  D('GoodsClass')->H();
//    $data = S('goods_class');
//    if($data){
//        return $data;
//    }else{
//        $data = S('goods_class',D('GoodsClass')->H(),3600);
//        return S('goods_class');
//    }
}


/**
 * [refundOrderDispatch description]
 * @param  [type] $order_state     订单状态  0 cancel 10 new 20 pay 30 send 40 complete
 * @param  [type] $refund_type     1 退款  2 退货
 * @param  [type] $return_type     1 不用退货 2 退货
 * @param  [type] $seller_state    1 处理中  2 同意 3 不同意
 * @param  [type] $refund_state    1 处理中 2 等待管理员处理
 * @param  [type] $goods_state     申请状态:1为处理中,2为待管理员处理,3为已完成,默认为1
 * @param  [type] $buyer_or_seller 角色 1 为买家 2 为买家
 * @param  [type] $type            区分发货前退款退货和发货后退款退货 1 为发货前退款  2 为发货后的退款退货
 * @param  [type] $type            投诉状态 0 为没有投诉 投诉状态(10-新投诉/20-投诉通过转给被投诉人/30-被投诉人已申诉/40-提交仲裁/99-已关闭)
 * @return [type]                  [description]
 */
function refundOrderDispatch($order_state, $refund_type, $return_type, $seller_state, $refund_state, $goods_state,$buyer_or_seller,$type, $complain_state =0,$verdict=0){
	if($buyer_or_seller == 1){
		//返回退款退货，不返回退款
		if($type == 2){
			return refundOrderDispatchForBuyer($order_state, 2, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict);
		}else{
			return refundOrderDispatchForBuyer($order_state, 1, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict);
		}
		
	}else{
		if($type == 2){
			return refundOrderDispatchForSeller($order_state, 2, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict);
		}else{
			return refundOrderDispatchForSeller($order_state, 1, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict);
		}
		
	}
}


/**
 * 买家
 * @param  [type] $refund_type  退款退货类型  1 退款 2 退货
 * @param  [type] $return_type  退货类型      1 不用 2 需要退货
 * @param  [type] $seller_state 买家状态      1.待审核 2.同意 3.不同意
 * @param  [type] $refund_state 退款退货申请状态 1.待审核 2.同意 3.不同意
 * @return [type]               [description]
 */
function refundOrderDispatchForBuyer($order_state, $refund_type, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict){
	$menu = array();
	
	$menu['title'] = '申请退款';
	$menu['url'] = urlShop('member', 'apply_for_refund');
	switch ($order_state) {
		case ORDER_STATE_PAY:
        case ORDER_STATE_HANDLING:
			return refundOrderDispatchForBuyerPay($refund_type, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict);
			break;
		case ORDER_STATE_SEND:
			return refundOrderDispatchForBuyerSend($refund_type, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict);
			break;
		case ORDER_STATE_CANCEL:
			return refundOrderDispatchForBuyerCancel($refund_type, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict);
            break;
		case ORDER_STATE_SUCCESS:
			return refundOrderDispatchForBuyerSuccess($refund_type, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict);
            break;
		default:
			return null;
			//$menu['title'] = '申请退款';
			//$menu['url'] = urlShop('member', 'apply_for_refund');
			break;
	}
	return $menu;
}

function refundOrderDispatchForBuyerPay($refund_type, $return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict){
	
	//订单处于已结算，未申请退款退货状态
	if(!isset($refund_type) || empty($refund_type)){
		$menu['title'] = '申请退款';
		$menu['url'] = urlShop('member', 'apply_for_refund');
		return $menu;
	}else{
		if($refund_type == REFUNDTYPE_MONEY){
			//退款
			return refundOrderDispatchForBuyerPayReturnMoney($return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict);
		}else{
			
		}
		
	}
}


/**
 * 买家退款
 *
 * @param      <type>  $return_type   The return type
 * @param      <type>  $seller_state  The seller state
 * @param      <type>  $refund_state  The refund state
 */
function refundOrderDispatchForBuyerPayReturnMoney($return_type, $seller_state, $refund_state, $goods_state,$complain_state,$verdict){
	//已经申请了退款退货，需要区分
	//处理中
	//待退款
	//已完成
	if($seller_state == null){
		$menu['title'] = "(20,退款)申请退款";
		$menu['name'] = "申请退款";
		$menu['url'] = urlShop('member', 'apply_for_refund');
		return $menu;
	}
	if($seller_state == SELLER_STATE_PROCESSING){
		//卖家处理中
		switch ($refund_state) {
			case REFUND_STATE_PROCESSING:
				$menu['title'] = "(20 退款）卖家处理中";
				$menu['name'] = '卖家处理中';
				$menu['url'] = urlShop('member', 'goodsmoneyback_processing');
				return $menu;
				break;
			case REFUND_STATE_REFUNDING:
				$menu['title'] = "退款处理中2";
				$menu['url'] = urlShop('member', 'ignore');
				return $menu;
				break;
			case REFUND_STATE_COMPLETE:
				$menu['title'] = "退款处理中3";
				$menu['url'] = urlShop('member', 'ignore');
				return $menu;
				break;
			
			default:
				
				break;
		}
	}else if($seller_state == SELLER_STATE_AGREE){
		//卖家同意
		switch ($refund_state) {
			case REFUND_STATE_PROCESSING:
				$menu['title'] = "待退款1";
				$menu['url'] = urlShop('member', 'ignore');
				return $menu;
				break;
			case REFUND_STATE_REFUNDING:
				$menu['title'] = "(20,退款)待退款";
				$menu['url'] = urlShop('member', 'money_one_the_way');
				return $menu;
				break;
			case REFUND_STATE_COMPLETE:
				$menu['title'] = "待退款3";
				$menu['url'] = urlShop('member', 'ignore');
				return $menu;
				break;
			
			default:
				
				break;
		}
	}else{
		//卖家不同意
		switch ($refund_state) {
			case REFUND_STATE_PROCESSING:
				$menu['title'] = "已拒绝1";
				$menu['url'] = urlShop('member', 'ignore');
				return $menu;
				break;
			case REFUND_STATE_REFUNDING:
				$menu['title'] = "已拒绝2";
				$menu['url'] = urlShop('member', 'ignore');
				return $menu;
				break;
			case REFUND_STATE_COMPLETE:
				$menu['title'] = "(20,退款)拒绝退款";
				$menu['url'] = urlShop('member', 'seller_refuse_to_refund');
				return $menu;
				break;
			
			default:
				$menu['title'] = "(20,退款)申请退款";
				$menu['name'] = "申请退款";
				$menu['url'] = urlShop('member', 'apply_for_refund');
				return $menu;
				break;

		}
	}
	
}


/**通过百度地图API,传入ip获取对应地址坐标信息**/
function getAddrByIp($ip=''){
	$ip=!empty($ip)?$ip:getIp();
	$url="http://api.map.baidu.com/location/ip?ak=VmIibnB1cW9LG5q6UdTPCAQKscRMk4kW&ip=";
	$url.=$ip;
	$url.='&coor=bd0911';
	return _curl_get($url);
}

/**curl get request**/
function _curl_get($url){
          $curl = curl_init();
          $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
          $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
          $header[] = "Cache-Control: max-age=0";
          $header[] = "Connection: keep-alive";
          $header[] = "Keep-Alive: 300";
          $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
          $header[] = "Accept-Language: en-us,en;q=0.5";
          $header[] = "Pragma: "; // browsers keep this blank.
          curl_setopt($curl, CURLOPT_URL, $url);
          curl_setopt($curl, CURLOPT_USERAGENT, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
          curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
          curl_setopt($curl, CURLOPT_REFERER, 'http://www.baidu.com');
          curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
          curl_setopt($curl, CURLOPT_AUTOREFERER, true);
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt($curl, CURLOPT_TIMEOUT, 10);
          $content = curl_exec($curl); // execute the curl command  get the data
	      if($content){
			  return json_decode($content,true);
		  }else{
			  return [];
		  }
          curl_close($curl); // close the connection
}

function updateEasypayStatus(){
	$easypay_api = API('easypay');
    $result = json_decode($easypay_api->get_credit_status(array('usrid' =>$_SESSION['mid'])), true);
    if($result['code'] == 0){
        $application_model = D('Easypayapplication');
        $data = $result['return_param'];
        if($data['is_loan'] == 0){
        	$data['check_flag'] = 6;
        }
		syncEasypaySession($data);
    }
}

/*
 * 同步分期购session
 */
function syncEasypaySession($data){
		$_SESSION['easypay_credit_status']    = $data['check_flag'];
		$_SESSION['easypay_credit_total']     = $data['loan_limit'];
		$_SESSION['easypay_credit_available'] = $data['loan_useble'];
		$_SESSION['is_activate']              = $data['is_activate'];
		$_SESSION['easypay_freeze']           = $_SESSION['easypay_credit_status'] === -1 ? 1:0;;
		$_SESSION['easypay_status_zh'] = str_replace(
		array(0, 1, 3, 2, 5, 4, 6),
		array('未开启', '审批中','审批中', '审批未通过', '已开通', '审批未通过','冻结' ),
		intval($_SESSION['easypay_credit_status']));
}

/*
 * 获取特定期数信息
 * 
 */
function getMyEasypayInfo($amount, $period=null, $is_tiexi = 1){
    //检查是否开启

    if(!C('easybuy_status'))
        return array();

    //
    $pay_by_period = C('pay_by_period');
    $pay_by_period = unserialize($pay_by_period);

    //期数和费率
    $pay_by_period2 = $pay_by_period;
    $qishu = implode(';', array_keys($pay_by_period));
    $rate = array_values($pay_by_period);
    foreach ($rate as $key => $value) {
        $rate[$key] = number_format($value/100,3,'.','');
    }
    $rate = implode(';', $rate);

    //计算分期费用
    //$easypay_charge_by_ccfax = M('setting')->where(array('name' => 'easypay_charge_by_ccfax'))->getField('value');
    $easypay_charge_by_ccfax = C('easypay_charge_by_ccfax');

    $easypay_api = API('easypay');
    $options['amount']        = $amount;
    $options['interest_type'] = $is_tiexi;//1：贴息 0：不贴息
    $options['periods']       = $qishu;
    $options['interest_rate'] = $rate;
    $options['factorage']     = $easypay_charge_by_ccfax/ 100;
    $result = json_decode($easypay_api->get_interest_info($options), true);
    if($result['code'] == 0){
        $pay_by_period = $result['return_param']['interests_list'];
    }

    //封装利息参数
    foreach ($pay_by_period as $key => $value) {
        $pay_by_period[$key]['principal'] = $amount;
        $pay_by_period[$key]['factorage_rate'] = $options['factorage'];
        $pay_by_period[$key]['interest_rate'] = $pay_by_period2[$value['period']];
        if($value['period'] == $period){
            $tmp =  $pay_by_period[$key];
        }
    }

    if(is_numeric($period)){
        //返回特定期数
        return $tmp;
    }else{
        //返回所有期数
        return $pay_by_period;
    }    
}

//广告图片处理
function advImg($img,$thumb=false){
	$arr=explode(',',$img);
	return $thumb?$arr[0]:$arr[1];
}
