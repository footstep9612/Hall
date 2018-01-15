<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 采购配置
 */
class PurchaseModel extends PublicModel {
    protected $dbName = 'erui_config';
    protected $tableName = 'purchase';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param string $lang
     * 采购模式名称列表-王帅
     */
    public function purchaseModeNameList($lang='zh'){
        if($lang=='zh'){
            $info=$this->field('id mode_id,name purchase_name')->where(array('type'=>1,'deleted_flag'=>'N'))->order('sort asc')->select();
        }else{
            $info=$this->field('id mode_id,en as purchase_name')->where(array('type'=>1,'deleted_flag'=>'N'))->order('sort asc')->select();
        }
        return $info;
    }
    /**
     * @param string $lang
     * 采购周期名称列表-王帅
     */
    public function purchaseCycleNameList($lang='zh'){
        if($lang=='zh'){
            $info=$this->field('id cycle_id,name purchase_name')->where(array('type'=>2,'deleted_flag'=>'N'))->order('sort asc')->select();
        }else{
            $info=$this->field('id cycle_id,en as purchase_name')->where(array('type'=>2,'deleted_flag'=>'N'))->order('sort asc')->select();
        }
        return $info;
    }
    /**
     * @param $id 采购id
     * @param string $lang  语言
     * 王帅
     */
    public function getPurchaseModelNameById($id,$lang='zh'){
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
    public function getPurchaseCycleNameById($id,$lang='zh'){
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
