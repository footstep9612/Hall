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
class BuyerTestController extends PublicController
{

    public function __init()
    {
        parent::init();
    }
    /**
     * 客户档案信息管理计算信息完整度-王帅
     */
    public function percentTestAction(){
        set_time_limit(0);
        $base = new BuyerModel();
        $info=$base->field('id,created_by')->where(array('is_build'=>1,'status'=>'APPROVED','deleted_flag'=>'N'))->select();
        $arr=[];
        foreach($info as $k => $v){
            $buyer_id=$v['id'];
            $created_by=$v['created_by'];
            $res=$this->percentTest($buyer_id,$created_by);
            $arr[]=$res;
        }
        $save=$base->where(array('percent'=>0))->save(array('percent'=>null));
        print_r($arr);
        print_r($save);
    }
    public function percentTest($buyer_id,$created_by){
        set_time_limit(0);
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
        if(empty($upInfo)){
            $upInfo=array(
                'up_name'=>null, //客户名称
                'up_cooperation'=>null, //合作情况
                'up_business_type'=>null, //业务类型
                'up_scale'=>null, //规模
                'up_settlement'=>null, //结算方式
                'up_marketing_network'=>null, //营销网络
                'up_buyer_project'=>null, //客户参与的项目
                'up_buyer_problem'=>null, //客户遇到过的困难
                'up_solve_problem'=>null //客户如何解决的困难
            );
        }
        $downInfo=$chain->field($downField)->where($downCond)->find();
        if(empty($downInfo)){
            $downInfo=array(
                'down_name'=>null, //客户名称
                'down_cooperation'=>null, //合作情况
                'down_goods'=>null, //商品
                'down_profile'=>null, //简介
                'down_settlement'=>null, //结算方式
                'down_warranty_terms'=>null, //保质条款
                'down_relationship'=>null, //供应商与客户关系如何
                'down_analyse'=>null, //与KERUI/ERUI的对标分析
                'down_dynamic'=>null //供应商动态
            );
        }
        $competitorInfo=$chain->field($competitorField)->where($competitorCond)->find();
        if(empty($competitorInfo)){
            $competitorInfo=array(
                'competitor_name'=>null, //竞争对手名称
                'competitor_area'=>null, //竞争领域
                'company_compare'=>null, //两公司优劣势对比
                'what_plan'=>null //KERUI/ERUI可以做什么
            );
        }
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
        if(empty($businessInfo)){
            $businessInfo=array(
                'product_type'=>null, //产品类型
                'purchasing_model'=>null, //采购模式
                'purchasing_cycle'=>null, //采购周期
                'usage'=>null, //设备以及使用情况
                'is_warehouse'=>null, //是否有仓库
                'warehouse_address'=>null, //仓库所在地
                'Product_service_preference'=>null, //产品服务偏好
                'Origin_preference'=>null, //原产地偏好
                'Brand_preference'=>null, //品牌偏好
                'trade_terms'=>null, //贸易术语
                'settlement'=>null, //结算方式
                'is_local_settlement'=>null, //是否支持本地结算
                'is_purchasing_relationship'=>null, //是否有采购关系
                'is_net'=>null //是否入网
            );
        }
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
        if(empty($equipmentInfo)){
            $equipmentInfo=array(
                'equipment_subject_name'=>null,
                'equipment_net_at'=>null,
                'equipment_net_invalid_at'=>null,
                'equipment_net_goods'=>null
            );
        }
        $eruiInfo=$subject->field($eruiField)->where(array('buyer_id'=>$buyer_id,'subject_name'=>'erui','deleted_flag'=>'N'))->find();
        if(empty($eruiInfo)){
            $eruiInfo=array(
                'erui_subject_name'=>null,
                'erui_net_at'=>null,
                'erui_net_invalid_at'=>null,
                'erui_net_goods'=>null
            );
        }
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
        if(empty($purchasingInfo)){
            $purchasingInfo=array(
                'purchasing.purchasing_at'=>null, //采购时间
                'purchasing.purchasing_budget'=>null, //采购预算
                'purchasing.purchasing_plan'=>null, //采购计划
                'attach.attach_name'=>null, //采购计划
                'attach.attach_url'=>null, //采购计划
            );
        }
        //里程碑事件
        $milestone_event=new MilestoneEventModel();
        $eventField=array(
            'event_time', //里程碑时间
            'event_name', //里程碑名称
            'event_content', //里程碑事件内容
            'event_contact' //里程碑负责人
        );
        $eventInfo=$milestone_event->field($eventField)->where($cond)->find();
        if(empty($eventInfo)){
            $eventInfo=array(
                'event_time'=>null, //里程碑时间
                'event_name'=>null, //里程碑名称
                'event_content'=>null, //里程碑事件内容
                'event_contact'=>null //里程碑负责人
            );
        }
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
        return $base->where(array('id'=>$buyer_id))->save(array('percent'=>$percent));
    }
}
