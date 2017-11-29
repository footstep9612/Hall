<?php
//客户业务信息 wangs
class BuyerBusinessModel extends PublicModel
{
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'buyer_business'; //采购商业务信息表名
    public function __construct()
    {
        parent::__construct();
    }

    //客户档案管理搜索列表index,wangs
    public function createBusiness($data)
    {
        $validArr = array(
            'product_type', //所需产品类型
            'purchasing_model', //采购模式
            'purchasing_cycle', //采购周期
            'usage',    //设备使用情况
            'is_warehouse', //是否有仓库
            'warehouse_address',    //仓库所在地
            'competitor_info',  //客户竞争对手信息
            'trade_terms',  //客户惯用贸易术语
            'settlement',   //结算方式
            'is_local_settlement',  //是否本地决算
            'is_net',   //是否入网
            'is_purchasing_relationship',   //采购关系
            'net_at',   //入网时间
            'net_invalid_at',   //失效时间
            'net_goods',    //入网产品+
        );
        //业务基础信息,结算基本情况,入网管理
        $purchase = $data['product_type'];  //所需产品类型 varchar
        $purchase = $data['purchasing_model'];  //采购模式 varchar
        $purchase = $data['purchasing_cycle'];  //采购周期 varchar
        $purchase = $data['usage'];  //设备使用情况 varchar
        $purchase = $data['is_warehouse'];  //是否有仓库 char
        $purchase = $data['warehouse_address'];  //仓库所在地 varchar
        $purchase = $data['competitor_info'];  //客户竞争对手信息 varchar
        $purchase = $data['trade_terms'];  //客户惯用贸易术语 varchar
        $purchase = $data['settlement'];  //结算方式 varchar
        $purchase = $data['is_local_settlement'];  //是否本地决算 char
        $purchase = $data['is_net'];  //是否入网 char
        $purchase = $data['net_subject'];  //入网主体 char
        $purchase = $data['net_at'];  //入网时间 date
        $purchase = $data['net_invalid_at'];  //失效时间 date
        $purchase = $data['net_goods'];  //入网产品 varchar
        //采购计划
        $purchase = $data['purchase'];  //采购计划

//        $purchase = $data['purchasing_at'];  //采购年份date
//        $purchase = $data['purchasing_plan'];  //采购计划varchar
//        $purchase = $data['purchasing_budget'];  //采购预算varchar
        //上传附件




//        $page = isset($data['page'])&&!empty($data['page']) ? $data['page'] : 1;
//        $offset = ($page-1)*2;
//        $map = array('buyer.created_by'=>$data['created_by']);
        if(!empty($data['product_type'])){
            $map += array('buyer.area_bn'=>$data['area_bn']);
        }
//        if(!empty($data['country_bn'])){
//            $map += array('buyer.country_bn'=>$data['country_bn']);
//        }
//        if(!empty($data['buyer_code'])){
//            $map += array('buyer.buyer_code'=>$data['buyer_code']);
//        }
//        if(!empty($data['name'])){
//            $map += array('buyer.name'=>$data['name']);
//        }
//        if(!empty($data['buyer_level'])){
//            $map += array('buyer.buyer_level'=>$data['buyer_level']);
//        }
//        if(!empty($data['reg_capital'])){
//            $map += array('buyer.reg_capital'=>$data['reg_capital']);
//        }
//        if(!empty($data['line_of_credit'])){
//            $map += array('buyer.line_of_credit'=>$data['line_of_credit']);
//        }
//        $info = $this->alias('buyer')
//            ->join('erui_buyer.buyer_account account on buyer.id=account.buyer_id','left')
//            ->join('erui_buyer.buyer_business business on account.buyer_id=business.buyer_id','left')
//            ->field('buyer.id,buyer.buyer_code,buyer.name,buyer.area_bn,buyer.country_bn,buyer.line_of_credit,buyer.credit_available,buyer.buyer_level,buyer.level_at,buyer.credit_level,buyer.reg_capital,buyer.created_by,account.email,business.is_local_settlement,business.is_purchasing_relationship,business.is_net,business.net_at,business.net_invalid_at')
//            ->where($map)
//            ->limit($offset,2)
//            ->select();
//        return $info;
    }

}