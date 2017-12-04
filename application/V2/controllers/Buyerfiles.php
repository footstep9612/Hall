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
        $result['page'] = $arr['page'];
        $result['info'] = $info;
        $dataJson['code'] = 1;
        $dataJson['message'] = '返回数据';
        $dataJson['data'] = $result;
        $this -> jsonReturn($dataJson);
    }
}
