<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TodoController extends PublicController {

    const inquiryIssueRole = 'A002'; //易瑞辅分单员角色编号
    const quoteIssueMainRole = 'A003'; //报价辅分单员角色编号
    const quoteIssueAuxiliaryRole = 'A004'; //报价主分单员角色编号
    const inquiryIssueAuxiliaryRole = 'A011'; //易瑞主分单员角色编号

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
        $org_model = new System_OrgModel();
        $role_nos = $this->user['role_no'];

        if (!empty($this->user['country_bn']) && in_array('201711242', $role_nos)) {
            $country_bns = '';
            foreach ($this->user['country_bn'] as $country_bn) {
                $country_bns .= '\'' . $org_model->escapeString($country_bn) . '\',';
            }
            $country_bns = rtrim($country_bns, ',');
            $confirm_sql = ' OR(`agent_id`=' . $this->user['id'] . ' and `status`=\'INQUIRY_CONFIRM\')';
            $sql_inquiry = '';
            if (
                    in_array(self::inquiryIssueRole, $role_nos) ||
                    in_array(self::quoteIssueMainRole, $role_nos)) {
                if ($this->user['group_id']) {

                    $sql_inquiry .= ' AND (`now_agent_id`=\'' . $this->user['id'] . '\'' . $confirm_sql;
                    $org_ids = $org_model->getOrgIdsById($this->user['group_id'], ['in', ['erui', 'eub']]);
                    $sql_inquiry .= ' OR (`status` in(\'BIZ_DISPATCHING\') AND org_id in(' . implode(',', $org_ids) . ')))';
                }
            } elseif (in_array(self::quoteIssueAuxiliaryRole, $role_nos) || in_array(self::inquiryIssueAuxiliaryRole, $role_nos)) {

                if ($this->user['group_id']) {

                    $sql_inquiry .= ' AND (`now_agent_id`=\'' . $this->user['id'] . '\'' . $confirm_sql;
                    $org_ids = $org_model->getOrgIdsById($this->user['group_id'], ['in', ['erui', 'eub']]);

                    $sql_inquiry .= ' OR (`status` in(\'BIZ_DISPATCHING\') '
                            . 'AND org_id in(' . implode(',', $org_ids) . ')'
                            . ' AND country_bn in(' . $country_bns . ')'
                            . '))';
                }
            } else {
                $sql_inquiry .= ' AND (`now_agent_id`=\'' . $this->user['id'] . '\'' . $confirm_sql . ')';
            }


            $sql = ' select id,serial_no,inflow_time,status,quote_status,country_bn,type,name from ('
                    . '(SELECT `id`,\'\' as serial_no,\'\' as inflow_time,`status`,\'\' as quote_status,'
                    . '`country_bn`,`name`,'
                    . '\'BUYER\' as type ,created_at as updated_at '
                    . 'FROM erui_buyer.buyer WHERE '
                    . '`status` = \'APPROVING\' AND `country_bn` IN '
                    . '(' . $country_bns . ') AND `deleted_flag` = \'N\' and `name` is not null and `name`<>\'\') '
                    . 'UNION ALL (SELECT `id`,`serial_no`,`inflow_time`,`status`,`quote_status`,`country_bn`,\'\' as name,'
                    . '\'RFQ\' as type,updated_at FROM erui_rfq.inquiry'
                    . ' WHERE `status` '
                    . 'NOT IN (\'INQUIRY_CLOSED\',\'REJECT_CLOSE\',\'QUOTE_SENT\') '
                    . 'AND `deleted_flag` = \'N\' ' . $sql_inquiry . ' )) as a order by updated_at DESC';
            $sql .= ' limit ' . (($page - 1) * $pagesize) . ',' . $pagesize;

            $list = $inquiry_model->db()->query($sql);
        } else {
            $where_inquiry = [
                'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
                'deleted_flag' => 'N'
            ];
            if (in_array(self::inquiryIssueRole, $role_nos) || in_array(self::quoteIssueMainRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsById($this->user['group_id'], ['in', ['erui', 'eub']])];
                    $map1['status'] = 'BIZ_DISPATCHING';
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;

                    $map2 = [];
                    $map2['agent_id'] = $this->user['id'];
                    $map2['status'] = 'INQUIRY_CONFIRM';
                    $map2['_logic'] = 'and';
                    $map['_complex'] = $map2;


                    $map['now_agent_id'] = $this->user['id'];
                    $map['_logic'] = 'or';
                    $where_inquiry['_complex'] = $map;
                }
            } elseif (in_array(self::quoteIssueAuxiliaryRole, $role_nos) || in_array(self::inquiryIssueAuxiliaryRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsById($this->user['group_id'], ['in', ['erui', 'eub']])];
                    $map1['status'] = ['in', ['BIZ_DISPATCHING']];
                    if ($this->user['country_bn']) {
                        $map1['country_bn'] = ['in', $this->user['country_bn']];
                    } else {
                        $map1['country_bn'] = '-1';
                    }
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;
                    $map2 = [];
                    $map2['agent_id'] = $this->user['id'];
                    $map2['status'] = 'INQUIRY_CONFIRM';
                    $map2['_logic'] = 'and';
                    $map['_complex'] = $map2;
                    $map['now_agent_id'] = $this->user['id'];
                    $map['_logic'] = 'or';
                    $where_inquiry['_complex'] = $map;
                }
            } else {
                $map2 = [];
                $map2['agent_id'] = $this->user['id'];
                $map2['status'] = 'INQUIRY_CONFIRM';
                $map2['_logic'] = 'and';
                $map['_complex'] = $map2;
                $map['now_agent_id'] = $this->user['id'];
                $map['_logic'] = 'or';
                $where_inquiry['_complex'] = $map;
            }
            $list = $inquiry_model->where($where_inquiry)
                    ->order('updated_at DESC')
                    ->field('id,serial_no,inflow_time,status,quote_status,country_bn,\'\' as name,\'RFQ\' as type')
                    ->page($page, $pagesize)
                    ->select();
        }


        (new Common_MarketAreaCountryModel())->setAreaBn($list);
        (new Common_MarketAreaModel())->setArea($list);
        (new Common_CountryModel())->setCountry($list, $this->lang);


        $rfq_parent_id = $urlPermModel
                ->getMenuIdByName('询报价');
        $buyer_parent_id = $urlPermModel->getMenuIdByName('客户');
        foreach ($list as &$item) {
            if (!empty($item['inflow_time'])) {
                $item['inflow_time'] = $this->_setInflowTime($item['inflow_time']);
            }
            switch ($item['type']) {
                case 'BUYER': $item['parent_id'] = $buyer_parent_id;
                    break;
                case 'RFQ': $item['parent_id'] = $rfq_parent_id;
                    break;
                default : $item['parent_id'] = $rfq_parent_id;
                    break;
            }
        }

        if ($list) {
            $res['code'] = '1';
            $res['message'] = L('SUCCESS');

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
                '`name` is not null and `name`<>\'\'',
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
            'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
            'deleted_flag' => 'N'
        ];
        $role_nos = $this->user['role_no'];
        $inquiry_model = new Rfq_InquiryModel();
        $org_model = new System_OrgModel();
        if (!empty($this->user['country_bn']) && in_array('201711242', $this->user['role_no'])) {
            $where = ['status' => 'APPROVING',
                'country_bn' => ['in',
                    $this->user['country_bn']]
                , '`name` is not null and `name`<>\'\'', 'deleted_flag' => 'N'];
            if (in_array(self::inquiryIssueRole, $role_nos) || in_array(self::quoteIssueMainRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsById($this->user['group_id'], ['in', ['erui', 'eub']])];
                    $map1['status'] = ['in', ['BIZ_DISPATCHING']];
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;
                    $map['now_agent_id'] = $this->user['id'];
                    $map['_logic'] = 'or';
                    $where_inquiry['_complex'] = $map;
                }
            } elseif (in_array(self::quoteIssueAuxiliaryRole, $role_nos) || in_array(self::inquiryIssueAuxiliaryRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsById($this->user['group_id'], ['in', ['erui', 'eub']])];
                    $map1['status'] = ['in', ['BIZ_DISPATCHING']];
                    if ($this->user['country_bn']) {
                        $map1['country_bn'] = ['in', $this->user['country_bn']];
                    } else {
                        $map1['country_bn'] = '-1';
                    }
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;
                    $map['now_agent_id'] = $this->user['id'];
                    $map['_logic'] = 'or';
                    $where_inquiry['_complex'] = $map;
                }
            } else {
                $where_inquiry['now_agent_id'] = $this->user['id'];
            }
            $list = $inquiry_model->field('COUNT(id) AS tp_count')
                    ->where($where_inquiry)
                    ->union(['field' => 'COUNT(id) AS tp_count',
                        'table' => (new Buyer_BuyerModel())->getTableName(), 'where' => $where], true)
                    ->select();


            if ($list) {
                foreach ($list as $val) {
                    $count += isset($val['tp_count']) ? $val['tp_count'] : 0;
                }
            }
        } else {

            if (in_array(self::inquiryIssueRole, $role_nos) || in_array(self::quoteIssueMainRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsById($this->user['group_id'], ['in', ['erui', 'eub']])];
                    $map1['status'] = ['in', ['BIZ_DISPATCHING']];
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;
                    $map['now_agent_id'] = $this->user['id'];
                    $map['_logic'] = 'or';
                    $where_inquiry['_complex'] = $map;
                }
            } elseif (in_array(self::quoteIssueAuxiliaryRole, $role_nos) || in_array(self::inquiryIssueAuxiliaryRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsById($this->user['group_id'], ['in', ['erui', 'eub']])];
                    $map1['status'] = ['in', ['BIZ_DISPATCHING']];
                    if ($this->user['country_bn']) {
                        $map1['country_bn'] = ['in', $this->user['country_bn']];
                    } else {
                        $map1['country_bn'] = '-1';
                    }
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;
                    $map['now_agent_id'] = $this->user['id'];
                    $map['_logic'] = 'or';
                    $where_inquiry['_complex'] = $map;
                }
            } else {
                $where_inquiry['now_agent_id'] = $this->user['id'];
            }


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
