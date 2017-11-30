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
        $created_by = '39305';
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerModel();
        $arr = $model->buyerList($data);
        if(!$arr){
            echo json_encode(array("code" => "0", "data" => $res, "message" => "空数据"));
            exit();
        }
        $info = $arr['info'];
        $visitModel = new BuyerVisitModel();
        $visitRes = $visitModel->getVisitCount($arr['ids']);
        foreach($info as $key => $value){
            foreach($visitRes as $k => $v){
                if($value['id']==$k){
                    $info[$key]['week']=$v['week'];
                    $info[$key]['month']=$v['month'];
                    $info[$key]['quarter']=$v['quarter'];
                }
            }
        }
        echo json_encode(array("code" => "1", "data" => $info, "message" => "返回数据"));
    }
    //查看
//    public function showBuyerAction(){
//        $created_by = '39305';
//        $data = json_decode(file_get_contents("php://input"), true);
//        $data['created_by'] = $created_by;
//        $model = new BuyerModel();
//        $res = $model->showBuyer($data);
//        if(!$res){
//            echo json_encode(array("code" => "0", "data" => $res, "message" => "空数据"));
//            exit();
//        }
//        echo json_encode(array("code" => "1", "data" => $res, "message" => "返回数据"));
//    }
}
