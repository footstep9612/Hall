<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *采购计划附件-王帅

 */
class PurchasingAttachModel extends PublicModel {

    protected $dbName = 'erui_buyer';
    protected $tableName = 'purchasing_attach';


    public function __construct() {
        parent::__construct();
    }

    /**
     * @param $data
     * 编辑基本信息,删除财务报表+++++++++++++++++
     */
    public function delPurchasingAttach($buyer_id,$created_by){
        $cond = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
            'attach_group'=>'PURCHASING',
            'deleted_flag'=>'N'
        );
        $exist=$this->field('id')->where($cond)->select();
        if($exist){
            $this->where($cond)->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));
        }
        return true;
    }

    /**
     * @param $attach
     * 创建采购计划,多文件添加+++++++++++++++++++++
     * wangs
     */
    public function CreatePurchasingAttach($attach,$buyer_id,$created_by){
        foreach($attach as $key => $value){
            $value['buyer_id']=$buyer_id;
            $value['created_by']=$created_by;
            $value['attach_group']='PURCHASING';
            $value['created_at']=date('Y-m-d H:i:s');
            $this->add($value);
        }
        return true;
    }

    /**
     * @param $attach财务报表附件arr
     * @param $buyer_id客户id
     * @param $created_by创建人
     */
    public function updatePurchasingAttach($attach,$buyer_id,$created_by){
        //财务表中记录的id
        $cond = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
            'attach_group'=>'PURCHASING',
            'deleted_flag'=>'N'
        );
//        if(empty($attach[0]['attach_url'])){
//            $this->where($cond)->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));
//            return true;
//        }
        $existId = $this->field('id')->where($cond)->select();
        $arrId=$this->packageId($existId);
        //编辑的传过来的id
        $attachId=$this->packageId($attach);
        $extra=array_diff($arrId,$attachId);    //额外id
        if(!empty($extra)){
            $strId=implode(',',$extra);
            $this->where("id in ($strId)")->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));  //删除
        }
        //添加
        foreach($attach as $key => $value){
            if(empty($value['id'])){
                $value['buyer_id']=$buyer_id;
                $value['created_by']=$created_by;
                $value['attach_group']='PURCHASING';
                $value['created_at']=date('Y-m-d H:i:s');
                $this->add($value);
            }
        }
        return true;
    }
    public function packageId($data){
        $arr=array();
        foreach($data as $k => $v){
            if(!empty($v['id'])){
                $arr[]=$v['id'];
            }
        }
        return $arr;
    }
    /*
     * 创建财务报表
     * attach_name,attach_url
     * wangs
     */
    public function createBuyerFinanceTable($data){
        $attach_name = $data['attach_name'];
        $attach_url = $data['attach_url'];
        $buyer_id = $data['buyer_id'];
        $created_by = $data['created_by'];
        $cond = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
            'attach_group'=>'FINANCE',
            'deleted_flag'=>'N',
        );
        $exist = $this->where($cond)->find();
        if(!empty($exist)){
            $this->where($cond)->save(array('deleted_flag'=>'Y'));
        }
        $arr = array(
            'buyer_id'=>$buyer_id,
            'attach_group'=>'FINANCE',
            'attach_name'=>$attach_name,
            'attach_url'=>$attach_url,
            'created_by'=>$created_by,
            'created_at'=>date('Y-m-d H:i:s'),
        );
        $res = $this -> add($arr);
        if($res){
            return true;
        }
        return false;
    }

    //按条件客户id，创建人,查询附件
    public function showPurchaseAttach($buyer_id,$created_by){
        $cond=array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
            'attach_group'=>'PURCHASING',
            'deleted_flag'=>'N',
        );
        return $this->field('id,attach_name,attach_url')->where($cond)->select();
    }
}
