<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
客户管理---业务信息--王帅
 */
class BuyerbusinessController extends PublicController
{
    public function __init()
    {
        parent::__init();
    }
    /*
     * 创建客户---业务信息及采购计划，附件
     * wangs
     * */
    public function createBusinessAction()
    {
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $business = new BuyerBusinessModel();
        $businessRes = $business->createBusiness($data);
        if($businessRes == false){
            $dataJson = array(
                'code'=>0,
                'message'=>'请保证数据长度及时间有效性',
            );
            $this -> jsonReturn($dataJson);
        }
        //采购计划
        if(!empty($data['purchase'])){
            $purchase = new BuyerPurchasingModel();
            $purchaseRes = $purchase->createPurchase($data['purchase'],$data['buyer_id'],$data['created_by']);
            if($purchaseRes === false){
                $dataJson = array(
                    'code'=>1,
                    'message'=>'业务信息创建成功，采购计划创建失败',
                );
                $this -> jsonReturn($dataJson);
            }
            //采购计划附件
            if(!empty($purchaseRes)){
                $purchase = new BuyerattachModel();
                $purchaseResult = $purchase->createBuyerPurchaseTable($purchaseRes,$data['buyer_id'],$data['created_by']);
                if($purchaseResult == false){
                    $dataJson = array(
                        'code'=>1,
                        'message'=>'业务信息，采购计划创建成功，采购计划附件失败',
                    );
                }else{
                    $dataJson = array(
                        'code'=>1,
                        'message'=>'业务信息，采购计划，采购计划附件创建成功',
                    );
                }
                $this -> jsonReturn($dataJson);
            }else{
                $dataJson = array(
                    'code'=>1,
                    'message'=>'业务信息，采购计划，创建成功',
                );
                $this -> jsonReturn($dataJson);
            }
        }
        //提示仅业务信息创建成功
        $dataJson = array(
            'code'=>1,
            'message'=>'业务信息创建成功,采购计划为空',
        );
        $this -> jsonReturn($dataJson);

    }

    /**
     * 展示客户业务信息详情
     * wagns
     */
    public function businessListAction(){
        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $business = new BuyerBusinessModel();
        $businessRes = $business->businessList($data);
        $purchase = new BuyerPurchasingModel();
        $purchaseRes = $purchase->showPurchase($data['buyer_id'],$data['created_by']);
        if(!empty($purchaseRes)){
            $businessRes ['purchase'] = $purchaseRes;
        }
        $dataJson = array(
            'code'=>1,
            'message'=>'返回数据',
            'data'=>$businessRes,
        );
        $this -> jsonReturn($dataJson);
    }
}
