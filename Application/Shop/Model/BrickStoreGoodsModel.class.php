<?php
namespace Shop\Model;
use Think\Model;

class BrickStoreGoodsModel extends Model
{
    public function __construct() {
        parent::__construct('brick_store_goods');
    }
    
    public function addrecode($recode){
        return $this->insert($recode);
    }
}

?>