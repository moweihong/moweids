<?php
/**
 * 规格详细
 *
 *
 *
 *

 */
namespace Shop\Model;
use Think\Model;

class SpecValueModel extends Model {
    public function __construct() {
        parent::__construct('spec_value');
    }

    /**
     * 根据一组sp_id,查询所有相关的spec_value
     *
     * @param	array $condition
     * @return	string
     */
    public  function getSpecValueLists($condition){

        return $this->where($condition)->select();
    }




}