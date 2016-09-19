<?php
namespace Admin\Controller;
use Admin\Controller\AdminController;
class ArticleController extends AdminController {
	public function __construct(){
		parent::__construct();
		
	}

	/**
	 * 文章管理
	 */
	public function articleClass(){
		$model_class = D('Articleclass');
		if(isset($_GET['type']) && $_GET['ac_id'])
		{
			switch($_GET['type'])
			{
				case 'add':
					if (!empty($_POST))
					{
						$message='';	
						$insert_array = array();
						if(!empty($_POST['ac_name']))
						{
							$insert_array['ac_name'] = trim($_POST['ac_name']);
							$insert_array['ac_parent_id'] = intval($_POST['ac_parent_id']);
							$insert_array['ac_sort'] = trim($_POST['ac_sort']);
							$result = $model_class->addrecord($insert_array);
							
						}else
						{
							$result=0;
							$message='，分类名称不能为空';
						}
						
						if ($result)
						{
							echo "<script>alert('添加成功！');window.location.href='/Admin/article/articleclass';</script>";
						}else 
						{
							$info='添加失败'.$message;
							echo "<script>alert('$info');window.location.href='/Admin/article/articleClassAdd';</script>";
						}
					}
					/**
					 * 父类列表，只取到第三级
					 */
					$parent_list = $model_class->getTreeClassList(1);
			                //print_r($parent_list);exit;
					if (is_array($parent_list)){
						foreach ($parent_list as $k => $v){
							$parent_list[$k]['ac_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['ac_name'];
						}
					}
					$data['ac_parent_id']=$_GET['ac_id'];
					$data['parent_list']=$parent_list;
					$this->assign('output',$data);
					$this->display('articleclassadd');
				break;
				case 'delete':
					$del_array = $model_class->getChildClass($_GET['ac_id']);
					//print_r($del_array);exit;
					if (is_array($del_array)){
						foreach ($del_array as $k => $v){
							$model_class->del($v['ac_id']);
						}
					}
					
				break;
				case 'edit':
					if (!empty($_POST))
					{
						$message='';	
						$insert_array = array();
						if(!empty($_POST['ac_name']))
						{
							$update_array = array();
							$update_array['ac_id'] = intval($_POST['ac_id']);
							$update_array['ac_name'] = trim($_POST['ac_name']);
							$update_array['ac_sort'] =trim($_POST['ac_sort']);
							//print_r($_POST);exit;
							$result = $model_class->update($update_array);
							
						}else
						{
							$result=0;
							$message='，分类名称不能为空';
						}
						
						if ($result)
						{
							echo "<script>alert('编辑成功！');</script>";
						}else 
						{
							$info='编辑失败'.$message;
							echo "<script>alert('$info');</script>";
						}
					}
					$class_array = $model_class->getOneClass(intval($_GET['ac_id']));	
					$this->assign('classarray',$class_array);
					$this->display('articleclassedit');
				break;
				default:
				return FALSE;
			}
				
			//echo $_GET['ac_id'];exit;
		}
		$parent_id = $_GET['ac_parent_id']?intval($_GET['ac_parent_id']):0;
		$tmp_list = $model_class->getTreeClassList(2);
		if (is_array($tmp_list)){
			foreach ($tmp_list as $k => $v){
				if ($v['ac_parent_id'] == $parent_id){
					/**
					 * 判断是否有子类
					 */
					if ($tmp_list[$k+1]['deep'] > $v['deep']){
						$v['have_child'] = 1;
					}
					$class_list[] = $v;
				}
			}
		}
		$count=count($class_list);
		$Page       = new \Think\Page($count,15);
		$show       = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show); 
		$this->assign('classlist',$class_list);
		$this->display('articleclass');
		
	}
	
	/**
	 * 文章分类 新增
	 */
	public function articleClassAdd(){
	
		$model_class = D('Articleclass');
		
		if (!empty($_POST))
		{
			$message='';	
			$insert_array = array();
			if(!empty($_POST['ac_name']))
			{
				$insert_array['ac_name'] = trim($_POST['ac_name']);
				$insert_array['ac_parent_id'] = intval($_POST['ac_parent_id']);
				$insert_array['ac_sort'] = trim($_POST['ac_sort']);
				$result = $model_class->addrecord($insert_array);
				
			}else
			{
				$result=0;
				$message='，分类名称不能为空';
			}
			
			if ($result)
			{
				echo "<script>alert('添加成功！');window.location.href='/Admin/article/articleclass';</script>";
			}else 
			{
				$info='添加失败'.$message;
				echo "<script>alert('$info');window.location.href='/Admin/article/articleClassAdd';</script>";
			}
		}
		
		/**
		 * 父类列表，只取到第三级
		 */
		
		$parent_list = $model_class->getTreeClassList(1);
		if (is_array($parent_list)){
			foreach ($parent_list as $k => $v){
				$parent_list[$k]['ac_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['ac_name'];
			}
		}
		//print_r($parent_list);exit;
		$data['ac_parent_id']=$_GET['ac_id'];
		$data['parent_list']=$parent_list;
		$this->assign('output',$data);
		$this->display('articleclassadd');
	}
	
	

	public  function getUTF8($key){
		/**
		 * 转码
		 */
		if (!empty($key)){
			if (is_array($key)){
				$result = $this->var_export($key, true);//变为字符串
				$result = $this->iconv('GBK','UTF-8',$result);
				eval("\$result = $result;");//转换回数组
			}else {
				$result = $this->iconv('GBK','UTF-8',$key);
			}
		}
		return $result;
	}
	
	public function var_export ($expression, $return = false) {}
	
	function iconv ($in_charset, $out_charset, $str) {}

	/*
	 * 文章管理
	 */
	public function articleManage(){
		
		$model_article = D('Article');
		if(isset($_GET['type']) && $_GET['article_id'])
		{
			switch($_GET['type'])
			{
				case 'add':
					if (!empty($_POST))
					{
						$message='';	
						$insert_array = array();
						if(!empty($_POST['ac_name']))
						{
							$insert_array['ac_name'] = trim($_POST['ac_name']);
							$insert_array['ac_parent_id'] = intval($_POST['ac_parent_id']);
							$insert_array['ac_sort'] = trim($_POST['ac_sort']);
							$result = $model_class->addrecord($insert_array);
							
						}else
						{
							$result=0;
							$message='，分类名称不能为空';
						}
						
						if ($result)
						{
							echo "<script>alert('添加成功！');window.location.href='/Admin/article/articleclass';</script>";
						}else 
						{
							$info='添加失败'.$message;
							echo "<script>alert('$info');window.location.href='/Admin/article/articleClassAdd';</script>";
						}
					}
					/**
					 * 父类列表，只取到第三级
					 */
					$parent_list = $model_class->getTreeClassList(1);
			                //print_r($parent_list);exit;
					if (is_array($parent_list)){
						foreach ($parent_list as $k => $v){
							$parent_list[$k]['ac_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['ac_name'];
						}
					}
					$data['ac_parent_id']=$_GET['ac_id'];
					$data['parent_list']=$parent_list;
					$this->assign('output',$data);
					$this->display('articleclassadd');
				break;
				case 'delete':
					$data['tesu_deleted']=1;
					$res=$model_article->where('article_id='.$_GET['article_id'])->save($data);
					if ($res)
					{
						echo "<script>alert('删除成功！');</script>";
					}else 
					{
						$info='删除失败';
						echo "<script>alert('$info');</script>";
					}
					
				break;
				case 'edit':
					if(!empty($_POST['act']))
					{
						//print_r($_POST);exit;
						$message='';	
						$insert_array = array();
						if(!empty($_POST['article_title']) && !empty($_POST['search_ac_id']))
						{
							$insert_array['article_title'] = trim($_POST['article_title']);
							$insert_array['article_id'] = trim($_POST['article_id']);
							$insert_array['ac_id'] = intval($_POST['search_ac_id']);
							$insert_array['ac_sort'] = intval($_POST['ac_sort']);
							$insert_array['article_show'] = intval($_POST['article_show']);
							$insert_array['article_url'] = trim($_POST['article_url']);
							$insert_array['article_content'] = trim($_POST['article_content']);
							$insert_array['article_time'] = time();
							$result = $model_article->update($insert_array);
							
						}else
						{
							$result=0;
							$message='，文章标题不能为空或所属分类不能为空';
						}
						
						if ($result)
						{
							echo "<script>alert('编辑成功！');window.location.href='/Admin/article/articlemanage';</script>";
						}else 
						{
							$info='编辑失败'.$message;
							echo "<script>alert('$info');window.location.href='/Admin/article/articlemanage';</script>";
						}
					}
					$article_array = $model_article->getOneArticle(intval($_GET['article_id']));
					//print_r($article_array);exit;
					$model_class = D('Articleclass');
					$parent_list = $model_class->getTreeClassList(2);
					if (is_array($parent_list)){
						$unset_sign = false;
						foreach ($parent_list as $k => $v){
							$parent_list[$k]['ac_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['ac_name'];
						}
					}
					$data['parent_list']=$parent_list;
					$this->assign('output',$data);
					$this->assign('article_array',$article_array);
					$this->assign('act','edit');
					$this->assign('article_id',$_GET['article_id']);
					$this->display('articleadd');
				break;
				default:
				return FALSE;
			}
				
			//echo $_GET['ac_id'];exit;
		}
		/**
		 * 检索条件
		 */
		$condition=array();
		if(isset($_GET['search_ac_id'])&& !empty($_GET['search_ac_id']))$condition['ac_id'] = intval($_GET['search_ac_id']);
		if(isset($_GET['search_title'])&&!empty($_GET['search_title']))$condition['article_title'] = trim($_GET['search_title']);
		if(!isset($_GET['p'])){$page=0;}else{$page=$_GET['p'];}
		
		/**
		 * 列表
		 */
		$condition['tesu_deleted']=0;
		$article_list = $model_article->getArticleList($condition,$page);
		//echo $model_article->getLastSql();exit;
		$count=$model_article->where($condition)->count();
		/**
		 * 分页
		 */
		$Page       = new \Think\Page($count,15);
		$show       = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show); 
		/**
		 * 整理列表内容
		 */
		if (is_array($article_list)){
			/**
			 * 取文章分类
			 */
			$model_class = D('Articleclass');
			$class_list = $model_class->getClassList($condition);
			//print_r($class_list);exit;
			$tmp_class_name = array();
			if (is_array($class_list)){
				foreach ($class_list as $k => $v){
					$tmp_class_name[$v['ac_id']] = $v['ac_name'];
				}
			}
			
			foreach ($article_list as $k => $v){
				/**
				 * 发布时间
				 */
				$article_list[$k]['article_time'] = date('Y-m-d H:i:s',$v['article_time']);
				/**
				 * 所属分类
				 */
				if (@array_key_exists($v['ac_id'],$tmp_class_name)){
					$article_list[$k]['ac_name'] = $tmp_class_name[$v['ac_id']];
				}
			}
		}
		/**
		 * 分类列表
		 */
		$model_class = D('Articleclass');
		$parent_list = $model_class->getTreeClassList(2);
		if (is_array($parent_list)){
			$unset_sign = false;
			foreach ($parent_list as $k => $v){
				$parent_list[$k]['ac_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['ac_name'];
			}
		}
		//print_r($article_list);exit;
		$data['parent_list']=$parent_list;
		$data['search_ac_id']=intval($_GET['search_ac_id']);
		$this->assign('output',$data);
		$this->assign('articlelist',$article_list);
		$this->assign('searchtitle',trim($_GET['search_title']));
		$this->display();
		//Tpl::showpage('article.index');
	}


	/**
	 * 文章添加
	 */
	public function articleAdd()
	{
		$model_articel = D('Article');
		
		if(!empty($_POST['act']))
		{
			//print_r($_POST);exit;
			if($_POST['act']=='add')
			$message='';	
			$insert_array = array();
			if(!empty($_POST['article_title']) && !empty($_POST['search_ac_id']))
			{
				$insert_array['article_title'] = trim($_POST['article_title']);
				$insert_array['ac_id'] = intval($_POST['search_ac_id']);
				$insert_array['ac_sort'] = intval($_POST['ac_sort']);
				$insert_array['article_show'] = intval($_POST['article_show']);
				$insert_array['article_url'] = trim($_POST['article_url']);
				$insert_array['article_content'] = trim($_POST['article_content']);
				$insert_array['article_time'] = time();
				$result = $model_articel->addRecord($insert_array);
				
			}else
			{
				$result=0;
				$message='，文章标题不能为空或所属分类不能为空';
			}
			
			if ($result)
			{
				echo "<script>alert('添加成功！');window.location.href='/Admin/article/articlemanage';</script>";
			}else 
			{
				$info='添加失败'.$message;
				echo "<script>alert('$info');window.location.href='/Admin/article/articleAdd';</script>";
			}
		}
		/**
		 * 分类列表
		 */
		$model_class = D('Articleclass');
		$parent_list = $model_class->getTreeClassList(2);
		if (is_array($parent_list)){
			$unset_sign = false;
			foreach ($parent_list as $k => $v){
				$parent_list[$k]['ac_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['ac_name'];
			}
		}
		//print_r($article_list);exit;
		$data['parent_list']=$parent_list;
		$this->assign('output',$data);
		$this->assign('act','add');
		$this->display();
	}

	/*
	 * 系统文章
	 */
	public function articleSys()
	{
		$model_article = D('Document');
		if(isset($_GET['type']) && $_GET['doc_id'])
		{
			switch($_GET['type'])
			{
				case 'add':
					if (!empty($_POST))
					{
						$message='';	
						$insert_array = array();
						if(!empty($_POST['ac_name']))
						{
							$insert_array['ac_name'] = trim($_POST['ac_name']);
							$insert_array['ac_parent_id'] = intval($_POST['ac_parent_id']);
							$insert_array['ac_sort'] = trim($_POST['ac_sort']);
							$result = $model_class->addrecord($insert_array);
							
						}else
						{
							$result=0;
							$message='，分类名称不能为空';
						}
						
						if ($result)
						{
							echo "<script>alert('添加成功！');window.location.href='/Admin/article/articleclass';</script>";
						}else 
						{
							$info='添加失败'.$message;
							echo "<script>alert('$info');window.location.href='/Admin/article/articleClassAdd';</script>";
						}
					}
					/**
					 * 父类列表，只取到第三级
					 */
					$parent_list = $model_class->getTreeClassList(1);
			                //print_r($parent_list);exit;
					if (is_array($parent_list)){
						foreach ($parent_list as $k => $v){
							$parent_list[$k]['ac_name'] = str_repeat("&nbsp;",$v['deep']*2).$v['ac_name'];
						}
					}
					$data['ac_parent_id']=$_GET['ac_id'];
					$data['parent_list']=$parent_list;
					$this->assign('output',$data);
					$this->display('articleclassadd');
				break;
				case 'delete':
					$data['tesu_deleted']=1;
					$res=$model_article->where('article_id='.$_GET['article_id'])->save($data);
					if ($res)
					{
						echo "<script>alert('删除成功！');</script>";
					}else 
					{
						$info='删除失败';
						echo "<script>alert('$info');</script>";
					}
					
				break;
				case 'edit':
					if(!empty($_POST['act']))
					{
						//print_r($_POST);exit;
						$message='';	
						$insert_array = array();
						if(!empty($_POST['doc_title']) && !empty($_POST['doc_content']))
						{
							$insert_array['doc_title'] = trim($_POST['doc_title']);
							$insert_array['doc_content'] = trim($_POST['doc_content']);
							$insert_array['doc_id'] = intval($_POST['article_id']);
							$insert_array['doc_time'] = time();
							$result = $model_article->update($insert_array);
							
						}else
						{
							$result=0;
							$message='，文章标题不能为空或文章内容不能为空';
						}
						
						if ($result)
						{
							echo "<script>alert('编辑成功！');window.location.href='/Admin/article/articlesys';</script>";
						}else 
						{
							$info='编辑失败'.$message;
							echo "<script>alert('$info');window.location.href='/Admin/article/articlesys';</script>";
						}
					}
					$article_array = $model_article->getOneById(intval($_GET['doc_id']));
					//print_r($article_array);exit;
					$data['parent_list']=$parent_list;
					$this->assign('article_array',$article_array);
					$this->assign('act','edit');
					$this->assign('doc_id',$_GET['doc_id']);
					$this->display('documentadd');
				break;
				default:
				return FALSE;
			}
				
			//echo $_GET['ac_id'];exit;
		}
		
		if(!isset($_GET['p'])){$page=0;}else{$page=$_GET['p'];}
		$condition['tesu_deleted']=0;
		$article_list = $model_article->getList($condition,$page);
		//echo $model_article->getLastSql();exit;
		$count=$model_article->where($condition)->count();
		/**
		 * 分页
		 */
		$Page       = new \Think\Page($count,15);
		$show       = $Page->show();
		$this->assign('count',$count);
		$this->assign('page',$show); 
		/**
		 * 整理列表内容
		 */
		if (is_array($article_list)){
			
			foreach ($article_list as $k => $v)
			{
				
				$article_list[$k]['doc_time'] = date('Y-m-d H:i:s',$v['doc_time']);
				
			}
		}
		//print_r($article_list);exit;
		$this->assign('articlelist',$article_list);
		$this->display();
		//Tpl::showpage('article.index');
	}
}