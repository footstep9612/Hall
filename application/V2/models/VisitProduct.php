<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
    crm客户拜访记录相关联的产品信息-wangs
 */
class VisitProductModel extends PublicModel {

    protected $tableName = 'visit_product';
    protected $dbName = 'erui_buyer'; //数据库名称

    public function __construct() {
        parent::__construct();
    }
    //修改拜访产品信息
    public function addProductInfo($product,$visit_id,$created_by){
        $productArr=[];
        foreach($product as $key => $value){
            $v=array_filter($value);
            if(!empty($v)){
                if(!empty($v['product_cate'])){
                    $v['product_cate']=implode(',',$v['product_cate']);
                }
                $productArr[$key]=$v;
                $productArr[$key]['visit_id']=$visit_id;
                $productArr[$key]['created_by']=$created_by;
                $productArr[$key]['created_at']=date('Y-m-d H:i:s');
            }
        }
        $productArr=array_values($productArr);
        if(count($productArr)<1){
            $productArr=[
                ['product_cate'=>null,'product_desc'=>null,'purchase_amount'=>null,'supplier'=>null,'remark'=>null,'visit_id'=>$visit_id,'created_by'=>$created_by,'created_at'=>date('Y-m-d H:i:s')]
            ];
        }
        return $this->addAll($productArr);
    }
    //编辑
    public function updateProductInfo($product,$visit_id,$created_by){
        $existId=[];
        $updateId=[];
        $existIds=$this->field('id')->where(array('visit_id'=>$visit_id,'deleted_flag'=>'N'))->select();
        foreach($existIds as $k => $v){
            $existId[]=$v['id'];    //存在id
        }
        $addArr=[];
        foreach($product as $key => $value){
            if(!empty($value['id'])){
                $updateId[]=$value['id'];
                unset($value['deleted_flag']);
                unset($value['visit_id']);
                if(!empty($value['product_cate'])){
                    $value['product_cate']=implode(',',$value['product_cate']);
                }
                $value['created_by']=$created_by;
                $value['created_at']=date('Y-m-d H:i:s');
                $this->where(array('id'=>$value['id']))->save($value);  //保存编辑
            }else{
                $addArr[]=$value;
            }
        }
        $delId=array_diff($existId,$updateId);
        if(!empty($delId)){
            $delStr=implode(',',$delId);
            $this->where("id in ($delStr)")->save(array('created_at'=>date('Y-m-d H:i:s'),'deleted_flag'=>'Y'));
        }
        if(!empty($addArr)){    //添加产品记录信息
            $this->addProductInfo($addArr,$visit_id,$created_by);
        }
        return true;
    }
    public function getProductInfo($visit_id){
        $cond=array(
            'visit_id'=>$visit_id,
            'deleted_flag'=>'N',
        );
        $info=$this->where($cond)->select();
        foreach($info as $k => $v){
            $info[$k]['product_cate']=explode(',',$v['product_cate']);
        }
        return $info;
    }
}
