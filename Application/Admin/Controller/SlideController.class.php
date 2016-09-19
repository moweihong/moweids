<?php
/*
 * 幻灯片管理
 */
namespace Admin\Controller;
use Admin\Controller\AdminController;
class SlideController extends AdminController {

	/*
	 * 幻灯片管理首页
     */
	public function index()
    {
        $name = 'HOME_PAGE';
        $this->config($name);
    }
	//精品家具
	public function furniture()
    {
        $name = 'FURNITURE_PAGE';
        $this->config($name);
    }
	//品牌专区
	public function brand()
    {
        $name = 'BRAND_PAGE';
        $this->config($name);
    }
	//家装设计
	public function homedesign()
    {
        $name = 'HOMEDESIGN_PAGE';
        $this->config($name);
    }
	//装修材料
	public function material()
    {
        $name = 'MATERIAL_PAGE';
        $this->config($name);
    }
	
	public function config($name)
	{
        $db   = M('web');
        $data = $db->where("web_page='%s'", $name)->find();
        if (IS_POST) {
            $ids   = I("id",null);
            $array = array();
            for ($i = 0; $i < sizeof($ids); $i++) {
					$array[$i] = array(
						"id"    => I('id')[$i],
						"img"   => I('img')[$i],
						'name'  => I('name')[$i],
						'order' => I('order')[$i],
						'link'  => I('link')[$i]
					);
	                if ($_FILES['file']) {
	                    $files = $this->reArrayFiles($_FILES['file']);
	                    if (!empty($files[$i]['name']) && !empty($files[$i]['type'])) {
	                        $_tmp = array(
	                            "file"     => $files[$i],
	                            "savePath" => "/slide/home/",
								//'w'=>400,
								//'h'=>150
	                        );
	                        $array[$i]['img'] = $this->UploadFile($_tmp);
	                    }
	                }	            
					$data['value'] = json_encode($array);
					$data['update_time']=time();
					if (isset($data['web_id'])) {
						$db->save($data);
					} else {
						$data['web_page'] = $name;
						$db->add($data);
					}
			}
		}
        $this->assign('list', $data['value']);
        $this->display();	
  }
  
  	
	function reArrayFiles(&$file_post)
	{

		$file_ary   = array();
		$file_count = count($file_post['name']);
		$file_keys  = array_keys($file_post);

		for ($i = 0; $i < $file_count; $i++) {
			foreach ($file_keys as $key) {
				$file_ary[$i][$key] = $file_post[$key][$i];
			}
		}

		return $file_ary;
	}  
}