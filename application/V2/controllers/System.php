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
class SystemController extends Yaf_Controller_Abstract
{
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
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($dataJson);
    }
}
