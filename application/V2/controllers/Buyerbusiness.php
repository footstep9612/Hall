<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
创建客户---业务信息
 * 王帅
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

        $created_by = '39305';  //创建人
        $buyer_id = '123';
//        $created_by = $this->user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['buyer_id'] = $buyer_id;
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
}
