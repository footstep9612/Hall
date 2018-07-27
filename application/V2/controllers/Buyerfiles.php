<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
    客户管理
 * 王帅
 */
class BuyerfilesController extends PublicController
{

    public function init() {
        parent::init();
    }
    //获取用户的角色
    public function getUserRole(){
        /*$config = \Yaf_Application::app()->getConfig();
        $ssoServer=$config['ssoServer'];
        $token=$_COOKIE['eruitoken'];
        $opt = array(
            'http'=>array(
                'method'=>"POST",
                'header'=>"Content-Type: application/json\r\n" .
                    "Cookie: ".$_COOKIE."\r\n",
                'content' =>json_encode(array('token'=>$token))

            )
        );
        $context = stream_context_create($opt);
        $json = file_get_contents($ssoServer,false,$context);
        $info=json_decode($json,true);
        $arr['role']=$info['role_no'];
        if(!empty($info['country_bn'])){
            $countryArr=[];
            foreach($info['country_bn'] as $k => $v){
                $countryArr[]="'".$v."'";
            }
            $countryStr=implode(',',$countryArr);
        }*/
        $arr['role']=$this->user['role_no'];
        foreach($this->user['country_bn'] as $v) {
            $countryArr[]="'".$v."'";
        }
        $countryStr = implode(',', $countryArr);
        $buyer=new BuyerModel();
        $areas=$buyer->table('erui_operation.market_area_country')
            ->field('distinct market_area_bn as area_bn')
            ->where(['country_bn' => ['in', $this->user['country_bn'] ? : ['-1']]])->select();
        if(!empty($areas)){
            $areaArr=[];
            foreach($areas as $k => $v){
                $areaArr[]="'".$v['area_bn']."'";
            }
            $areaStr=implode(',',$areaArr);
        }
        $arr['country']=$countryStr;
        $arr['area']=$areaStr;
        return $arr;
    }
    /*
     * 客户管理列表搜索展示
     * */
    public function buyerList1Action()
    {
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $data['lang'] = $this->getLang();
        $data['admin']=$this->getUserRole();
        $model = new BuyerModel();
        $arr = $model->buyerList($data);
        $dataJson['code'] = 1;
        $dataJson['message'] = '返回数据';
        $dataJson['data'] = $arr;
        $this -> jsonReturn($dataJson);
    }
    public function buyerListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this->user['id'];;
        $data['admin']=$this->getUserRole();   //=1市场专员
        $data['lang'] = $this->getLang();
        $model = new BuyerModel();
        $ststisInfo = $model->buyerStatisList($data,false,true);
        if($ststisInfo===false){
            $dataJson = array(
                'code' => 1,
                'message' => '返回数据',
                'total_status' => 0,
                'approved_status' => 0,
                'approving_status' => 0,
                'rejected_status' => 0,
                'currentPage' => 1,
                'data' => []
            );
            $this->jsonReturn($dataJson);
        }
        $cond = $model->getBuyerStatisListCond($data,false);  //获取条件
        $totalCount=$model->crmGetBuyerTotal($cond); //获取总条数
        $statusCount=$model->crmGetBuyerStatusCount($cond);    //获取各个状态的总
        $dataJson = array(
            'code' => 1,
            'message' => '返回数据',
            'total_status' => intval($totalCount),
            'approved_status' => intval($statusCount['APPROVED']),
            'approving_status' => intval($statusCount['APPROVING']),
            'rejected_status' => intval($statusCount['REJECTED']),
            'currentPage' => $ststisInfo['currentPage'],
        );
        if(empty($data['status'])){
            $dataJson['count']=intval($ststisInfo['totalCount']);
        }elseif($data['status']=='APPROVED'){
            $dataJson['count']=intval($statusCount['APPROVED']);
        }elseif($data['status']=='APPROVING'){
            $dataJson['count']=intval($statusCount['APPROVING']);
        }elseif($data['status']=='REJECTED'){
            $dataJson['count']=intval($statusCount['REJECTED']);
        }
        $dataJson['data']= $ststisInfo['info'];
        $this->jsonReturn($dataJson);
    }
    /**
     * 客户管理列表excel导出
     */
    public function exportBuyerExcelAction(){
        set_time_limit(0);
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $data['lang'] = $this->getLang();
        $data['admin']=$this->getUserRole();
        $model = new BuyerModel();
        try{
            $res = $model->exportBuyerExcel($data);
        }catch (Exception $e){
            print_r($e->getMessage());exit;
        }
        if($res['code'] == 1){
            $excel = new BuyerExcelModel();
            $excel->saveExcel($res['name'],$res['url'],$created_by);
            $this->jsonReturn($res);
        }else{
            $dataJson = array(
                'code'=>0,
                'message'=>'excel导出错误或数据为空'
            );
            $this->jsonReturn($dataJson);
        }
    }
    /**
     * 客户档案信息管理计算信息完整度-王帅
     */
    public function percentInfoAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        if(empty($data['buyer_id'])){
            $dataJson=array(
                'code'=>0,
                'message'=>'参数错误'
            );
            return $this->jsonReturn($dataJson);
        }
        //客户基本信息
        $base = new BuyerModel();
        $baseInfo=$base->percentBuyer($data);
        if(empty($baseInfo)){
            $dataJson=array(
                'code'=>0,
                'message'=>'暂无信息'
            );
            return $this->jsonReturn($dataJson);
        }
        unset($baseInfo['buyer_no']);
        //信用评价信息
        $credit=new CustomerCreditModel();
        $creditInfo=$credit->percentCredit($data);
        //联系人
        $contact = new BuyercontactModel();
        $contactInfo=$contact->percentContact($data);
        //上下游-竞争对手
        $chain=new IndustrychainModel();
        $chainInfo=$chain->percentChain($data);
        //业务信息
        $business=new BuyerBusinessModel();
        $businessInfo=$business->percentBusiness($data);
        //入网主题内容
        $subject=new NetSubjectModel();
        $netInfo=$subject->percentNetSubject($data);
        //采购计划
        $purchasing=new BuyerPurchasingModel();
        $purchasingInfo=$purchasing->percentPurchase($data);
        //里程碑事件
        $milestone_event=new MilestoneEventModel();
        $eventInfo=$milestone_event->percentMilestoneEvent($data);
        //附件=财务报表-公司人员组织架构-分析报告
        $attach=new BuyerattachModel();
        $cond=array('buyer_id'=>$data['buyer_id'],'deleted_flag'=>'N');
        $attachArr=$attach->field('attach_group,attach_name,attach_url')->where($cond)->group('attach_group')->select();

        //汇总
        $attachInfo=$attachArr?$attachArr:[];   //附件
        $infoArr=array_merge($baseInfo,$creditInfo,$contactInfo,$chainInfo,$businessInfo,$netInfo,$purchasingInfo,$eventInfo);  //信息
        $infoCount=count($infoArr)+3;  //总数
        //统计数据
        $infoExist=count(array_filter($infoArr))+count($attachInfo);
        //判断
        if(!empty($infoArr['is_warehouse'])){  //仓库
            if($infoArr['is_warehouse']=='N'){
                $infoExist += 1;
            }
        }
        if($infoArr['payment_behind']){    //拖欠货款
            if($infoArr['payment_behind']=='N'){
                $infoExist += 1;
            }
        }
        if($infoArr['violate_treaty']){    //是否违约
            if($infoArr['violate_treaty']=='N'){
                $infoExist += 1;
            }
        }
        //判断end
        $percent=floor(($infoExist / $infoCount)*100);
        //更新百分比
        $base->where(array('id'=>$data['buyer_id']))->save(array('percent'=>$percent));
        $dataJson=array(
            'code'=>1,
            'message'=>'档案信息完整度',
            'data'=>$percent
        );
        return $this->jsonReturn($dataJson);
    }
    //会员统计来源
    public function statisMemberAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this -> user['id'];;
        $data['admin'] = $this->getUserRole();
        $data['lang'] = $this->getLang();
        $buyer=new BuyerModel();
        $member=$buyer->statisMemberInfo($data);
        if($member===false){
            $dataJson = array(
                'code'=>1,
                'message'=>'无权查看会员来源统计',
            );
        }else{
            $dataJson = array(
                'code'=>1,
                'message'=>'会员来源统计',
                'data'=>$member
            );
        }
        $this->jsonReturn($dataJson);
    }
    //会员增长
    public function memberSpeedAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this -> user['id'];
        $role=$this->getUserRole();
        $data['lang'] = $this->getLang();
        $data['admin'] = $role;
        $buyer=new BuyerModel();
        $member=$buyer->memberSpeed($data);
        if($member===false){
            $dataJson = array(
                'code'=>1,
                'message'=>'无权限查看会员增长'
            );
        }else{
            $dataJson = array(
                'code'=>1,
                'message'=>'会员增长',
                'data'=>$member
            );
        }
        $this->jsonReturn($dataJson);
    }
    //统计询单量
    public function statisInquiryAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this -> user['id'];
        $data['admin'] = $this->getUserRole();
        $data['lang'] = $this->getLang();
        $inquiry=new InquiryModel();
        $inquiryInfo=$inquiry->statisCondInquiry($data);
        if($inquiryInfo===false){
            $dataJson = array(
                'code'=>1,
                'message'=>'无权限查看询单量统计'
            );
        }else{
            $dataJson = array(
                'code'=>1,
                'message'=>'询单量统计',
                'data'=>$inquiryInfo
            );
        }
        $this->jsonReturn($dataJson);
    }
    //统计报价量
    public function statisQuoteAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this -> user['id'];
        $data['admin'] = $this->getUserRole();
        $data['lang'] = $this->getLang();
        $inquiry=new InquiryModel();
        $quoteInfo=$inquiry->statisCondQuote($data);
        if($quoteInfo===false){
            $dataJson = array(
                'code'=>1,
                'message'=>'无权限查看报价量统计'
            );
        }else{
            $dataJson = array(
                'code'=>1,
                'message'=>'报价量统计',
                'data'=>$quoteInfo
            );
        }
        $this->jsonReturn($dataJson);
    }
    //统计报价量
    public function statisOrderAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this -> user['id'];
        $data['admin'] = $this->getUserRole();
        $data['lang'] = $this->getLang();
        $order=new OrderModel();
        $orderInfo=$order->statisCondOrder($data);
        if($orderInfo===false){
            $dataJson = array(
                'code'=>1,
                'message'=>'无权限查看订单量统计'
            );
        }else{
            $dataJson = array(
                'code'=>1,
                'message'=>'订单量统计',
                'data'=>$orderInfo
            );
        }
        $this->jsonReturn($dataJson);
    }
    //会员统计信息列表
    public function statisMemberListAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this -> user['id'];
        $data['admin'] = $this->getUserRole();
        $data['lang'] = $this->getLang();
        $buyer=new BuyerModel();
        $memInfo=$buyer->statisMemberList($data);
        if($memInfo===false){
            $dataJson = array(
                'code'=>1,
                'message'=>'无权限统计会员信息列表'
            );
        }else{
            $dataJson = array(
                'code'=>1,
                'message'=>'统计会员信息列表',
                'data'=>$memInfo
            );
        }
        $this->jsonReturn($dataJson);
    }
    //会员属性统计信息列表
    public function statisMemberAttrAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this -> user['id'];
        $data['admin'] = $this->getUserRole();
        $data['lang'] = $this->getLang();
        $buyer=new BuyerModel();
        $memInfo=$buyer->statisMemberAttr($data);
        if($memInfo===false){
            $dataJson = array(
                'code'=>1,
                'message'=>'无权限查看会员属性统计列表'
            );
        }else{
            $dataJson = array(
                'code'=>1,
                'message'=>'会员属性统计列表',
                'data'=>$memInfo
            );
        }
        $this->jsonReturn($dataJson);
    }
    //会员行为统计信息列表
    public function statisMemberBehaveAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $this -> user['id'];
        $data['admin'] = $this->getUserRole();
        $data['lang'] = $this->getLang();
        $buyer=new BuyerModel();
        $memInfo=$buyer->statisMemberBehave($data);
        if($memInfo===false){
            $dataJson = array(
                'code'=>1,
                'message'=>'无权查看会员行为统计列表'
            );
        }else{
            $dataJson = array(
                'code'=>1,
                'message'=>'会员行为统计列表',
                'data'=>$memInfo
            );
        }
        $this->jsonReturn($dataJson);
    }
    //地区国家
    public function areaCountryAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();
        $area=new CountryModel();
        $role=$this->getUserRole();
        if(in_array('CRM客户管理',$role['role'])){  //所有权限
            if(!empty($data['area_bn'])){
                $info=$area->table('erui_operation.market_area_country country_bn')
                    ->join('erui_dict.country country on country_bn.country_bn=country.bn')
                    ->field('country_bn.country_bn,country.name as country_name')
                    ->where(array('country_bn.market_area_bn'=>$data['area_bn'],'country.lang'=>$lang,'country.deleted_flag'=>'N'))
                    ->select();
            }else{
                $info=$area->table('erui_operation.market_area')
                    ->field('bn as area_bn,name as area_name')
                    ->where(array('deleted_flag'=>'N','lang'=>$lang))
                    ->select();
            }
        }elseif(in_array('201711242',$role['role'])){   //所属地区国家权限
            if(!empty($data['area_bn'])){
                $info=$area->table('erui_operation.market_area_country country_bn')
                    ->join('erui_dict.country country on country_bn.country_bn=country.bn')
                    ->field('country_bn.country_bn,country.name as country_name')
                    ->where("country_bn.market_area_bn='$data[area_bn]' and country.lang='$lang' and country.deleted_flag='N' and country_bn.country_bn in ($role[country])")
                    ->select();
            }else{
                $info=$area->table('erui_operation.market_area')
                    ->field('bn as area_bn,name as area_name')
                    ->where("bn in ($role[area]) and deleted_flag='N' and lang='$lang'")
                    ->select();
            }
        }else{
            $info=[];
        }
        $dataJson = array(
            'code'=>1,
            'message'=>'地区国家权限列表',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    //客户管理国家权限列表
    public function accessCountryAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $lang=$this->getLang();
        $country=new CountryModel();
        $role=$this->getUserRole();
//        print_r($role);die;
        if(in_array('CRM客户管理',$role['role'])){  //所有权限
            $info=$country->table('erui_dict.country')->field('bn,name')
                ->where(array('lang'=>$lang,'deleted_flag'=>'N'))
                ->select();
        }
        else{
            $info=$country->table('erui_dict.country')->field('bn,name')
                ->where("bn in ($role[country]) and lang='$lang' and deleted_flag='N'")
                ->select();
        }
        $dataJson = array(
            'code'=>1,
            'message'=>'国家权限列表',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    /**
     * @desc BOSS首页获取客户信息
     *
     * @author liujf
     * @time 2018-05-07
     */
    public function getCustomerInfoAction() {
        $buyerModel = new BuyerModel();
        $inquiryModel = new InquiryModel();
        $orderModel = new OrderModel();
        $condition['created_by'] = $this -> user['id'];
        $condition['admin'] = $this->getUserRole();
        $condition['lang'] = $this->lang;
        // 会员总数
        $cond = $buyerModel->getBuyerStatisListCond($condition);
        $totalMember = $cond ? $buyerModel->crmGetBuyerTotal($cond) : 0;
        // 今日
        $condition['start_time'] = $condition['end_time'] = date('Y-m-d');
        $todayMemberSpeed = $buyerModel->memberSpeed($condition);
        $todayInquirySpeed = $inquiryModel->statisCondInquiry($condition);
        $todayOrderSpeed = $orderModel->statisCondOrder($condition);
        // 本周
        $condition['start_time'] = date('Y-m-d', (time() - ((date('w') ? : 7) + 1) * 24 * 3600));
        $weekMemberSpeed = $buyerModel->memberSpeed($condition);
        $weekInquirySpeed = $inquiryModel->statisCondInquiry($condition);
        $weekOrderSpeed = $orderModel->statisCondOrder($condition);
        // 本月
        $condition['start_time'] = date('Y-m') . '-01';
        $monthMemberSpeed = $buyerModel->memberSpeed($condition);
        $monthInquirySpeed = $inquiryModel->statisCondInquiry($condition);
        $monthOrderSpeed = $orderModel->statisCondOrder($condition);
        // 客户信息
        $customerInfo = [
            'today' => [
                'member_speed' => array_sum($todayMemberSpeed['count']) ? : 0,
                'inquiry_speed' => array_sum($todayInquirySpeed['count']) ? : 0,
                'order_speed' => array_sum($todayOrderSpeed['count']) ? : 0
            ],
            'week' => [
                'member_speed' => array_sum($weekMemberSpeed['count']) ? : 0,
                'inquiry_speed' => array_sum($weekInquirySpeed['count']) ? : 0,
                'order_speed' => array_sum($weekOrderSpeed['count']) ? : 0
            ],
            'month' => [
                'member_speed' => array_sum($monthMemberSpeed['count']) ? : 0,
                'inquiry_speed' => array_sum($monthInquirySpeed['count']) ? : 0,
                'order_speed' => array_sum($monthOrderSpeed['count']) ? : 0
            ],
            'total' => [
                'total_member' => $totalMember
            ]
        ];
        $res['code'] = 1;
        $res['message'] = L('SUCCESS');
        $res['data'] = $customerInfo;
        $this->jsonReturn($res);
    }

    /**
     * 钉钉消息提醒-wangs
     */
    public function sentMessageAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['lang']=$this->getLang();
        $data['admin']=$this->getUserRole();
        $buyer=new BuyerModel();
        $res=$buyer->messageRemind($data);    //消息提醒
        if($res===false){
            $dataJson['code'] = 1;
            $dataJson['message'] = '无市场区域国家负责人权限';
            $dataJson['data'] = array('count'=>0);
        }elseif($res==0 || empty($res)){
            $dataJson['code'] = 1;
            $dataJson['message'] = '无消息提醒';
            $dataJson['data'] = array('count'=>0);
        }else{
            $dataJson['code'] = 1;
            $dataJson['message'] = '消息提醒';
            $dataJson['data'] = $res;
        }
        $this->jsonReturn($dataJson);
    }
    public function noticeMessageAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['lang']=$this->getLang();
        $data['admin']=$this->getUserRole();
        $buyer=new BuyerModel();
        $res=$buyer->noticeMessage($data);    //消息提醒
        if($res===false){
            $dataJson['code'] = 1;
            $dataJson['message'] = '无市场区域国家负责人权限';
            $dataJson['data'] = array('count'=>0);
        }elseif($res==0 || empty($res)){
            $dataJson['code'] = 1;
            $dataJson['message'] = '无消息提醒';
            $dataJson['data'] = array('count'=>0);
        }else{
            $dataJson['code'] = 1;
            $dataJson['message'] = '消息提醒';
            $dataJson['data'] = $res;
        }
        $this->jsonReturn($dataJson);
    }
    public function readMessageAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $data['lang']=$this->getLang();
        $data['admin']=$this->getUserRole();
        $buyer=new BuyerModel();
        $res=$buyer->readMessage($data);    //消息提醒
        if($res==='param'){
            $dataJson['code'] = 1;
            $dataJson['message'] = '缺少参数';
        }elseif($res==='none'){
            $dataJson['code'] = 1;
            $dataJson['message'] = '客户不存在';
        }else{
            $dataJson['code'] = 1;
            $dataJson['message'] = '读取信息';
        }
        $this->jsonReturn($dataJson);
    }
    //系统提示信息
//    public function sentSystemMessageAction(){
//        $data = json_decode(file_get_contents("php://input"), true);
//        $data['lang']=$this->getLang();
//        $data['admin']=$this->getUserRole();
//        $buyer=new BuyerModel();
//        $res=$buyer->sentSystemMessage($data);
//        if($res===0){   //无数据
//            $dataJson['code'] = 1;
//            $dataJson['message'] = '无消息提醒';
//        }else{
//            $dataJson['code'] = 1;
//            $dataJson['message'] = '24h过期消息提醒';
//            $dataJson['data'] = $res;
//        }
//        $this->jsonReturn($dataJson);
//    }
    public function requestSystemAction(){
        $buyer=new BuyerModel();
        $res=$buyer->requestSystem();
        if($res===0){   //无数据
            $dataJson['code'] = 1;
            $dataJson['message'] = '无消息提醒';
        }else{
            $dataJson['code'] = 1;
            $dataJson['message'] = '24h过期消息设置OK';
            $dataJson['data'] = $res;
        }
        $this->jsonReturn($dataJson);
    }
    //X
//    public function sysMsgSetAction(){
//        $data['admin']=$this->getUserRole();
//        $data['lang']=$this->getLang();
//        $buyer=new BuyerModel();
//        $res=$buyer->sysMsgSet($data);
//        if($res===0){   //无数据
//            $dataJson['code'] = 1;
//            $dataJson['message'] = '无消息提醒';
//        }else{
//            $dataJson['code'] = 1;
//            $dataJson['message'] = '24h过期消息设置OK';
//            $dataJson['data'] = $res;
//        }
//        $this->jsonReturn($dataJson);
//    }
}
