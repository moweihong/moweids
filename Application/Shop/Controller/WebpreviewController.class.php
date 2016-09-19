<?php
/**
 * 此页面用来协助 IE6/7 预览图片，因为 IE 6/7 不支持 base64
 */

namespace Shop\Controller;
use Think\Controller;
use Think\Log;
class WebpreviewController extends Controller {

    public function index(){
        $DIR = 'data/upload/tmp';
        // Create target dir
        if (!file_exists($DIR)) {
            @mkdir($DIR);
        }

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        if ($cleanupTargetDir) {
            if (!is_dir($DIR) || !$dir = opendir($DIR)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $DIR . DIRECTORY_SEPARATOR . $file;

                // Remove temp file if it is older than the max age and is not the current file
                if (@filemtime($tmpfilePath) < time() - $maxFileAge) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }

        //$src = file_get_contents('php://input');
        $src = $_REQUEST['src'] ? $_REQUEST['src'] : '';
        $filedir_name = isset($_REQUEST['storage']) ? "upload_".$_REQUEST['storage'] : 'default';
        //$dir_tmp = '/data/upload/preview/';
        $dir_tmp = dirname(dirname(dirname(dirname(__FILE__)))).'/data/upload/preview/';


        if (!file_exists($dir_tmp)) {
            @mkdir($dir_tmp);
        }
        $filedir = $dir_tmp.$filedir_name;

        if (!file_exists($filedir)) {
            @mkdir($filedir);
        }

        if (preg_match("/^(data:\s*image\/(\w+);base64,)/", $src, $result)) {   

        $new_file = $filedir."/thumb_".mt_rand(1, 999999).time().".".$result[2];
            if (file_put_contents($new_file, base64_decode(str_replace($result[0], '', $src)))){
                $lpath_arr = explode('/', $new_file);
                $lpath = '/'.$lpath_arr[1].'/'.$lpath_arr[2].'/'.$lpath_arr[3].'/'.$lpath_arr[4];
                $pos = strpos($new_file, '/data/');
                $res = substr($new_file, $pos);
                $return['jsonrpc'] = "2.0";
                $return['result'] = $res;
                $return['id'] = "id";
                echo json_encode($return);

            }
        } else {
            $return['jsonrpc'] = "2.0";
            $return['error']['code'] = 100;
            $return['error']['message'] = 'un recoginized sourc';    
            echo json_encode($return);
        }
    }
}