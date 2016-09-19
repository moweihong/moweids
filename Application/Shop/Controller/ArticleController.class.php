<?php
/*
 * 文章管理
 */

namespace Shop\Controller;
use Think\Controller;
class ArticleController extends Controller {

	/*
	 *文章展示
     */
	public function index()
	{
		$ac_id=intval($_GET['ac_id']);
		$footlist=D('ArticleClass')->getFootList();
		$articlelist=D('Article')->getArticleListNew($ac_id);
		//print_r($articlelist);exit;
		$this->assign('articlelist',$articlelist);
		$this->assign('footlist',$footlist);
		$this->assign('ac_id',$ac_id);
		$this->display();
	}
	
}