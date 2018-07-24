<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class RfqController extends PublicController {

    public function __init() {
        parent::init();
        ini_set("display_errors", "off");
        error_reporting(E_ERROR | E_STRICT);
    }

    public function inquiryListAction() {
        if ($this->getMethod() === 'GET') {
            $condition = $this->getParam();

            $condition['lang'] = $this->getParam('lang', 'zh');
        } else {
            $condition = $this->getPut();
            $condition['lang'] = $this->getPut('lang', 'zh');
        }

        $inquiry_model = new Rfq_InquiryModel();
        $list = $inquiry_model->getList($condition);
        //  (new Common_MarketAreaCountryModel())->setAreaBn($list);
        //  (new Common_MarketAreaModel())->setArea($list);
        (new Common_CountryModel())->setCountry($list, $this->lang);
        (new System_EmployeeModel())->setUserName($list, ['agent_name' => 'agent_id', 'quote_name' => 'quote_id',
            'now_agent_name' => 'now_agent_id', 'created_name' => 'created_by', 'obtain_name' => 'obtain_id']);

        // (new Buyer_BuyerModel())->setBuyerNo($list);
        //(new Rfq_QuoteModel())->setLogiQuoteFlag($list);
        // (new System_OrgModel())->setOrgName($list);
        //  (new Common_TransModeModel())->setTransModeName($list);
        //  (new Rfq_InquiryOrderModel())->setContractNo($list);
        if ($list) {
            $res['code'] = 1;
            $res['message'] = L('SUCCESS');
            $res['data'] = $list;
            $res['count'] = $inquiry_model->getCount($condition);
            $this->jsonReturn($res);
        } else {
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            $this->jsonReturn();
        }
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
        $page = !empty($condtion['currentPage']) ? $condtion['currentPage'] : 1;
        $pagesize = !empty($condtion['pageSize']) ? $condtion['pageSize'] : 10;

        $inquiry = new Rfq_InquiryModel();

        $urlPermModel = new System_UrlPermModel();
        $where = [
            'now_agent_id' => $this->user['id'],
            'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
            'deleted_flag' => 'N'
        ];
        $list = $inquiry->where($where)
                ->order('updated_at DESC')
                ->field('id,serial_no,inflow_time,status,quote_status,country_bn,\'RFQ\' as type,\'\' as name')
                ->page($page, $pagesize)
                ->select();
        (new Common_MarketAreaCountryModel())->setAreaBn($list);
        (new Common_MarketAreaModel())->setArea($list);
        (new Common_CountryModel())->setCountry($list, $this->lang);
        // (new Rfq_InquiryRemindModel)->_remindList($list);
        foreach ($list as &$item) {
            $item['inflow_time'] = $this->_setInflowTime($item['inflow_time']);
        }

        if ($list) {
            $res['code'] = '1';
            $res['message'] = L('SUCCESS');
            $res['count'] = $inquiry
                            ->where($where)
                            ->count('id') ?: 0;
            $res['parent_id'] = $urlPermModel
                    ->getMenuIdByName('询报价');
            $res['data'] = $list;
        } else {
            $res['code'] = '-101';
            $res['message'] = L('NO_DATA');
        }
        $this->jsonReturn($res);
    }

    /*
     * 询报价的任务个数
     */

    public function countAction() {

        $inquiry_model = new Rfq_InquiryModel();

        $where_inquiry = [
            'now_agent_id' => $this->user['id'],
            'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
            'deleted_flag' => 'N'
        ];

        $count = $inquiry_model->where($where_inquiry)->count('id') ?: 0;
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

        $real_end_time = $end_time ? $end_time : time();
        $real_start_time = strtotime($start_time);

        $distance = $real_end_time - $real_start_time;

        $day = floor($distance / 86400);
        $hour = floor($distance % 86400 / 3600);
        $minut = round($distance % 86400 % 3600 / 60);

        $distance_str = $day . L('NOTIFICATION_DAYS') . $hour . L('NOTIFICATION_HOURS') . $minut . L('NOTIFICATION_MINUTES');
        return $distance_str;
    }

}
