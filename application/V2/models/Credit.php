<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 采购配置
 */
class CreditModel extends PublicModel {
    protected $dbName = 'erui_config';
    protected $tableName = 'credit';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param string $lang
     * 采购模式名称列表-王帅
     */
    public function creditLevelNameList($lang='zh'){
        if($lang=='zh'){
            $info=$this->field('id credit_id,name credit_name')->where(array('type'=>1,'deleted_flag'=>'N'))->order('sort asc')->select();
        }else{
            $info=$this->field('id credit_id,en as credit_name')->where(array('type'=>1,'deleted_flag'=>'N'))->order('sort asc')->select();
        }
        return $info;
    }
    /**
     * @param string $lang
     * 采购周期名称列表-王帅
     */
    public function creditTypeNameList($lang='zh'){
        if($lang=='zh'){
            $sql="select id type_id,name credit_type ";
        }else{
            $sql="select id type_id,en credit_type ";
        }
        $sql.="from erui_config.credit";
        $sql.=" where pid=0 and deleted_flag='N' order by sort";
        $type=$this->query($sql);
        foreach($type as $k => $v){
            $level="select id level_id,en level_name from erui_config.credit where pid=$v[type_id] and deleted_flag='N' ORDER BY sort";
            $name=$this->query($level);
            $type[$k]['credit_level']=$name;
        }
        return $type;
    }
    /**
     * @param $id 采购id
     * @param string $lang  语言
     * 王帅
     */
    public function getCreditLevelNameById($id,$lang='zh'){
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
    public function getCreditTpeNameById($id,$lang='zh'){
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
