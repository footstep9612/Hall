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
    public function buyerListAction()
    {
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $data['admin']=$this->getUserRole();
        $model = new BuyerModel();
        $arr = $model->buyerList($data);
        $dataJson['code'] = 1;
        $dataJson['message'] = '返回数据';
        $dataJson['data'] = $arr;
        $this -> jsonReturn($dataJson);
    }
    /**
     * 客户管理列表excel导出
     */
    public function exportBuyerExcelAction(){
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $data['admin']=$this->getUserRole();
        $model = new BuyerModel();
        $res = $model->exportBuyerExcel($data);
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
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $buyer_id=$data['buyer_id'];
        $baseCond=array('id'=>$buyer_id,'is_build'=>1,'deleted_flag'=>'N');
        $cond=array(
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        //客户基本信息
        $base = new BuyerModel();
        $baseField=array(
            'buyer_code', //客户代码
            'buyer_no', //客户编码
//            'buyer_level', //客户等级
            'country_bn', //国家
            'buyer_type', //客户类型
            'is_oilgas', //是否油气
            'name as company_name', //公司名称
            'official_phone', //公司电话
            'official_email', //公司邮箱
            'official_website', //公司网址
            'company_reg_date', //公司注册日期
            'reg_capital', //注册金额
            'reg_capital_cur', //注册币种
            'employee_count', //公司员工数量
            'company_model', //公司性质
            'sub_company_name', //子公司名称
            'company_address', //公司地址
            'profile as company_profile', //公司其他信息
//            'biz_scope', //公司名称
//            'intent_product', //公司名称
//            'purchase_amount', //公司名称
            'line_of_credit', //授信额度
            'credit_available', //可用额度
            'credit_type', //授信类型
            'credit_level', //信用等级
            'payment_behind', //是否拖欠过货款
            'behind_time', //拖欠货款时间
            'reputation', //业内口碑
            'violate_treaty', //是否有针对ERUI的违约
            'treaty_content', //违约的内容
            'comments' //ERUI对其评价
        );
        $baseInfo=$base->field($baseField)->where($baseCond)->find();
        //联系人
        $contact = new BuyercontactModel();
        $contactField=array(
            'name as contact_name', //联系人姓名
            'title as contact_title', //联系人职位
            'role as contact_role', //角色
            'phone as contact_phone', //联系人电话
            'email as contact_email', //联系人邮箱
            'hobby as contact_hobby', //爱好
            'address as contact_address', //地址
            'experience', //经历
            'social_relations', //社会关系
            'key_concern', //决策主要关注点
            'attitude_kerui', //对科瑞的态度
            'social_habits', //常去社交场所
            'relatives_family' //家庭亲戚相关信息
        );
        $contactInfo=$contact->field($contactField)->where($cond)->find();
        $baseArr=array_merge($baseInfo,$contactInfo);
        //上下游-竞争对手
        $chain=new IndustrychainModel();
        $upField=array(
            'name as up_name', //客户名称
            'cooperation as up_cooperation', //合作情况
            'business_type as up_business_type', //业务类型
            'scale as up_scale', //规模
            'settlement as up_settlement', //结算方式
            'marketing_network as up_marketing_network', //营销网络
//            'buyer_type_name as up_buyer_type_name', //客户的客户类型名称
            'buyer_project as up_buyer_project', //客户参与的项目
            'buyer_problem as up_buyer_problem', //客户遇到过的困难
            'solve_problem as up_solve_problem' //客户如何解决的困难
        );
        $downField=array(
            'name as down_name', //客户名称
            'cooperation as down_cooperation', //合作情况
            'goods as down_goods', //商品
            'profile as down_profile', //简介
            'settlement as down_settlement', //结算方式
            'warranty_terms as down_warranty_terms', //保质条款
            'relationship as down_relationship', //供应商与客户关系如何
            'analyse as down_analyse', //与KERUI/ERUI的对标分析
            'dynamic as down_dynamic' //供应商动态
        );
        $competitorField=array(
            'competitor_name', //竞争对手名称
            'competitor_area', //竞争领域
            'company_compare', //两公司优劣势对比
            'what_plan' //KERUI/ERUI可以做什么
        );
        $upCond=array('buyer_id'=>$buyer_id,'deleted_flag'=>'N','industry_group'=>'up');
        $downCond=array('buyer_id'=>$buyer_id,'deleted_flag'=>'N','industry_group'=>'down');
        $competitorCond=array('buyer_id'=>$buyer_id,'deleted_flag'=>'N','industry_group'=>'competitor');
        $upInfo=$chain->field($upField)->where($upCond)->find();
        $downInfo=$chain->field($downField)->where($downCond)->find();
        $competitorInfo=$chain->field($competitorField)->where($competitorCond)->find();
        $chainArr=array_merge($upInfo,$downInfo,$competitorInfo);
        //业务信息
        $business=new BuyerBusinessModel();
        $businessField=array(
            'product_type', //产品类型
            'purchasing_model', //采购模式
            'purchasing_cycle', //采购周期
            'usage', //设备以及使用情况
            'is_warehouse', //是否有仓库
            'warehouse_address', //仓库所在地
            'Product_service_preference', //产品服务偏好
            'Origin_preference', //原产地偏好
            'Brand_preference', //品牌偏好
            'trade_terms', //贸易术语
            'settlement', //结算方式
            'is_local_settlement', //是否支持本地结算
            'is_purchasing_relationship', //是否有采购关系
            'is_net', //是否入网
//            'net_subject', //入网主题
//            'net_at', //是否有采购关系
//            'net_invalid_at', //是否有采购关系
//            'net_goods' //是否有采购关系
        );
        $businessCond=array('buyer_id'=>$buyer_id);
        $businessInfo=$business->field($businessField)->where($businessCond)->find();
        //入网主题内容
        $subject=new NetSubjectModel();
        $equipmentField=array(
            'subject_name as equipment_subject_name', //入网主题简称
            'net_at as equipment_net_at', //入网时间
            'net_invalid_at as equipment_net_invalid_at', //失效时间
            'net_goods as equipment_net_goods' //入网商品
        );
        $eruiField=array(
            'subject_name as erui_subject_name', //入网主题简称
            'net_at as erui_net_at', //入网时间
            'net_invalid_at as erui_net_invalid_at', //失效时间
            'net_goods as erui_net_goods' //入网商品
        );
        $equipmentInfo=$subject->field($equipmentField)->where(array('buyer_id'=>$buyer_id,'subject_name'=>'equipment','deleted_flag'=>'N'))->find();
        $eruiInfo=$subject->field($eruiField)->where(array('buyer_id'=>$buyer_id,'subject_name'=>'erui','deleted_flag'=>'N'))->find();
        //采购计划
        $purchasing=new BuyerPurchasingModel();
        $purchasingField=array(
            'purchasing.purchasing_at', //采购时间
            'purchasing.purchasing_budget', //采购预算
            'purchasing.purchasing_plan', //采购计划
            'attach.attach_name', //采购计划
            'attach.attach_url', //采购计划
        );
        $purchasingInfo=$purchasing->alias('purchasing')
            ->join('erui_buyer.purchasing_attach attach on purchasing.id=attach.purchasing_id','left')
            ->field($purchasingField)
            ->where(array('purchasing.buyer_id'=>$buyer_id,'purchasing.deleted_flag'=>'N'))
            ->find();
        //里程碑事件
        $milestone_event=new MilestoneEventModel();
        $eventField=array(
            'event_time', //里程碑时间
            'event_name', //里程碑名称
            'event_content', //里程碑事件内容
            'event_contact' //里程碑负责人
        );
        $eventInfo=$milestone_event->field($eventField)->where($cond)->find();
        $businessArr=array_merge($businessInfo,$equipmentInfo,$eruiInfo,$purchasingInfo,$eventInfo);
        //附件=财务报表-公司人员组织架构-分析报告
        $attach=new BuyerattachModel();
        $attachInfo=$attach->field('attach_group,attach_name,attach_url')->where($cond)->group('attach_group')->select();

        //汇总
        $info=array_merge($baseArr,$chainArr,$businessArr);
        $infoCount=count($info)+3;  //总数
        //统计数据
        $infoExist=count(array_filter($info))+count($attachInfo);
        //判断
        if(!empty($info['is_warehouse'])){  //仓库
            if($info['is_warehouse']=='N'){
                $infoExist += 1;
            }
        }
        if($info['is_net']){    //入网
            if($info['is_net']=='N'){
                $infoExist += 6;
            }
        }
        if($info['payment_behind']){    //拖欠货款
            if($info['payment_behind']=='N'){
                $infoExist += 1;
            }
        }
        if($info['violate_treaty']){    //是否违约
            if($info['violate_treaty']=='N'){
                $infoExist += 1;
            }
        }
        //判断end
        $percent=floor(($infoExist / $infoCount)*100);
        //更新百分比
        $base->where(array('id'=>$buyer_id))->save(array('percent'=>$percent));
        $dataJson=array(
            'code'=>1,
            'message'=>'档案信息完整度',
            'data'=>$percent.'%'
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
            $info=null;
        }
        $dataJson = array(
            'code'=>1,
            'message'=>'地区国家权限列表',
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
        $totalMember = $buyerModel->crmGetBuyerTotal($cond);
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
                'total_member' => $totalMember ? : 0
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
