<?php
/*
 *  协议控制器
 */

namespace Shop\Controller;
use Think\Controller;
class ContractController extends Controller {
	function  __construct() {
        parent::__construct();
		$this->document=D('Document');
		
   }
	/*
	 * 注册协议
	 */
	public function zcxy(){
		$info=$this->document->getOneByCode('agreement');
		$this->assign('info',$info);
		$this->display();
	}
	/*
	 *  个人征信授权书 
	 */
	public function grzxsqs(){
		$info=$this->document->getOneByCode('grzxsqs');
		$this->assign('info',$info);
		$this->display();
	}

	/*
	 *  借款协议
	 */
	public function jkxy(){
		$info=$this->document->getOneByCode('jkxy');
		$this->assign('info',$info);
		$this->display();
	}

	/*
	 *  委托代扣协议
	 */
	public function wtdkxy(){
		$info=$this->document->getOneByCode('wtdkxy');
		$this->assign('info',$info);
		$this->display();
	}

	/*
	 *  还款承诺书
	 */
	public function hkcns(){
		$info=$this->document->getOneByCode('hkcns');
		$this->assign('info',$info);
		$this->display();

	}

	/*
	 *  购销合同
	 */
	public function gxht(){
		$info=$this->document->getOneByCode('gxht');
		$this->assign('info',$info);
		$this->display();
	
	}

	/*
	 *  发标协议
	 */
	public function fbxy(){
		
		$info=$this->document->getOneByCode('fbxy');
		$this->assign('info',$info);
		$this->display();
	}

	/*
	 * 个人信息使用授权书
	 */
	public function grxxsysqs(){
		$info=$this->document->getOneByCode('grxxsysqs');
		$this->assign('info',$info);
		$this->display();

	}

	/*
	 * 风险告知书
	 */
	public function fxgzs(){
		$info=$this->document->getOneByCode('fxgzs');
		$this->assign('info',$info);
		$this->display();
	}


}