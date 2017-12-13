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
        header('content-type:text/html;charset=utf-8');
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $arr = $model->buyerList($data);
        $info = $arr['info'];
        //客户服务经理
        $agentModel = new BuyerAgentModel();
        $agentRes = $agentModel->getMarketAgent($arr['ids']);
        foreach($info as $key => $value){
            foreach($agentRes as $k => $v){
                if($value['id']==$k){
                    $info[$key]['market_agent']=$v;
                }
            }
        }
        //访问
        $visitModel = new BuyerVisitModel();
        $visitRes = $visitModel->getVisitCount($arr['ids']);
        foreach($info as $key => $value){
            foreach($visitRes as $k => $v){
                if($value['id']==$k){
                    $info[$key]['total_visit']=$v['totalVisit'];
                    $info[$key]['week_visit']=$v['week'];
                    $info[$key]['month_visit']=$v['month'];
                    $info[$key]['quarter_visit']=$v['quarter'];
                }
            }
        }
        //询报价
        $inquiryModel = new InquiryModel();
        $inquiryRes = $inquiryModel->getInquiryStatis($arr['ids']);
        foreach($info as $key => $value){
            foreach($inquiryRes as $k => $v){
                if($value['id']==$k){
                    $info[$key]['inquiry_count']=$v['count'];
                    $info[$key]['inquiry_account']=$v['account'];
                }
            }
        }
        //订单
        $orderModel = new OrderModel();
        $orderRes = $orderModel->getOrderStatis($arr['ids']);
        foreach($info as $key => $value){
            foreach($orderRes as $k => $v){
                if($value['id']==$k){
                    $info[$key]['order_count']=$v['countaccount']['count'];
                    $info[$key]['order_account']=$v['countaccount']['account'];
                    $info[$key]['max_range']=$v['range']['max'];
                    $info[$key]['min_range']=$v['range']['min'];
                }
            }
        }
        $result['page'] = $arr['page'];
        $result['totalCount'] = $arr['totalCount'];
        $result['totalPage'] = $arr['totalPage'];
        $result['info'] = $info;
        $dataJson['code'] = 1;
        $dataJson['message'] = '返回数据';
        $dataJson['data'] = $result;
        $this -> jsonReturn($dataJson);
    }
//id	string	客户id
//area_bn	string	地区
//country_bn	string	国家
//buyer_code	string	客户编码
//name	string	客户名称
//created_at	datetime	创建时间
//is_oilgas	int	是否油气
//buyer_level	varchar	客户等级
//level_at	date	等级设置时间
//reg_capital	decimal	注册资金
//reg_capital_cur	decimal	货币1111111111111111

//credit_level	varchar	采购商信用等级333333333
//credit_type	int	授信类型


//line_of_credit	decimal	授信额度




//is_net	char	是否入网22222222222222222
//net_at	date	入网时间
//net_invalid_at	date	失效时间
//product_type	date	产品类型

//is_local_settlement	char	本地结算444444444444
//is_purchasing_relationship	char	采购关系
//////////////////////////////////////////////////////////////////////////
//market_agent_name	int	kerui/erui客户服务经理55555555555555

//total_visit	int	总访问次数666666666666666666
//quarter_visit	int	季度访问次数
//month_visit	int	月访问次数
//week_visit	int	周访问次数
//inquiry_count	int	询报价数量777777777777777777777
//inquiry_account	int	询报价金额
//order_count	int	订单数量
//order_account	int	订单金额
//order_range	int	单笔金额偏重区间
}
