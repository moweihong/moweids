<?php

namespace Shop\Controller;
use Think\Controller;
use Think\Log;
/*
 * 图片上传
 */
class WebuploadController extends Controller
{
    private $targetDir = 'data/upload/tmp';//临时文件夹

    private $uploadDir = 'data/upload/tmp';//上传文件目录
    private $allowtype = array('jpg', 'jpeg', 'gif', 'png', 'pdf');//允许上传的图片类型
    private $maxsize = 10485760000000;//限制图片上传的大小
    private $name = '';//新的文件名

    function __construct()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

    }

    public function set($option = array())
    {
        if (!empty($option)) {
            $varArr = get_class_vars(get_class($this));
            foreach ($option as $key => $val) {
                if (!array_key_exists($key, $varArr)) {
                    continue;
                }
                $this->$key = $val;
            }
        }
    }

    public function index(){
        echo 'abc';
    }

    /**
     * 上传文件
     * @return [type] [description]
     */
    public function upfile()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {//拒绝的方法
            exit;
        }

        if (!empty($_REQUEST['debug'])) {
            $random = rand(0, intval($_REQUEST['debug']));
            if ($random === 0) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }

        @set_time_limit(5 * 60);//设置脚本最大执行时间
        //解析参数，
        $result = $this->dirDispatch();
        if (!$result) {
            $return['jsonrpc'] = "2.0";
            $return['result'] = $subpath;
            $return['error']['code'] = 1001;
            $return['error']['message'] = "POST IS empty.";
            $return['id'] = 'id';
            echo json_encode($return);
            exit;
        }


        $targetDir = $this->targetDir;
        $uploadDir = $this->uploadDir;
        $cleanupTargetDir = true;
        $maxFileAge = 5 * 3600; //临时文件生成时间

        if (!file_exists($targetDir)) {//创建临时文件夹
            @mkdir($targetDir);
        }

        if (!file_exists($uploadDir)) {//创建上传文件夹
            @mkdir($uploadDir);
        }

        // Get a file name
        if (isset($_REQUEST["id"])) {
            $id = $_REQUEST["id"];
        }

        //获取文件名
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        //获取后缀
        $pos = strrpos($fileName, '.');
        $suffix = substr($fileName, $pos);

        if (!$this->checkFileType(trim($suffix, '.'))) {
            echo "file type disallowed";
            exit();
        }
        if (empty($this->name)) {
            $fileName = rand(1, 10000) . date('YmdHis', time()) . $suffix;
        } else {
            $fileName = $this->name . $suffix;
        }
        if($_POST['storage']=='goods'){
            $fileName=$_SESSION['store_id'].'_'.$fileName;
        }
        $filePath = $targetDir . '/' . $fileName;
        $uploadPath = $uploadDir . '/' . $fileName;
        $chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
        $chunks = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 1;
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {

                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }

            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }

        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }

            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }

        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }

        @fclose($out);
        @fclose($in);

        try {
            rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");
        } catch (Exception $e) {
        }

        $index = 0;
        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {
                $done = false;
                break;
            }
        }
        if ($done) {
            if (!$out = @fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }

            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                        break;
                    }

                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }

                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part");
                }

                flock($out, LOCK_UN);
            }
            @fclose($out);
        }
        //$subpath = substr($uploadPath, 2);
        $subpath = $uploadPath;
        $return['jsonrpc'] = "2.0";
        $return['result'] = "/".$subpath;
			
        $return['id'] = $id;
        $return['file_type'] = trim($suffix, '.');
        \Think\Log::lol(' path  = '.$absolute_path);
        //生成商品缩略图仅仅在是商品图片上传的时候生效
        if ($_POST['storage'] == 'goods') {
            $thumb = new \Common\library\thumb();
            $absolute_path = BASE_ROOT_PATH . DS . $subpath;
            \Think\Log::lol(' path  = '.$absolute_path);
            $image_type = getimagesize($absolute_path);//获取图片信息
            for ($i = 0; $i < 4; $i++) {
                switch ($i) {
                    case '0':
                        $size = 60;
                        break;
                    case '1':
                        $size = 240;
                        break;
                    case '2':
                        $size = 360;
                        break;
                    case '3':
                        $size = 1280;
                        break;
                }
                $img_src = $thumb->ImageType($absolute_path);//$img_src为压缩后的图片
                //获取图片类型
                $img_src=$thumb->resizeImages($img_src,$size,$size);//调用上面的函数
                \Think\Log::lol(' IMAGE TYPE   = '.json_encode($image_type));
                //生成新的图片
                if (strpos($image_type ['mime'], 'png')) {
                    $subpath=str_replace('.png','',$subpath);
                    imagepng($img_src, BASE_ROOT_PATH . DS . $subpath . '_' . $size . '.png');
                }
                /*统一处理jpg 和 jpeg 类型*/
                if (strpos($image_type ['mime'], 'jp')) {
                    $ext_pos = strpos($subpath, '.jp');
                    $ext = substr($subpath, $ext_pos);
                    $tmp_path = preg_replace("/.jp.*$/", "", $subpath);
                    $filename = BASE_ROOT_PATH . DS . $tmp_path . '_' . $size . $ext;
                    imagejpeg($img_src, $filename);
                }

                if (strpos($image_type ['mime'], 'gif')) {
                    $subpath=str_replace('.gif','',$subpath);
                    imagegif($img_src, BASE_ROOT_PATH . DS . $subpath . '_' . $size . '.gif');
                }
            }
        }
        echo json_encode($return);

    }

    /**
     * 根据请求参数获取存储路径
     * @return [type] [description]
     */
    private function dirDispatch()
    {
        //如果post请求为空，返回
        if (!isset($_POST) || empty($_POST)) {
            return false;
        }

        //解析storage
        $storage = $_POST['storage'];
        $this->switchUploadDir($storage);
        return true;
    }

    /**
     * storage 分发图片存储路径
     * @return [type] [description]
     */
    private function switchUploadDir($storage)
    {
        if (!isset($storage) || empty($storage)) {
            $storage = 'general';
        } else {
            $storage = $storage;
        }
        if ($storage == 'goods') {
            //仅针对商品图片上传有效
            if (intval($_SESSION['store_id']) > 0) {
                $storage = 'store/goods/' . $_SESSION['store_id'];
            }
        }

        if ($storage == 'slide') {
            //仅针对商品图片上传有效
            if (intval($_SESSION['store_id']) > 0) {
                $storage = 'store/slide/';
            }
        }

        $tmp = 'data/upload/shop/' . $storage;
        $this->targetDir = $tmp;
        $this->uploadDir = $tmp;
        return;
    }

    /**
     * 检查文件大小
     * @param  [type] $filesize [description]
     * @return [type]           [description]
     */
    private function checkFileSize($filesize)
    {
        if ($filesize > $this->maxsize) {
            return false;
        }
        return true;
    }

    /**
     * 检查文件类型
     * @param  [type] $filetype [description]
     * @return [type]           [description]
     */
    private function checkFileType($filetype)
    {
        if (!in_array(strtolower($filetype), $this->allowtype)) {
            return false;
        }
        return true;
    }
}