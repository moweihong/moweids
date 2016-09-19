<?php
/**
 * 解密函数
 *
 * @param string $txt 需要解密的字符串
 * @param string $key 密匙
 * @return string 字符串类型的返回结果
 */
function decrypt($txt, $key = '', $ttl = 0){
	if (empty($txt)) return $txt;
	if (empty($key)) $key = md5(MD5_KEY);

	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
	$ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
	$knum = 0;$i = 0;
	$tlen = @strlen($txt);
	while(isset($key{$i})) $knum +=ord($key{$i++});
	$ch1 = @$txt{$knum % $tlen};
	$nh1 = strpos($chars,$ch1);
	$txt = @substr_replace($txt,'',$knum % $tlen--,1);
	$ch2 = @$txt{$nh1 % $tlen};
	$nh2 = @strpos($chars,$ch2);
	$txt = @substr_replace($txt,'',$nh1 % $tlen--,1);
	$ch3 = @$txt{$nh2 % $tlen};
	$nh3 = @strpos($chars,$ch3);
	$txt = @substr_replace($txt,'',$nh2 % $tlen--,1);
	$nhnum = $nh1 + $nh2 + $nh3;
	$mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum % 8,$knum % 8 + 16);
	$tmp = '';
	$j=0; $k = 0;
	$tlen = @strlen($txt);
	$klen = @strlen($mdKey);
	for ($i=0; $i<$tlen; $i++) {
		$k = $k == $klen ? 0 : $k;
		$j = strpos($chars,$txt{$i})-$nhnum - ord($mdKey{$k++});
		while ($j<0) $j+=64;
		$tmp .= $chars{$j};
	}
	$tmp = str_replace(array('-','_','.'),array('+','/','='),$tmp);
	$tmp = trim(base64_decode($tmp));

	if (preg_match("/\d{10}_/s",substr($tmp,0,11))){
		if ($ttl > 0 && (time() - substr($tmp,0,11) > $ttl)){
			$tmp = null;
		}else{
			$tmp = substr($tmp,11);
		}
	}
	return $tmp;
}
/**
 * 加密函数
 *
 * @param string $txt 需要加密的字符串
 * @param string $key 密钥
 * @return string 返回加密结果
 */
function encrypt($txt, $key = ''){
	if (empty($txt)) return $txt;
	if (empty($key)) $key = md5(MD5_KEY);
	$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
	$ikey ="-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
	$nh1 = rand(0,64);
	$nh2 = rand(0,64);
	$nh3 = rand(0,64);
	$ch1 = $chars{$nh1};
	$ch2 = $chars{$nh2};
	$ch3 = $chars{$nh3};
	$nhnum = $nh1 + $nh2 + $nh3;
	$knum = 0;$i = 0;
	while(isset($key{$i})) $knum +=ord($key{$i++});
	$mdKey = substr(md5(md5(md5($key.$ch1).$ch2.$ikey).$ch3),$nhnum%8,$knum%8 + 16);
	$txt = base64_encode(time().'_'.$txt);
	$txt = str_replace(array('+','/','='),array('-','_','.'),$txt);
	$tmp = '';
	$j=0;$k = 0;
	$tlen = strlen($txt);
	$klen = strlen($mdKey);
	for ($i=0; $i<$tlen; $i++) {
		$k = $k == $klen ? 0 : $k;
		$j = ($nhnum+strpos($chars,$txt{$i})+ord($mdKey{$k++}))%64;
		$tmp .= $chars{$j};
	}
	$tmplen = strlen($tmp);
	$tmp = substr_replace($tmp,$ch3,$nh2 % ++$tmplen,0);
	$tmp = substr_replace($tmp,$ch2,$nh1 % ++$tmplen,0);
	$tmp = substr_replace($tmp,$ch1,$knum % ++$tmplen,0);
	return $tmp;
}



/**
 * 拼接动态URL，参数需要小写
 *
 * 调用示例
 *
 * 若指向网站首页，可以传空:
 * url() => 表示act和op均为index，返回当前站点网址
 *
 * url('search,'index','array('cate_id'=>2)); 实际指向 index.php?act=search&op=index&cate_id=2
 * 传递数组参数时，若act（或op）值为index,则可以省略
 * 上面示例等同于
 * url('search','',array('act'=>'search','cate_id'=>2));
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @param boolean $model 默认取当前系统配置
 * @param string $site_url 生成链接的网址，默认取当前网址
 * @return string
 */
function url($act = '', $op = '', $args = array(), $model = false, $site_url = ''){
    //伪静态文件扩展名
    $ext = '.html';
    //入口文件名
    $file = 'index.php';
//    $site_url = empty($site_url) ? SHOP_SITE_URL : $site_url;
    $act = trim($act);
    $op = trim($op);
    $args = !is_array($args) ? array() : $args;
    //定义变量存放返回url
    $url_string = '';
    if (empty($act) && empty($op) && empty($args)) {
        return $site_url;
    }
    $act = !empty($act) ? $act : 'index';
    $op = !empty($op) ? $op : 'index';

    $model = $model ? URL_MODEL : $model;

    if ($model) {
        //伪静态模式
        $url_perfix = "{$act}-{$op}";
        if (!empty($args)){
            $url_perfix .= '-';
        }
        $url_string = $url_perfix.http_build_query($args,'','-').$ext;
        $url_string = str_replace('=','-',$url_string);
    }else {
        //默认路由模式
        $url_perfix = "act={$act}&op={$op}";
        if (!empty($args)){
            $url_perfix .= '&';
        }
        $url_string = $file.'?'.$url_perfix.http_build_query($args);
    }
    //将商品、店铺、分类、品牌、文章自动生成的伪静态URL使用短URL代替
    $reg_match_from = array(
        '/^goods-index-goods_id-(\d+)\.html$/',
        '/^show_store-index-store_id-(\d+)\.html$/',
        '/^show_store-goods_all-store_id-(\d+)-stc_id-(\d+)-key-([0-5])-order-([0-2])-curpage-(\d+)\.html$/',
        '/^article-show-article_id-(\d+)\.html$/',
        '/^article-article-ac_id-(\d+)\.html$/',
        '/^document-index-code-([a-z_]+)\.html$/',
        '/^search-index-cate_id-(\d+)-b_id-([0-9_]+)-a_id-([0-9_]+)-key-([0-3])-order-([0-2])-type-([0-2])-area_id-(\d+)-curpage-(\d+)\.html$/',
        '/^brand-list-brand-(\d+)-key-([0-3])-order-([0-2])-type-([0-2])-area_id-(\d+)-curpage-(\d+)\.html$/',
        '/^brand-index\.html$/',
        '/^show_groupbuy-index-area_id-(\d+)-groupbuy_class-(\d+)-groupbuy_price-(\d+)-groupbuy_order_key-(\d+)-groupbuy_order-(\d+)-curpage-(\d+)\.html$/',
        '/^show_groupbuy-groupbuy_soon-area_id-(\d+)-groupbuy_class-(\d+)-groupbuy_price-(\d+)-groupbuy_order_key-(\d+)-groupbuy_order-(\d+)-curpage-(\d+)\.html$/',
        '/^show_groupbuy-groupbuy_history-area_id-(\d+)-groupbuy_class-(\d+)-groupbuy_price-(\d+)-groupbuy_order_key-(\d+)-groupbuy_order-(\d+)-curpage-(\d+)\.html$/',
        '/^show_groupbuy-groupbuy_detail-group_id-(\d+).html$/',
        '/^pointprod-index.html$/',
        '/^pointprod-plist.html$/',
        '/^pointprod-pinfo-id-(\d+).html$/',
        '/^pointvoucher-index.html$/',
        '/^goods-comments_list-goods_id-(\d+)-type-([0-3])-curpage-(\d+).html$/'
        );
    $reg_match_to = array(
        'item-\\1.html',
        'shop-\\1.html',
        'shop_view-\\1-\\2-\\3-\\4-\\5.html',
        'article-\\1.html',
        'article_cate-\\1.html',
        'document-\\1.html',
        'cate-\\1-\\2-\\3-\\4-\\5-\\6-\\7-\\8.html',
        'brand-\\1-\\2-\\3-\\4-\\5-\\6.html',
        'brand.html',
        'groupbuy-\\1-\\2-\\3-\\4-\\5-\\6.html',
        'groupbuy_soon-\\1-\\2-\\3-\\4-\\5-\\6.html',
        'groupbuy_history-\\1-\\2-\\3-\\4-\\5-\\6.html',
        'groupbuy_detail-\\1.html',
        'integral.html',
        'integral_list.html',
        'integral_item-\\1.html',
        'voucher.html',
        'comments-\\1-\\2-\\3.html'
    );
    $url_string = preg_replace($reg_match_from,$reg_match_to,$url_string);
    return rtrim($site_url,'/').'/'.$url_string;
}


/**
 * 商城会员中心使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @param string $store_domian 店铺二级域名
 * @return string
 */
function urlShop($act = '', $op = '', $args = array(), $store_domain = ''){
   return U("/shop/$act/$op", $args);
}

/**
 * 取得商品缩略图的完整URL路径，接收商品信息数组，返回所需的商品缩略图的完整URL
 *
 * @param array $goods 商品信息数组
 * @param string $type 缩略图类型  值为60,160,240,310,1280
 * @return string
 */
function thumb($goods = array(), $type = ''){
    $type_array = explode(',_', ltrim(GOODS_IMAGES_EXT, '_'));
    if (!in_array($type, $type_array)&&$type!='') {
        $type = '240';
    }
    $upload = C('TMPL_PARSE_STRING')['__UPLOAD__'];
    
    if (empty($goods)){
        return $upload.defaultGoodsImage($type);
    }
    if (array_key_exists('apic_cover', $goods)) {
        $goods['goods_image'] = $goods['apic_cover'];
    }
    if (empty($goods['goods_image'])) {
        return $upload.defaultGoodsImage($type);
    }
    $search_array = explode(',', GOODS_IMAGES_EXT);
    $file = str_ireplace($search_array,'',$goods['goods_image']);
    $fname = basename($file);
    //取店铺ID
    if (preg_match('/^(\d+_)/',$fname)){
        $store_id = substr($fname,0,strpos($fname,'_'));
    }else{
        $store_id = $goods['store_id'];
    }
    $file = $type == '' ? $file : str_ireplace('.', '_' . $type . '.', $file);
    if (!file_exists(ltrim($upload,'\/').'/shop/store/goods/'.$store_id.'/'.$file)){
        return $upload.defaultGoodsImage($type);
    }
    $thumb_host = $upload.'/shop/store/goods/';
    return $thumb_host.$store_id.'/'.$file;

}


/**
 * 取得商品默认大小图片
 *
 * @param string $key	图片大小 small tiny
 * @return string
 */
function defaultGoodsImage($key){
    $file = str_ireplace('.', '_' . $key . '.', DEFAULT_IMG);
	return '/shop/common/'.$file;
}

function getClasses(){
	$goods_class = D('GoodsClass');

    $hie = array();
    $top = array();
    //获取所有gc_parent_id 为0 的记录
    $condition['gc_parent_id']= 0;
    $top_level = $goods_class->where($condition)->select();

    //创建顶级目录
    foreach ($top_level as $key => $value) {
        //$hie[$value['gc_id']] = "abc";
        $top[] = $value['gc_id'];
    }

    $top_array = implode(',', $top);
    $condition2['gc_parent_id'] = array('in', $top_array);
    $sec_level = $goods_class->where($condition2)->select();

    //创建二级目录
    $sec = array();
    $sec_parent = array();
    foreach ($sec_level as $key2 => $value2) {
        $hie[$value2['gc_parent_id']][$value2['gc_id']] = array();
        $sec_parent[$value2['gc_id']] = $value2['gc_parent_id'];
        $sec[] = $value2['gc_id'];
    }

	//三级目录
    $third_gc_id_arr = array();
    $sec_array = implode(',',  $sec);
    $condition3['gc_parent_id'] = array('in', $sec_array);
    $third_level = $goods_class->where($condition3)->select();
	$thi_parent=[];
	foreach ($third_level as $key2 => $value2) {
        $thi_parent[$value2['gc_id']] = $value2['gc_parent_id'];
        $third_gc_id_arr[] = $value2['gc_id'];
    }

    //四级目录
    $thi_array = implode(',', $third_gc_id_arr);
    $condition4['gc_parent_id'] = array('in', $thi_array);
    $four_level = $goods_class->where($condition4)->select();
    $four_parent = array();
    foreach ($four_level as $value2) {
        //$four_parent[$value2['gc_id']] = $value2['gc_parent_id'];
        $four_parent[] = $value2['gc_id'];
    }

	//一级目录,构造数组
	$hie=[];
	foreach ($top as $key=>$val){
		$hie[$val]=array();
	}

	//构造二级目录
	foreach ($sec_parent as $k2=>$v2){
        if(array_key_exists($v2,$hie)){
            $hie[$v2][$k2]=[];
        }
    }

	//构造三级目录
	foreach ($thi_parent as $k2=>$v2){
		foreach ($hie as $k=>$v){
            if(array_key_exists($v2,$hie[$k])){
                $hie[$k][$v2][$k2]=[];
            }
		}
	}

    foreach ($four_parent as $gc_id) {
        $gc_id_info = $goods_class->getGoodsClassLineForTag($gc_id);
        $classinfo = $goods_class->getGoodsClassInfo(array('gc_id' => $gc_id_info['gc_id_1']), 'gc_parent_id');
        $top_gc_id = $classinfo['gc_parent_id'];
        if ($top_gc_id){
            $hie[$top_gc_id][$gc_id_info['gc_id_1']][$gc_id_info['gc_id_2']][$gc_id] = array();
        }
	}

   /* foreach ($third_level as $key3 => $value3) {
        $top1 = $sec_parent[$value3['gc_parent_id']];
        $sec1 = $value3['gc_parent_id'];
        $thi1 = $value3['gc_id'];
        //构造三级菜单
        $hie[$top1][$sec1][$thi1] = array();
    }*/
    return $hie;
}

/**
 * 取得店铺标志
 *
 * @param string $店铺标志
 * @return string
 */
function getStoreLogo($store_logo){
    if (empty($store_logo)) {
        return $upload = C('TMPL_PARSE_STRING')['__IMG__'].'/def_header.jpg';
    } else {
        if (file_exists(ltrim(C('TMPL_PARSE_STRING')['__UPLOAD__'],'\/').'/shop/store/'.$store_logo)){
            return C('TMPL_PARSE_STRING')['__UPLOAD__'].'/shop/store/'.$store_logo;
        } else {
            return C('TMPL_PARSE_STRING')['__IMG__'].'/def_header.jpg';
        }
    }
}


function unique_arr($array2D, $stkeep = false, $ndformat = true){
    // 判断是否保留一级数组键 (一级数组键可以为非数字)
    if($stkeep) $stArr = array_keys($array2D);
    // 判断是否保留二级数组键 (所有二级数组键必须相同)
    if($ndformat) $ndArr = array_keys(end($array2D));
    //降维,也可以用implode,将一维数组转换为用逗号连接的字符串
    foreach ($array2D as $v){
        $v = join(",",$v);
        $temp[] = $v;
    }
    //去掉重复的字符串,也就是重复的一维数组
    $temp = array_unique($temp);
    //再将拆开的数组重新组装
    foreach ($temp as $k => $v) {
        if($stkeep){
            $k = $stArr[$k];
        }
        if($ndformat) {
            $tempArr = explode(",", $v);
            foreach($tempArr as $ndkey => $ndval){
                $output[$k][$ndArr[$ndkey]] = $ndval;
            }
        }
        else $output[$k] = explode(",",$v);
    }

    return $output;
}

?>