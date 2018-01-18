<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 客户类型名称
 */
class BuyerTypeModel extends PublicModel {
    protected $dbName = 'erui_config';
    protected $tableName = 'buyer_type';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param string $lang
     * 客户类型列表-王帅
     */
    public function buyerNameList($lang='zh'){
        $cond=array(
            'deleted_flag'=>'N'
        );
        $currency=new CurrencyModel();
        if($lang=='zh'){
            $type=$this->field('id type_id,name type_name')->where(array('deleted_flag'=>'N'))->order('sort asc')->select();
            $list=$currency->field('bn as currency_bn,name as currency_name')->where($cond)->select();
        }else{
            $type=$this->field('id type_id,en as type_name')->where(array('deleted_flag'=>'N'))->order('sort asc')->select();
            $list=$currency->field('bn as currency_bn,en_name as currency_name')->where($cond)->select();
        }
            $arr['type']=$type;
            $arr['currency']=$list;
        return $arr;
    }

    /**
     * @param $id 客户类型id
     * @param string $lang  语言
     * 王帅
     */
    public function buyerTypeNameById($id,$lang='zh'){
        $cond=array(
            'id'=>$id,
            'deleted_flag'=>'N'
        );
        if($lang=='zh'){
            $name=$this->field('id type_id,name type_name')->where($cond)->find();
        }else{
            $name=$this->field('id type_id,en type_name')->where($cond)->find();
        }
        return $name;
    }
}
