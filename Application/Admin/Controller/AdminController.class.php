<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Image;
class AdminController extends Controller { 
	static public $dbSource = 'close';
	static public $db;
	
	function __construct(){
		parent::__construct();
		$this->_run();  
		$this->db = self::$dbSource;
	} 

	public function _run(){ 
		$this->_conf();  
		$this->checkLogin();
		$this->creatDB(); //单例DB
	} 
	
	//common/conf/config.php中做系统配置，自定义配置写在这里
	public function _conf(){
		$this->tpl        = '/Application/'.MODULE_NAME.'/View/';  	
	    $this->common_tpl = '/Application/Common/view/';
		$this->url        = 'http://'.C('DOMAIN').__SELF__;
		$this->timestamp  = time();
		$this->module_url = __ROOT__.'/'.MODULE_NAME.'/';
		$this->pre        = C('DB_PREFIX');
		$this->root       = $_SERVER['DOCUMENT_ROOT']; 
	}
	 
	public function checkLogin(){
		//检验后台登录
		if(MODULE_NAME=="Admin" && __ACTION__ != (__ROOT__.'/Admin/Index/login')){
			$is_expired = false;     //是否过期 
			if(isset($_SESSION['admin'])){
				$is_expired = $_SESSION['admin']['login_time'] + $_SESSION['admin']['expired'] < time() ? true : $is_expired;
			}
			if(!isset($_SESSION['admin']) || $is_expired){
				redirect(__ROOT__.'/Admin/Index/login');
				exit;
			}
			$_SESSION['admin']['login_time'] = time();  //刷新登录时间
			$this->assign('admin_user', $_SESSION['admin']['username']);
		}  
	}

	static public function creatDB(){ 
		if(self::$dbSource=='close'){   ;
			self::$dbSource = new \Think\Model();
		}  
		return self::$dbSource;
	}
	
	public function upload($path,$file,$savename,$suffix=array('jpg','gif','png')){
        $upload = new \Think\Upload(); 
        $upload->exts      =  $suffix; 				     //允许上传类型
		$upload->maxSize   =  1048576*5;				 //最大5M
        $upload->rootPath  =  $path;    				 //上传目录
        $upload->saveName  =  $savename;				 //上传后文件名
        $upload->replace   =  true; 					 //上传后文件名
        $upload->autoSub   =  false; 
        $info			   =  $upload->uploadOne($file); //单文件上传
        if(!$info) {									 //上传错误提示错误信息
			$this->error($upload->getError());
		}else{											 //上传成功 获取上传文件信息
			return $info['savepath'].$info['savename'];
		}
	}
	
	//404跳转
	public function errorNotFound(){
		$this->display('./index');   
	}
	
	/**
	 * curl发送http请求
	 * @param string $url   请求链接
	 * @return array $arr   请求返回
	 */
	public function get_curl($url){
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		$json_str = curl_exec($ch);
		$arr = json_decode($json_str, true);
		return $arr;
	}
	
	/**
	 * curl发送POST数据请求
	 * @param string $url   请求链接
	 * @param string $data  要POST的数据，json格式
	 * @return array $arr   请求返回
	 */
	public function post_curl($url,$data){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json_str = curl_exec($ch);
		$arr = json_decode($json_str, true);
		return $arr;;
	}
 
	
	/**
	debug函数 add by tangzhe 2015-06-23
	par $var  : 要打印的数据
	par $die  : 是否要exit();
	par $type : 是否以var_dump()打印，默认print_r()
	*/
	public function e($var,$die=true,$type=false){
		//$trace = debug_backtrace();
		//echo "<br/>File: <b>",$trace[0]['file'],"</b><br/>Line: <b>",$trace[0]['line'],"</b>";
		echo "<pre>";
		if($type)
			var_dump($var);
		else
			print_r($var);
		echo "</pre>";
		if($die) die();
	}
	
	/*Tzmodel中调用Thinkphp内置函数接口*/	
	public function __call($funcName,$params){   
		if($this->callFather==1){ 
			$this->callFather = 2;
			parent::$funcName($params[0],$params[1],$params[2],$params[3],$params[4],$params[5]);
		}else{  
			$this->error('方法不存在！','',1);
		}
	}
	
	public function UploadFile($array = null)
    {
        $config = array_merge(C("PICTURE_UPLOAD"), $array);//print_r($config);exit;
        $upload = new \Think\Upload($config); // 实例化上传类

        $info = $upload->uploadOne($config['file']);
        if (!$info) {
            $this->error($upload->getError(), $config['url']);
        } else {
            $save_path = $config['rootPath'] . $info['savepath'];
            $filename = $info['savename'];
            //chmod($save_path . $filename, 0777);
            if (isset($array['w']) || isset($array['h'])) {
                $width = $array['w'];
                $height = $array['h'];
                $image = new \Think\Image();
                $image->open($save_path . $filename);
                $image->thumb($width, $height, Image::IMAGE_THUMB_CENTER)->save($save_path . 'm_' . $filename);
                $info['savename'] = 'm_' . $filename;
                //chmod($save_path . 'm_' . $filename, 0777);
            }
            if (isset($array['avatar'])) {
                $width = 45;
                $height = 45;
                $image = new \Think\Image();
                $image->open($save_path . $info['savename']);
                $image->thumb($width, $height, Image::IMAGE_THUMB_CENTER)->save($save_path . 's_' . $filename);
                //chmod($save_path . 's_' . $filename, 0777);
            }

            return str_replace("./", "/", $save_path) . $info['savename'] . "?r=" . time();
        }
    }
	
}