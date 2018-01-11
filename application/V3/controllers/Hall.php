<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 展厅-V3-王帅
 */
class HallController extends PublicController {

    public function __init() {
        parent::init();
    }
    //GMV累计及区域占比
    public function getGmvAction(){
        $lang=isset($_GET['lang'])?$_GET['lang']:'zh';
        $model = new GmvModel();
        $info = $model->getgmv($lang);
        $dataJson = array(
            'code'=>1,
            'message'=>'GMV累计及区域占比',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    //GMV趋势
    public function getGmvTrendAction(){
        $lang=isset($_GET['lang'])?$_GET['lang']:'zh';
        $model = new GmvModel();
        $info = $model->getGmvTrend($lang);
        $dataJson = array(
            'code'=>1,
            'message'=>'GMV趋势',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    //全球区域买家数量统计
    public function getBuyerAction(){
        $lang=isset($_GET['lang'])?$_GET['lang']:'zh';
//        $data = json_decode(file_get_contents("php://input"), true);
        $model = new BuyerModel();
        $info = $model->getBuyer($lang);
        $dataJson = array(
            'code'=>1,
            'message'=>'全球区域买家数量统计',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    //SKU数量及占比
    public function getGoodsAction(){
        $lang=isset($_GET['lang'])?$_GET['lang']:'zh';
//        $data = json_decode(file_get_contents("php://input"), true);
        $model = new GoodsModel();
        $info = $model->getGoods($lang);
        $dataJson = array(
            'code'=>1,
            'message'=>'SKU数量及占比',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    //平台流量趋势
    public function getPageViewTrendAction(){
        $lang=isset($_GET['lang'])?$_GET['lang']:'zh';
//        $data = json_decode(file_get_contents("php://input"), true);
        $model = new PageViewModel();
        $info = $model->getPageViewTrend($lang);
        $dataJson = array(
            'code'=>1,
            'message'=>'平台流量趋势',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    //海外仓分布点趋势
    public function getWarehouseAction(){
        $lang=isset($_GET['lang'])?$_GET['lang']:'zh';
//        $data = json_decode(file_get_contents("php://input"), true);
        $model = new WareHouseModel();
        $info = $model->getWarehouse($lang);
        $dataJson = array(
            'code'=>1,
            'message'=>'海外仓分布点趋势',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    //订单量统计
    public function getOrdersAction(){
        $lang=isset($_GET['lang'])?$_GET['lang']:'zh';
//        $data = json_decode(file_get_contents("php://input"), true);
        $model = new OrdersModel();
        $info = $model->getOrders($lang);
        $dataJson = array(
            'code'=>1,
            'message'=>'订单量统计',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    //海外发运单趋势
    public function getShipmentrendAction(){
        $lang=isset($_GET['lang'])?$_GET['lang']:'zh';
//        $data = json_decode(file_get_contents("php://input"), true);
        $model = new ShipmentModel();
        $info = $model->getShipmentrend($lang);
        $dataJson = array(
            'code'=>1,
            'message'=>'海外发运单趋势',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
    //订单交付及时率对比趋势
    public function getOrdersRratetrendAction(){
        $lang=isset($_GET['lang'])?$_GET['lang']:'zh';
//        $data = json_decode(file_get_contents("php://input"), true);
        $model = new OrdersRateModel();
        $info = $model->getOrdersRratetrend($lang);
        $dataJson = array(
            'code'=>1,
            'message'=>'订单交付及时率对比趋势',
            'data'=>$info
        );
        $this->jsonReturn($dataJson);
    }
}
