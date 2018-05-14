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
                if(!is_numeric($value['purchase_amount'])){
                    $v['purchase_amount']=0;
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
                unset($value['product_cate_name']);
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
    public function getProductInfo($visit_id,$field='*',$check=false,$lang='zh'){
        $cond=array(
            'visit_id'=>$visit_id,
            'deleted_flag'=>'N',
        );
        $info=$this->field($field)->where($cond)->select();
        foreach($info as $k => $v){
            if(!empty($v['product_cate'])){
                $cateArr=explode(',',$v['product_cate']);
            }else{
                $cateArr=[];
            }
            if(empty($cateArr)){
                $info[$k]['product_cate']='';
            }elseif(in_array(0,$cateArr)){
                $cond=array('cat_no'=>$cateArr[0],'lang'=>$lang);
                $material=$this->table('erui_goods.material_cat')->field('name')->where($cond)->find();
                if($lang=='zh'){
                    $info[$k]['product_cate']=$material['name']."/其他";
                }else{
                    $info[$k]['product_cate']=$material['name']."/Others";
                }
            }else{
                $a=$cateArr[0];
                $b=$cateArr[1];
                $cond="(cat_no='$a' or cat_no='$b') and lang='$lang'";
                $material=$this->table('erui_goods.material_cat')->field('name')->where($cond)->select();
                $info[$k]['product_cate']=$material[0]['name'].'/'.$material[1]['name'];
            }
            $info[$k]['product_cate_name']=$cateArr;
            if(empty($info[$k]['product_desc'])){
                $info[$k]['product_desc']='';
            }
            if(empty($info[$k]['purchase_amount'])){
                $info[$k]['purchase_amount']='';
            }
            if(empty($info[$k]['supplier'])){
                $info[$k]['supplier']='';
            }
            if(empty($info[$k]['remark'])){
                $info[$k]['remark']='';
            }
        }
        if($check==true){
            return $info;
        }
        //获取品类名称
        foreach($info as $k => $v){
            $info[$k]['product_cate_name']=explode('/',$v['product_cate']);
            $info[$k]['product_cate']=$v['product_cate_name'];
        }
        return $info;
    }
    public function getProductName($visit_id,$lang='zh'){
        $info=$this->getProductInfo($visit_id,'product_cate');
        if(empty($info[0]['product_cate'][0])){
            return null;
        }
        $arr=[];
        foreach($info as $k => $v){
            $cond=array('cat_no'=>$v['product_cate'][0],'lang'=>$lang);
            if(in_array(0,$v['product_cate'])){
                $material=$this->table('erui_goods.material_cat')->field('name')->where($cond)->find();
                if($lang=='zh'){
                    $arr[]=$material['name']."/其他\n";
                }else{
                    $arr[]=$material['name']."/Others\n";
                }
            }else{
                $a=$v['product_cate'][0];
                $b=$v['product_cate'][1];
                $cond="(cat_no='$a' or cat_no='$b') and lang='$lang'";
                $material=$this->table('erui_goods.material_cat')->field('name')->where($cond)->select();
                $arr[]=$material[0]['name'].'/'.$material[1]['name']."\n";
            }
        }
        $product_cate='';
        foreach($arr as $k => $v){
            $product_cate.=($k+1).'.'.$v;
        }
        return $product_cate;
    }
    //拜访客户获取品类信息多记录
    public function getProductArr($visit_id,$lang='zh'){
        $info=$this->getProductInfo($visit_id,'product_cate,product_desc,purchase_amount,supplier,remark');
        if(empty($info[0]['product_cate'][0])){
            return null;
        }
        foreach($info as $k => $v){
            $cond=array('cat_no'=>$v['product_cate'][0],'lang'=>$lang);
            if(in_array(0,$v['product_cate'])){
                $material=$this->table('erui_goods.material_cat')->field('name')->where($cond)->find();
                if($lang=='zh'){
                    $info[$k]['product_cate']=$material['name'].'/其他';
                }else{
                    $info[$k]['product_cate']=$material['name'].'/Others';
                }
            }else{
                $a=$v['product_cate'][0];
                $b=$v['product_cate'][1];
                $cond="(cat_no='$a' or cat_no='$b') and lang='$lang'";
                $material=$this->table('erui_goods.material_cat')->field('name')->where($cond)->select();
                $info[$k]['product_cate']=$material[0]['name'].'/'.$material[1]['name'];
            }
            $info[$k]="\n".($k+1).':'.implode('  |  ',$info[$k]);
        }
        return implode('',$info);
    }
}
