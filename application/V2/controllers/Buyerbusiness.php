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
        parent::init();
    }
    //新建/编辑业务信息
    public function editBusinessAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this->user['id'];
        $business = new BuyerBusinessModel();
        $res = $business->editBusiness($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
        }
        $this -> jsonReturn($dataJson);
    }
    //查看业务信息
    public function showBusinessAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data['buyer_id'])){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $business = new BuyerBusinessModel();
            $res = $business->showBusiness($data);
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
            $dataJson['data'] =$res;
        }
        $this -> jsonReturn($dataJson);
    }
    //新建/编辑结算基本信息==============================================================================
    public function editSettlementAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this->user['id'];
        $business = new BuyerBusinessModel();
        $res = $business->editSettlement($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
        }
        $this -> jsonReturn($dataJson);
    }
    //查看结算基本信息
    public function showSettlementAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $business = new BuyerBusinessModel();
        $res = $business->showSettlement($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
            $dataJson['data'] =$res;
        }
        $this -> jsonReturn($dataJson);
    }
    //里程牌事件==============================================================
    public function editMilestoneEventAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this->user['id'];
        $event = new MilestoneEventModel();
        $res = $event->editMilestoneEvent($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
        }
        $this -> jsonReturn($dataJson);
    }
    //采购里程碑事件
    public function delMilestoneEventAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] =  $this->user['id'];
        $event = new MilestoneEventModel();
        $res = $event->delMilestoneEvent($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
        }
        $this -> jsonReturn($dataJson);
    }
    //查看里程碑事件
    public function showMilestoneEventAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data['id'])){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $event = new MilestoneEventModel();
            $res = $event->showMilestoneEvent($data);
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
            $dataJson['data'] =$res;
        }
        $this -> jsonReturn($dataJson);
    }
    //查看里程碑事件
    public function MilestoneEventListAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data['buyer_id'])){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $event = new MilestoneEventModel();
            $res = $event->MilestoneEventList($data);
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
            $dataJson['data'] =$res;
        }
        $this -> jsonReturn($dataJson);
    }
    //采购计划列表===========================================================================
    public function showPurchaseListAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this->user['id'];
        $model = new BuyerPurchasingModel();
        $res = $model->showPurchaseList($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='数据信息';
            $dataJson['data'] =$res;
        }
        $this -> jsonReturn($dataJson);
    }
    //采购计划列表
    public function editPurchaseAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this->user['id'];
        $model = new BuyerPurchasingModel();
        $res = $model->editPurchase($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
        }
        $this -> jsonReturn($dataJson);
    }
    //采购计划列表
    public function showPurchaseAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerPurchasingModel();
        $res = $model->showPurchase($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
            $dataJson['data'] =$res;
        }
        $this -> jsonReturn($dataJson);
    }
    //采购计划列表
    public function delPurchaseAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] =  $this->user['id'];
        $model = new BuyerPurchasingModel();
        $res = $model->delPurchase($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
        }
        $this -> jsonReturn($dataJson);
    }
    //入网管理===================================================================================
    public function showNetSubjectAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $net = new NetSubjectModel();
        $res = $net->showNetSubject($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
            $dataJson['data'] =$res;
        }
        $this -> jsonReturn($dataJson);
    }
    public function editNetSubjectAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] =  $this->user['id'];
        $net = new NetSubjectModel();
        $res = $net->editNetSubject($data);
        if($res===false){
            $dataJson['code'] =0;
            $dataJson['message'] ='参数错误';
        }else{
            $dataJson['code'] =1;
            $dataJson['message'] ='成功';
        }
        $this -> jsonReturn($dataJson);
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
        //信用
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $buyer_credit = new BuyerModel();
        $credit = $buyer_credit->showBuyerCredit($data['buyer_id']);
        if($data['is_check']==true){    //查看
            if(!empty($credit['payment_behind'])){  //拖欠货款
                if($lang=='zh'){
                    $credit['payment_behind']=$credit['payment_behind']=='Y'?'是':'否';
                }else{
                    $credit['payment_behind']=$credit['payment_behind']=='Y'?'YES':'NO';
                }
            }
            if(!empty($credit['violate_treaty'])){  //拖欠货款
                if($lang=='zh'){
                    $credit['violate_treaty']=$credit['violate_treaty']=='Y'?'是':'否';
                }else{
                    $credit['violate_treaty']=$credit['violate_treaty']=='Y'?'YES':'NO';
                }
            }
        }
        $businessRes ['credit'] = $credit;
        //分析报告
        $attach = new BuyerattachModel();
        $org_chart = $attach->showBuyerExistAttach('REPORT',$data['buyer_id'],$data['created_by']);
        if(!empty($org_chart)){
            $businessRes['report_attach'] = $org_chart;
        }else{
            $businessRes['report_attach'] = array();
        }
        //采购计划附件
//        $attach = new PurchasingAttachModel();
//        $attacheRes = $attach->showPurchaseAttach($data['buyer_id'],$data['created_by']);
//        if(!empty($attacheRes)){
//            $businessRes ['purchase_attach'] = $attacheRes;
//        }else{
//            $businessRes ['purchase_attach'] = array();
//        }
        //采购计划
        $purchase = new BuyerPurchasingModel();
        $purchaseRes = $purchase->showPurchase($data['buyer_id'],$data['created_by']);
        if(empty($purchaseRes)){
            $purchaseRes=[
                array('purchasing_at'=>null,'purchasing_budget'=>null,'purchasing_plan'=>null,'attach_name'=>null,'attach_url'=>null)
            ];
        }
        $businessRes ['purchase'] = $purchaseRes;
        //里程碑事件
        $event = new MilestoneEventModel();
        $eventRes = $event->showMilestoneEvent($data['buyer_id'],$data['created_by']);
        if(empty($eventRes)){
            $eventRes=[
                array('event_time'=>null,'event_name'=>null,'event_content'=>null,'event_contact'=>null)
            ];
        }
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
        $tradeTerms = new TradeTermsModel();  //贸易术语
        $trade = $tradeTerms->tradeList($lang);
        $arr['trade']=$trade;
        $purchase = new PurchaseModel();  //采购模式
        $purchaseInfo = $purchase->purchaseModeNameList($lang);
        $arr['purchase_mode']=$purchaseInfo;    //采购周期
        $purchaseInfo = $purchase->purchaseCycleNameList($lang);
        $arr['purchase_cycle']=$purchaseInfo;
        $credit = new CreditModel();  //信用
        $creditType = $credit->creditTypeNameList($lang);
//        $creditLevel = $credit->creditLevelNameList($lang);
//        $arr['creditLevel']=$creditLevel;
        $arr['creditType']=$creditType;
        $dataJson['code']=1;
        $dataJson['message']='结算和贸易和采购和信用配置';
        $dataJson['data']=$arr;

        $this -> jsonReturn($dataJson);
    }
    public function gradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['lang']=$this->getLang();
        $payment = new PaymentModeModel();  //结算方式
        $info = $payment->table('erui_config.buyer_grade')->field('id,type,name')->where(array('deleted_flag'=>'N'))->select();
        $arr=[];
        foreach($info as $k => $v){
            if($v['type']==1){
                unset($v['type']);
                $arr['position'][]=$v;
            }else if($v['type']==2){
                unset($v['type']);
                $arr['enterprise'][]=$v;
            }else if($v['type']==3){
                unset($v['type']);
                $arr['year_keep'][]=$v;
            }else if($v['type']==4){
                unset($v['type']);
                $arr['re_purchase'][]=$v;
            }else if($v['type']==5){
                unset($v['type']);
                $arr['credit'][]=$v;
            }
        }
        $dataJson['code']=1;
        $dataJson['message']='客户分级配置数据';
        $dataJson['data']=$arr;
        $this -> jsonReturn($dataJson);
    }
    public function buyerGradeListAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['lang']=$this->getLang();
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->buyerGradeList($data);
//        $res=$model->addBuyerGrade($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='客户分级列表数据';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    public function addGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->addGrade($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='成功';
        }else{
            $dataJson['code']=0;
            $dataJson['message']='失败';
        }
        $this -> jsonReturn($dataJson);
    }
}
