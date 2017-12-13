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
                'message'=>'excel导出失败'
            );
            $this->jsonReturn($dataJson);
        }
    }
}
