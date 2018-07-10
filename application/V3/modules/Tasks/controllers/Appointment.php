<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class AppointmentController extends PublicController {

    public function __init() {
        parent::init();
        ini_set("display_errors", "off");
        error_reporting(E_ERROR | E_STRICT);
    }

    /*
     * 询报价的任务清单
     */

    public function listAction() {
        if ($this->getMethod() === 'GET') {
            $condtion = $this->getParam();

            $condtion['lang'] = $this->getParam('lang', 'zh');
        } else {
            $condtion = $this->getPut();
            $condtion['lang'] = $this->getPut('lang', 'zh');
        }
        if (in_array('201711242', $this->user['role_no'])) {
            $buyerModel = new Buyer_BuyerModel();

            $urlPermModel = new System_UrlPermModel();
            $currentPage = empty($request['currentPage']) ? 1 : $request['currentPage'];
            $pageSize = empty($request['pageSize']) ? 10 : $request['pageSize'];
            $where = ['status' => 'APPROVING',
                'country_bn' => ['in', $this->user['country_bn'] ?: ['-1']], 'deleted_flag' => 'N'];
            $buyerList = $buyerModel
                    ->field('id, status, country_bn, name,\'BUYER\' as type ,\'\' as serial_no,\'\' as inflow_time,\'\' as quote_status')
                    ->where($where)
                    ->page($currentPage, $pageSize)
                    ->order('created_at DESC')
                    ->select();
            (new Common_MarketAreaCountryModel())->setAreaBn($buyerList);
            (new Common_MarketAreaModel())->setArea($buyerList);
            (new Common_CountryModel())->setCountry($buyerList, $this->lang);
            if ($buyerList) {
                $res['code'] = '1';
                $res['message'] = L('SUCCESS');
                $res['count'] = $buyerModel->where($where)->count('id') ?: 0;
                $res['parent_id'] = $urlPermModel->getMenuIdByName('客户');
                $res['data'] = $buyerList;
            } else {
                $res['code'] = '-101';
                $res['message'] = L('NO_DATA');
            }
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn([
                'code' => '-101',
                'message' => L('FAIL')
            ]);
        }
    }

    /*
     * 询报价的任务个数
     */

    public function countAction() {


        if (!empty($this->user['country_bn']) && in_array('201711242', $this->user['role_no'])) {
            $where = ['status' => 'APPROVING',
                'country_bn' => ['in',
                    $this->user['country_bn']]
                , 'deleted_flag' => 'N'];
            $count = (new Buyer_BuyerModel())
                    ->where($where)
                    ->count();
            $count = !empty($count) ? $count : 0;
        } else {

            $count = 0;
        }


        header('Content-Type:application/json; charset=utf-8');

        if ($count) {
            $this->send['data'] = intval($count);
        } elseif ($count === 0) {
            $this->send['data'] = 0;
        }
        $this->send['code'] = $this->getCode();
        $this->send['message'] = L('SUCCESS', null, '成功！');
        exit(json_encode($this->send, JSON_UNESCAPED_UNICODE));
    }

    private function _setInflowTime($start_time, $end_time = '') {

        $end_time = $end_time ? $end_time : time();
        $start_time = strtotime($start_time);

        $distance = $end_time - $start_time;

        $day = floor($distance / 86400);
        $hour = floor($distance % 86400 / 3600);
        $minut = round($distance % 86400 % 3600 / 60);

        $distance_str = $day . L('NOTIFICATION_DAYS') . $hour . L('NOTIFICATION_HOURS') . $minut . L('NOTIFICATION_MINUTES');
        return $distance_str;
    }

}
