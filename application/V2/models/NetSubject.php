<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *入网主题-王帅
 */
class NetSubjectModel extends PublicModel {
    protected $dbName = 'erui_buyer';
    protected $tableName = 'net_subject';

    public function __construct() {
        
        parent::__construct();
    }
    private function equipmentNetSubject($data){
        $field=array(
            'subject_name as equipment_subject_name', //入网主题简称
            'net_at as equipment_net_at', //入网时间
            'net_invalid_at as equipment_net_invalid_at', //失效时间
            'net_goods as equipment_net_goods' //入网商品
        );
        $cond=array('buyer_id'=>$data['buyer_id'],'subject_name'=>'equipment','deleted_flag'=>'N');
        $info=$this->field($field)->where($cond)->find();

        if(!empty($info)){
//            foreach($info as $k => &$v){
//                if(empty($v) || $v==0){
//                    $v='';
//                }
//            }
        }else{
            $info=[];
            foreach($field as $k => $v){
                $info[$v]='';
            }
        }
        return $info;
    }
    private function eruiNetSubject($data){
        $field=array(
            'subject_name as erui_subject_name', //入网主题简称
            'net_at as erui_net_at', //入网时间
            'net_invalid_at as erui_net_invalid_at', //失效时间
            'net_goods as erui_net_goods' //入网商品
        );
        $cond=array('buyer_id'=>$data['buyer_id'],'subject_name'=>'erui','deleted_flag'=>'N');
        $info=$this->field($field)->where($cond)->find();

        if(!empty($info)){
//            foreach($info as $k => &$v){
//                if(empty($v) || $v==0){
//                    $v='';
//                }
//            }
        }else{
            $info=[];
            foreach($field as $k => $v){
                $info[$v]='';
            }
        }
        return $info;
    }
    public function percentNetSubject($data){
        $equipment=$this->equipmentNetSubject($data);
        $erui=$this->eruiNetSubject($data);
        return array_merge($equipment,$erui);
    }
    /**
     * @param string $lang
     * 采购模式名称列表-王帅
     */
    public function addSubject($equipment,$erui,$buyer_id,$created_by){
        $arr=[
            array(
                'buyer_id'=>$buyer_id, //主题名称
                'subject_name'=>$equipment['subject_name'], //主题名称
                'net_at'=>!empty($equipment['net_at'])?$equipment['net_at']:null, //入网时间
                'net_invalid_at'=>!empty($equipment['net_invalid_at'])?$equipment['net_invalid_at']:null, //入网失效时间
                'net_goods'=>$equipment['net_goods'], //入网商品
                'created_by'=>$created_by, //入网商品
                'created_at'=>date('Y-m-d H:i:s'), //入网商品
            ),
            array(
                'buyer_id'=>$buyer_id, //主题名称
                'subject_name'=>$erui['subject_name'], //主题名称
                'net_at'=>!empty($erui['net_at'])?$erui['net_at']:null, //入网时间
                'net_invalid_at'=>!empty($erui['net_invalid_at'])?$erui['net_invalid_at']:null, //入网失效时间
                'net_goods'=>$erui['net_goods'], //入网商品
                'created_by'=>$created_by, //入网商品
                'created_at'=>date('Y-m-d H:i:s'), //入网商品
            )
        ];
        $this->addAll($arr);
        return true;
    }
    public function updateSubject($equipment,$erui,$buyer_id,$created_by){
        $equipmentArr=array(
            'buyer_id'=>$buyer_id, //主题名称
            'subject_name'=>$equipment['subject_name'], //主题名称
            'net_at'=>!empty($equipment['net_at'])?$equipment['net_at']:null, //入网时间
            'net_invalid_at'=>!empty($equipment['net_invalid_at'])?$equipment['net_invalid_at']:null, //入网失效时间
            'net_goods'=>$equipment['net_goods'], //入网商品
            'created_by'=>$created_by, //入网商品
            'created_at'=>date('Y-m-d H:i:s'), //入网商品
        );
        $eruiArr=array(
            'buyer_id'=>$buyer_id, //主题名称
            'subject_name'=>$erui['subject_name'], //主题名称
            'net_at'=>!empty($erui['net_at'])?$erui['net_at']:null, //入网时间
            'net_invalid_at'=>!empty($erui['net_invalid_at'])?$erui['net_invalid_at']:null, //入网失效时间
            'net_goods'=>$erui['net_goods'], //入网商品
            'created_by'=>$created_by, //入网商品
            'created_at'=>date('Y-m-d H:i:s'), //入网商品
        );
        $equipmentCond=array('buyer_id'=>$buyer_id,'subject_name'=>$equipment['subject_name'],'deleted_flag'=>'N');
        $eruiCond=array('buyer_id'=>$buyer_id,'subject_name'=>$erui['subject_name'],'deleted_flag'=>'N');
        $equipmentExist=$this->where($equipmentCond)->find();
        $eruiExist=$this->where($eruiCond)->find();
        if(!empty($equipmentExist)){
            $this->where($equipmentCond)->save($equipmentArr);
        }else{
            $this->add($equipmentArr);
        }
        if(!empty($eruiExist)){
            $this->where($eruiCond)->save($eruiArr);
        }else{
            $this->add($eruiArr);
        }
        return true;
    }
    public function getNetSubject($buyer_id){
        $cond=array(
            'buyer_id'=>$buyer_id,
            'deleted_flag'=>'N'
        );
        $field=array(
            'subject_name', //入网主题简称
            'net_at', //入网时间
            'net_invalid_at', //失效时间
            'net_goods' //入网商品
        );
        $arr=array();
        $info=$this->field($field)->where($cond)->select();
        foreach($info as $k => $v){
            $kk=$v['subject_name'];
            unset($v['subject_name']);
            $arr[$kk]=$v;
        }
        return $arr;
    }
    public function showNetSubject($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        $cond=array(
            'buyer_id'=>$data['buyer_id'],
            'deleted_flag'=>'N'
        );
        $field=array(
            'id', //入网主题简称
            'flag',
            'subject_name', //入网主题简称
            'net_at', //入网时间
            'net_invalid_at', //失效时间
            'net_goods' //入网商品
        );
        $arr=array();
        $info=$this->field($field)->where($cond)->select();
        if(!empty($info)){
            foreach($info as $k => &$v){
                $kk=$v['subject_name'];
                unset($v['subject_name']);
                $arr[$kk]=$v;
            }
        }else{
            $arr=array(
                'equipment'=>array('flag'=>'N','net_at'=>'','net_invalid_at'=>'','net_goods'=>''),
                'erui'=>array('flag'=>'N','net_at'=>'','net_invalid_at'=>'','net_goods'=>'')
            );
        }
        $arr['equipment_flag']=$arr['equipment']['flag'];
        $arr['erui_flag']=$arr['erui']['flag'];
        return $arr;
    }
    public function editNetSubject($data){
        if(empty($data['equipment']['id']) && empty($data['erui']['id'])){
            $this->addNetSubject($data);
            return true;
        }else{
            $this->updateNetSubject($data);
            return true;
        }
    }
    public function addNetSubject($data){
        $equipment_flag=$data['equipment_flag'];
        $erui_flag=$data['erui_flag'];

        $equipment=$data['equipment'];
        $erui=$data['erui'];
        $buyer_id=$data['buyer_id'];
        $created_by=$data['created_by'];
        if($equipment_flag=='N'){
            $equipment=[];
        }
        if($erui_flag=='N'){
            $erui=[];
        }
        $arr=[
            array(
                'flag'=>$equipment_flag,
                'buyer_id'=>$buyer_id, //主题名称
                'subject_name'=>'equipment', //主题名称
                'net_at'=>!empty($equipment['net_at'])?$equipment['net_at']:null, //入网时间
                'net_invalid_at'=>!empty($equipment['net_invalid_at'])?$equipment['net_invalid_at']:null, //入网失效时间
                'net_goods'=>$equipment['net_goods'], //入网商品
                'created_by'=>$created_by, //入网商品
                'created_at'=>date('Y-m-d H:i:s'), //入网商品
            ),
            array(
                'flag'=>$erui_flag,
                'buyer_id'=>$buyer_id, //主题名称
                'subject_name'=>'erui', //主题名称
                'net_at'=>!empty($erui['net_at'])?$erui['net_at']:null, //入网时间
                'net_invalid_at'=>!empty($erui['net_invalid_at'])?$erui['net_invalid_at']:null, //入网失效时间
                'net_goods'=>$erui['net_goods'], //入网商品
                'created_by'=>$created_by, //入网商品
                'created_at'=>date('Y-m-d H:i:s'), //入网商品
            )
        ];
        $info=$this->where(array('buyer_id'=>$buyer_id,'deleted_flag'=>'N'))->select();
        if(!empty($info)){
            $this->where(array('buyer_id'=>$buyer_id,'deleted_flag'=>'N'))->save(array('deleted_flag'=>'Y'));
        }
        $this->addAll($arr);
        return true;
    }
    public function updateNetSubject($data){
        $equipment=$data['equipment'];
        $erui=$data['erui'];

        $equipment_flag=$data['equipment_flag'];
        $erui_flag=$data['erui_flag'];

        if($equipment_flag=='N'){
            $equipment=[];
        }
        if($erui_flag=='N'){
            $erui=[];
        }

        $buyer_id=$data['buyer_id'];
        $created_by=$data['created_by'];
        $equipmentArr=array(
            'flag'=>$equipment_flag,
            'buyer_id'=>$buyer_id, //主题名称
            'subject_name'=>'equipment', //主题名称
            'net_at'=>!empty($equipment['net_at'])?$equipment['net_at']:null, //入网时间
            'net_invalid_at'=>!empty($equipment['net_invalid_at'])?$equipment['net_invalid_at']:null, //入网失效时间
            'net_goods'=>$equipment['net_goods'], //入网商品
            'created_by'=>$created_by, //入网商品
            'created_at'=>date('Y-m-d H:i:s'), //入网商品
        );
        $eruiArr=array(
            'flag'=>$erui_flag,
            'buyer_id'=>$buyer_id, //主题名称
            'subject_name'=>'erui', //主题名称
            'net_at'=>!empty($erui['net_at'])?$erui['net_at']:null, //入网时间
            'net_invalid_at'=>!empty($erui['net_invalid_at'])?$erui['net_invalid_at']:null, //入网失效时间
            'net_goods'=>$erui['net_goods'], //入网商品
            'created_by'=>$created_by, //入网商品
            'created_at'=>date('Y-m-d H:i:s'), //入网商品
        );
        $equipmentCond=array('buyer_id'=>$buyer_id,'subject_name'=>'equipment','deleted_flag'=>'N');
        $eruiCond=array('buyer_id'=>$buyer_id,'subject_name'=>'erui','deleted_flag'=>'N');
        $equipmentExist=$this->where($equipmentCond)->find();
        $eruiExist=$this->where($eruiCond)->find();
        if(!empty($equipmentExist)){
            $this->where($equipmentCond)->save($equipmentArr);
        }else{
            $this->add($equipmentArr);
        }
        if(!empty($eruiExist)){
            $this->where($eruiCond)->save($eruiArr);
        }else{
            $this->add($eruiArr);
        }
        return true;
    }
}
