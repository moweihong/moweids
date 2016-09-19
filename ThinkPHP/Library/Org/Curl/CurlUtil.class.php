<?php
/**
 * 核心文件
 *
 * API类
 *
 */
namespace Org\Curl;
class CurlUtil{

	//调试模式
    public static $debug = false;
	public $config = array();

	public function __construct(){
	}

	public function set($name, $value = '') {
		$this->config[$name] = $value;
	}

	/**
     * 发起一个HTTP/HTTPS的请求
     * @param $url 接口的URL 
     * @param $params 接口参数   array('content'=>'test', 'format'=>'json');
     * @param $method 请求类型    GET|POST
     * @return string
     */
    public  function request( $url , $params = array(), $method = 'GET' , $multi = false, $extheaders = array())
    {
        if(!function_exists('curl_init')) exit('Need to open the curl extension');
        $method = strtoupper($method);
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ci, CURLOPT_TIMEOUT, 20);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ci, CURLOPT_HEADER, false);
        $headers = (array)$extheaders;
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (!empty($params))
                {
                    if($multi)
                    {
                        foreach($multi as $key => $file)
                        {
                            $params[$key] = '@' . $file;
                        }
                        curl_setopt($ci, CURLOPT_POSTFIELDS, $params);
                        $headers[] = 'Expect: ';
                    }
                    else
                    {
                        curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($params));
                    }
                }
                break;
            case 'DELETE':
            case 'GET':
                $method == 'DELETE' && curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($params))
                {
                    $url = $url . (strpos($url, '?') ? '&' : '?')
                        . (is_array($params) ? http_build_query($params) : $params);
                }
                break;
        }
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );
        curl_setopt($ci, CURLOPT_URL, $url);
        if($headers)
        {
            curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
        }

        $response = curl_exec($ci);
        curl_close ($ci);
        return $response;
    }

	/**
     * 发起一个API请求
     * @param $command 接口名称 如：t/add
     * @param $params 接口参数  array('content'=>'test');
     * @param $method 请求方式 POST|GET
     * @param $multi 图片信息
     * @return string
     */
    public  function api($command, $params = array(), $method = 'GET', $multi = false, $url2=null)
    {
        $url = $this->config['url'].trim($command, '/');

        if(!is_null($url2)){
            $url = $url2.trim($command, '/');
        }
        \Think\log::ext_log('url = '.$url.' param ='.json_encode($params).'  method='.$method, 'api');
        //请求接口
        $r = self::request($url, $params, $method, $multi);

        $r = preg_replace('/[^\x20-\xff]*/', "", $r); //清除不可见字符
        $r = iconv("utf-8", "utf-8//ignore", $r); //UTF-8转码
        //调试信息
        if (self::$debug) {
            echo '<pre>';
            echo '接口：'.$url;
            echo '<br>请求参数：<br>';
            print_r($params);
            print_r($multi);
            echo '返回结果：'.$r;
            echo '</pre>';
        }
        return $r;
    }


    /**
     * 发起请求
     * @param  [type] $index   路径索引
     * @param  [type] $options 请求参数
     * @return [type]          [description]
     */
    public function getResponse($index, $options, $method = 'GET',$url=null){
        $result = $this->api(C('javaapi_'.$index), $options, $method, false, $url);
        if(is_null($url)){
            $result = $this->checkError($result, $index, $this->config['url']);    
        }else{
            $result = $this->checkError($result, $index, $url);    
        }
        \Think\Log::ext_log('java response  = '.$result, 'api');
        return $result;
    }

    /**
     * 检查javaapi错误
     */
    public function checkError($result, $interfaceName="", $ip=""){
        if(!C('debug')){
        }else{
        }

        //在result中查询<html>如果返回非false
        if(empty($result) || !isset($result)){
            $res['code'] = "-404";
            $res['resultText'] = 'JAVA接口没有响应';
            //java json格式前后不统一，此处做兼容
            $res['message'] = 'JAVA接口没有响应';
            $result = json_encode($res);
        }else if(strpos("$result", '<html>') === false){
            $res['code'] = "-404";
            //java json格式前后不统一，此处做兼容
            $res['message'] = 'JAVA接口异常：'.$result;
            //$res['resultText']['message'] = 'JAVA接口异常：'.$result;
            $res['resultText']['message'] = 'JAVA接口异常-404';
        }else{
            $res['code'] = "-500";
            //java json格式前后不统一，此处做兼容
            $res['message'] = 'JAVA接口异常：'.$result;
            $res['resultText']['message'] = 'JAVA接口异常!';

            $result = json_encode($res);
        }

        /*
         * java 和 全木行的接口规范不相同 ，将java的接口标准适配到全木行
         */
        $response = json_decode($result, true);
        if(is_null($response['resultText'])||(empty($response['resultText']))){

            $response['resultText']['message'] = $response['msg']?$response['msg']:"空消息";
        }
        $result = json_encode($response);

        return $result;
    }

    public function returnFalse(){
        $res['resultText'] = "";
        $res['code'] = -1;
        return json_encode($res);
    }



}