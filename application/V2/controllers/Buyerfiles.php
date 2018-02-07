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

    public function __init()
    {
        parent::__init();
    }
    /*
     * 客户管理列表搜索展示
     * */
    public function buyerListAction()
    {
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
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
            'created_by'=>$created_by,
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
//            'behind_time', //拖欠货款时间
            'reputation', //业内口碑
            'violate_treaty', //是否有针对ERUI的违约
//            'treaty_content', //违约的内容
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
}
