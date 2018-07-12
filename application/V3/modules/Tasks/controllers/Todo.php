<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TodoController extends PublicController {

    public function __init() {
        parent::init();
        ini_set("display_errors", "off");
        error_reporting(E_ERROR | E_STRICT);
    }

    /**
     * 通知列表
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

        $inquiry_model = new Rfq_InquiryModel();
        $urlPermModel = new System_UrlPermModel();

        if (!empty($this->user['country_bn']) && in_array('201711242', $this->user['role_no'])) {
            $country_bns = '';
            foreach ($this->user['country_bn'] as $country_bn) {
                $country_bns .= '\'' . $country_bn . '\',';
            }
            $country_bns = rtrim($country_bns, ',');
            $sql = ' select id,serial_no,inflow_time,status,quote_status,country_bn,type,name from ('
                    . '(SELECT `id`,`serial_no`,`inflow_time`,`status`,`quote_status`,`country_bn`,\'\' as name,'
                    . '\'RFQ\' as type,updated_at FROM erui_rfq.inquiry'
                    . ' WHERE `now_agent_id` = ' . $this->user['id'] . ' '
                    . 'AND `status` '
                    . 'NOT IN (\'INQUIRY_CLOSED\',\'REJECT_CLOSE\',\'QUOTE_SENT\') '
                    . 'AND `deleted_flag` = \'N\' ) '
                    . 'UNION ALL (SELECT `id`,\'\' as serial_no,\'\' as inflow_time,`status`,\'\' as quote_status,'
                    . '`country_bn`,`name`,'
                    . '\'BUYER\' as type ,created_at as updated_at '
                    . 'FROM erui_buyer.buyer WHERE '
                    . '`status` = \'APPROVING\' AND `country_bn` IN '
                    . '(' . $country_bns . ') AND `deleted_flag` = \'N\' )) as a order by updated_at DESC';
            $sql .= ' limit ' . (($page - 1) * $pagesize) . ',' . $pagesize;

            $list = $inquiry_model->db()->query($sql);
        } else {
            $where_inquiry = [
                'now_agent_id' => $this->user['id'],
                'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
                'deleted_flag' => 'N'
            ];
            $list = $inquiry_model->where($where_inquiry)
                    ->order('updated_at DESC')
                    ->field('id,serial_no,inflow_time,status,quote_status,country_bn,\'\' as name,\'RFQ\' as type')
                    ->page($page, $pagesize)
                    ->select();
        }


        (new Common_MarketAreaCountryModel())->setAreaBn($list);
        (new Common_MarketAreaModel())->setArea($list);
        (new Common_CountryModel())->setCountry($list, $this->lang);
        // (new Rfq_InquiryRemindModel)->_remindList($list);
        foreach ($list as &$item) {
            if (!empty($item['inflow_time'])) {
                $item['inflow_time'] = $this->_setInflowTime($item['inflow_time']);
            }
        }

        if ($list) {
            $res['code'] = '1';
            $res['message'] = L('SUCCESS');
            $res['rfq_parent_id'] = $urlPermModel
                    ->getMenuIdByName('询报价');
            $res['buyer_parent_id'] = $urlPermModel->getMenuIdByName('客户');
            $res['data'] = $list;
        } else {
            $res['code'] = '-101';
            $res['message'] = L('NO_DATA');
        }
        $this->jsonReturn($res);
    }

    /**
     * @desc 待办客户列表
     *
     * @author liujf
     * @time 2018-06-19
     */
    public function buyerListAction() {
        $request = $this->validateRequestParams();
        // 是否有市场区域（国家）负责人的角色
        if (in_array('201711242', $this->user['role_no'])) {
            $buyerModel = new Buyer_BuyerModel();

            $urlPermModel = new System_UrlPermModel();
            $currentPage = empty($request['currentPage']) ? 1 : $request['currentPage'];
            $pageSize = empty($request['pageSize']) ? 10 : $request['pageSize'];
            $where = ['status' => 'APPROVING',
                'country_bn' => ['in', $this->user['country_bn'] ?: ['-1']], 'deleted_flag' => 'N'];
            $buyerList = $buyerModel
                    ->field('id, status, country_bn, name')
                    ->where($where)
                    ->page($currentPage, $pageSize)
                    ->order('id DESC')
                    ->select();
            (new Common_MarketAreaCountryModel())->setAreaBn($buyerList);
            (new Common_MarketAreaModel())->setArea($buyerList);
            (new Common_CountryModel())->setCountry($buyerList);
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

    public function countAction() {
        $where_inquiry = [
            'now_agent_id' => $this->user['id'],
            'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
            'deleted_flag' => 'N'
        ];
        $inquiry_model = new Rfq_InquiryModel();
        if (!empty($this->user['country_bn']) && in_array('201711242', $this->user['role_no'])) {
            $where = ['status' => 'APPROVING',
                'country_bn' => ['in',
                    $this->user['country_bn']]
                , 'deleted_flag' => 'N'];
            $list = $inquiry_model->field('COUNT(id) AS tp_count')
                    ->where($where_inquiry)
                    ->union(['field' => 'COUNT(id) AS tp_count',
                        'table' => (new Buyer_BuyerModel())->getTableName(), 'where' => $where], true)
                    ->select();
            $count = 0;

            if ($list) {
                foreach ($list as $val) {
                    $count += isset($val['tp_count']) ? $val['tp_count'] : 0;
                }
            }
        } else {
            $where_inquiry = [
                'now_agent_id' => $this->user['id'],
                'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
                'deleted_flag' => 'N'
            ];

            $count = $inquiry_model->where($where_inquiry)->count('id') ?: 0;
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

        $distance_str = $day . L('NOTIFICATION_DAYS')
                . $hour . L('NOTIFICATION_HOURS')
                . $minut . L('NOTIFICATION_MINUTES');
        return $distance_str;
    }

}
