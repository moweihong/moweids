<?php
/**
 * 物流模型
 */
namespace Shop\Model;
use Think\Model;

class ExpressModel extends Model {


    public function __construct()
    {
        parent::__construct('express');
    }

    //获取一条物流公司的信息
    public  function  getOneExpressInfo($conditon)
    {
        return $this->where($conditon)->find();
    }

}