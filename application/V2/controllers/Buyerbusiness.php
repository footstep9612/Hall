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
     * 创建客户---业务信息
     * */
    public function createBusinessAction()
    {

        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $business = new BuyerBusinessModel();
        $businessRes = $business->createBusiness($data);
        if(!empty($data['purchase'])){
            $purchase = new BuyerPurchasingModel();
            $purchaseRes = $purchase->createPurchase($data['purchase'],$data['buyer_id'],$data['created_by']);
        }
        if(!$businessRes || !$purchaseRes){
            echo json_encode(array("code" => "0", "message" => "创建失败"));
            exit();
        }
        echo json_encode(array("code" => "1","message" => "创建成功"));
    }
    //展示客户业务信息
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
        echo json_encode(array("code" => "1","message" => "返回成功","data" => $businessRes));
    }
}
