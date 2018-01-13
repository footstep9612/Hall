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
        //提示仅业务信息创建成功
        $dataJson = array(
            'code'=>1,
            'message'=>'业务信息成功',
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
        //采购计划
        $purchase = new BuyerPurchasingModel();
        $purchaseRes = $purchase->showPurchase($data['buyer_id'],$data['created_by']);
        $businessRes ['purchase'] = $purchaseRes;
        //里程碑事件
        $event = new MilestoneEventModel();
        $eventRes = $event->showMilestoneEvent($data['buyer_id'],$data['created_by']);
        $businessRes ['milestone_event'] = $eventRes;
        $dataJson = array(
            'code'=>1,
            'message'=>'返回数据',
            'data'=>$businessRes,
        );
        $this -> jsonReturn($dataJson);
    }
    //贸易术语,结算方式-业务信息专用
    public function tradePaymentAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $payment = new PaymentModeModel();  //结算方式
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $pay = $payment->paymentList($lang);
        $arr['payment']=$pay;
        $tradeTerms = new TradeTermsModel();  //结算方式
        $trade = $tradeTerms->tradeList($lang);
        $arr['trade']=$trade;
        $purchase = new PurchaseModel();  //采购模式
        $purchaseInfo = $purchase->purchaseModeNameList($lang);
        $arr['purchase_mode']=$purchaseInfo;    //采购周期
        $purchaseInfo = $purchase->purchaseCycleNameList($lang);
        $arr['purchase_cycle']=$purchaseInfo;
        $credit = new CreditModel();  //结算方式
        $creditLevel = $credit->creditLevelNameList($lang);
        $creditType = $credit->creditTypeNameList($lang);
        $arr['creditLevel']=$creditLevel;
        $arr['creditType']=$creditType;
        $dataJson['code']=1;
        $dataJson['message']='结算和贸易和采购和信用配置';
        $dataJson['data']=$arr;

        $this -> jsonReturn($dataJson);
//        credit: {
//            credit_level: '',
//              credit_type: '',
//              line_of_credit: '',
//              credit_available: ''
//            },
    }
}
