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
        $lang=$this->getLang();
        $model = new CustomerGradeModel();  //结算方式
        if($lang=='zh'){
            $field='id,type,grade_no,name';
        }else{
            $field='id,type,grade_no,name_en as name';
        }
        $info = $model->table('erui_config.buyer_grade')
            ->field($field)
            ->where(array('deleted_flag'=>'N'))
            ->order('sort asc')
            ->select();
        $arr=[];
        foreach($info as $k => $v){
            if($v['type']==1){
                unset($v['id']);
                unset($v['type']);
                $arr['position'][]=$v;
            }else if($v['type']==2){
                unset($v['id']);
                unset($v['type']);
                $arr['enterprise'][]=$v;
            }else if($v['type']==3){
                unset($v['id']);
                unset($v['type']);
                $arr['year_keep'][]=$v;
            }else if($v['type']==4){
                unset($v['id']);
                unset($v['type']);
                $arr['re_purchase'][]=$v;
            }else if($v['type']==5){
                unset($v['id']);
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
        $data['role']=$this->user['role_no'];
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->buyerGradeList($data);
        if(is_array($res)){
            $dataJson['code']=1;
            $dataJson['message']='客户分级列表数据';
            if(!in_array('customer_agent',$data['role'])){
                $dataJson['old_button']=false;
                $dataJson['new_button']=false;
            }else{
                $dataJson['old_button']=true;
                $dataJson['new_button']=true;
            }
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    public function exportGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['lang']=$this->getLang();
        $data['role']=$this->user['role_no'];
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->exportExcelGrade($data);
        if(count($res)==0){
            $dataJson['code']=4;
            $dataJson['message']='暂无数据';
        }elseif(count($res)>0){
            $dataJson['code']=1;
            $dataJson['message']='客户分级导出';
            $dataJson['url']=$res['url'];
            $dataJson['name']=$res['name'];
        }else{
            $dataJson['code']=0;
            $dataJson['message']='导出失败';
        }
        $this -> jsonReturn($dataJson);
    }

    //客户历史成单金额1-----------------------------------------------
    public function amountAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->amount($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='客户历史成单金额';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //易瑞产品采购量占客户总需求量地位2
    public function positionAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->position($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='易瑞产品采购量占客户总需求量地位';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //连续N年及以上履约状况良好3
    public function yearKeepAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->yearKeep($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='连续N年及以上履约状况良好';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //年复购次数4
    public function repurchaseAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->repurchase($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='年复购次数';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //客户资信等级5
    public function creditGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->creditGrade($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='客户资信等级';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //零配件年采购额6
    public function purchaseAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->purchase($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='零配件年采购额';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //企业性质7
    public function enterpriseAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->enterprise($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='企业性质';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //营业收入8
    public function incomeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->income($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='营业收入';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //资产规模9
    public function scaleAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->scale($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='资产规模';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //分级结果10
    public function customerGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();
        $res=$model->customerGrade($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='综合分值&客户等级';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='参数错误';
        }
        $this -> jsonReturn($dataJson);
    }
    //---------------------------------------------------------------




    public function editGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();
        $data['created_by']=$this->user['id'];
        foreach($data as $k => &$v){
            $v=trim($v,' ');
        }
        $model = new CustomerGradeModel();  //结算方式
        if(empty($data['id'])){
            $res=$model->addGrade($data);
        }else{
            $res=$model->saveGrade($data);
        }
        if($res===true){
            $dataJson['code']=1;
            $dataJson['message']=$lang=='zh'?'成功':'SUCCESS';
        }elseif($res!==true && $res!==false){
            $dataJson['code']=0;
            $dataJson['message']=$lang=='zh'?'数据不能为空':'Data can not be empty';
//            $dataJson['message']=$res.'不能为空';
        }else{
            $dataJson['code']=0;
            $dataJson['message']=$lang=='zh'?'失败':'ERROR';
        }
        $this -> jsonReturn($dataJson);
    }
    public function infoGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by']=$this->user['id'];
        $data['lang']=$this->getLang();
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->infoGrade($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']='成功';
            $dataJson['data']=$res;
        }else{
            $dataJson['code']=0;
            $dataJson['message']='失败';
        }
        $this -> jsonReturn($dataJson);
    }
    public function delGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();
        $data['created_by']=$this->user['id'];
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->delGrade($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']=$lang=='zh'?'成功':'SUCCESS';
        }else{
            $dataJson['code']=0;
            $dataJson['message']=$lang=='zh'?'失败':'ERROR';
        }
        $this -> jsonReturn($dataJson);
    }
    //提交
    public function submitGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();
        $data['created_by']=$this->user['id'];
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->submitGrade($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']=$lang=='zh'?'成功':'SUCCESS';
        }else{
            $dataJson['code']=0;
            $dataJson['message']=$lang=='zh'?'失败':'ERROR';
        }
        $this -> jsonReturn($dataJson);
    }
    //审核
    public function checkedGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();
        $data['created_by']=$this->user['id'];
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->checkedGrade($data);
        if($res===true){
            $dataJson['code']=1;
            $dataJson['message']=$lang=='zh'?'成功':'SUCCESS';
        }else{
            $dataJson['code']=0;
            $dataJson['message']=$lang=='zh'?'参数错误':'ERROR';
        }
        $this -> jsonReturn($dataJson);
    }
    //发送邮件通知客户分级申请变更通过
    public function noticeEmailAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new CustomerGradeModel();  //结算方式
        $info=$model->noticeEmail($data);
        if($info){
            $dataJson['code']=1;
            $dataJson['message']='Success';
        }else{
            $dataJson['code']=0;
            $dataJson['message']='Error';
        }
        $this->jsonReturn($dataJson);
    }


    //申请变更
    public function applyGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();
        $data['created_by']=$this->user['id'];
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->applyGrade($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']=$lang=='zh'?'成功':'SUCCESS';
        }else{
            $dataJson['code']=0;
            $dataJson['message']=$lang=='zh'?'失败':'ERROR';
        }
        $this -> jsonReturn($dataJson);
    }
    //确认变更
    public function changeGradeAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();
        $data['created_by']=$this->user['id'];
        $model = new CustomerGradeModel();  //结算方式
        $res=$model->changeGrade($data);
        if($res){
            $dataJson['code']=1;
            $dataJson['message']=$lang=='zh'?'成功':'SUCCESS';
        }else{
            $dataJson['code']=0;
            $dataJson['message']=$lang=='zh'?'失败':'ERROR';
        }
        $this -> jsonReturn($dataJson);
    }
}
