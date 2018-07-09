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

        $inquiry = new Rfq_InquiryModel();

        $urlPermModel = new System_UrlPermModel();
        $where = [
            'now_agent_id' => $this->user['id'],
            'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
            'deleted_flag' => 'N'
        ];
        $list = $inquiry->where($where)
                ->order('updated_at DESC')
                ->field('id,serial_no,inflow_time,status,quote_status,country_bn')
                ->page($page, $pagesize)
                ->select();
        $this->_setAreaBn($list);
        $this->_setArea($list);
        $this->_setCountry($list);
        $this->_remindList($list);
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
            $where = ['status' => 'APPROVING', 'country_bn' => ['in', $this->user['country_bn'] ?: ['-1']], 'deleted_flag' => 'N'];
            $buyerList = $buyerModel
                    ->field('id, status, country_bn, name')
                    ->where($where)
                    ->page($currentPage, $pageSize)
                    ->order('id DESC')
                    ->select();
            $this->_setAreaBn($buyerList);
            $this->_setArea($buyerList);
            $this->_setCountry($buyerList);
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

    public function remindList($inquiry_id) {

        $inquiryRemind = new Rfq_InquiryRemindModel();
        $inquiry = new Rfq_InquiryModel();

        $where = ['inquiry_id' => $inquiry_id, 'isread_flag' => 'N'];
        $data = $inquiryRemind->where($where)->field('inquiry_id, created_by, created_at')->select();
        foreach ($data as &$datum) {
            $datum['role_name'] = $this->_setRoleName($inquiry->getUserRoleById($datum['created_by']));
            $datum['created_by'] = $this->_setUserName($datum['created_by']);
        }

        return $data;
    }

    public function _remindList(&$list) {

        if ($list) {
            $inquiryRemind = new Rfq_InquiryRemindModel();
            $inquiry = new Rfq_InquiryModel();
            $inquiry_ids = [];
            foreach ($list as $item) {
                $inquiry_ids[] = $item['id'];
            }

            $where = ['inquiry_id' => ['in', $inquiry_ids], 'isread_flag' => 'N'];
            $data = $inquiryRemind->where($where)->field('inquiry_id, created_by, created_at')->select();
            $ret = [];
            foreach ($data as &$datum) {
                $datum['role_name'] = $this->_setRoleName($inquiry->getUserRoleById($datum['created_by']));
                $datum['created_by'] = $this->_setUserName($datum['created_by']);
                $ret[$datum['inquiry_id']][] = $datum;
            }
            foreach ($list as $key => $item) {
                if (!empty($ret[$item['id']])) {

                    $item['remind_count'] = count($ret[$item['id']]);
                    $item['remind_list'] = $ret[$item['id']];
                } else {

                    $item['remind_count'] = 0;
                    $item['remind_list'] = [];
                }
                $list[$key] = $item;
            }
            return $data;
        }
    }

    public function remindCount($inquiry_id) {

        $inquiryRemind = new Rfq_InquiryRemindModel();
        $where = ['isread_flag' => 'N'];
        if ($inquiry_id && is_numeric($inquiry_id)) {
            $where = ['inquiry_id' => $inquiry_id];
        } elseif ($inquiry_id && is_array($inquiry_id)) {
            $where = ['inquiry_id' => ['in', $inquiry_id]];
        }
        $data = $inquiryRemind->where($where)->count();
        return $data;
    }

    private function _setRoleName($rolaArr) {
        if (!empty($rolaArr)) {
            if ($rolaArr['is_agent'] == 'Y') {
                return L('NOTIFICATION_AGENT');
            } elseif ($rolaArr['is_check'] == 'Y') {
                return L('NOTIFICATION_CHECK');
            } elseif ($rolaArr['is_country_agent'] == 'Y') {
                return L('NOTIFICATION_COUNTRY_AGENT');
            } elseif ($rolaArr['is_erui'] == 'Y') {
                return L('NOTIFICATION_ERUI');
            } elseif ($rolaArr['is_issue'] == 'Y') {
                return L('NOTIFICATION_ISSUE');
            } elseif ($rolaArr['is_quote'] == 'Y') {
                return L('NOTIFICATION_QUOTE');
            }
        }
    }

    private function _setUserName($id) {
        $employee = new System_EmployeeModel();
        return $employee->where(['id' => $id])->getField('name');
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

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setCountry(&$arr) {
        if ($arr) {
            $country_model = new Common_CountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val['country_bn']);
            }
            $countrynames = $country_model->getNamesBybns($country_bns, $this->lang);
            foreach ($arr as $key => $val) {
                if (trim($val['country_bn']) && isset($countrynames[trim($val['country_bn'])])) {
                    $val['country_name'] = $countrynames[trim($val['country_bn'])];
                } else {
                    $val['country_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setAreaBn(&$arr) {
        if ($arr) {
            $marketAreaCountryModel = new Common_MarketAreaCountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val['country_bn']);
            }
            $area_bns = $marketAreaCountryModel
                            ->field('country_bn,market_area_bn')
                            ->where(['country_bn' => ['in', $country_bns]])->select();


            $countrytoarea_bns = [];
            foreach ($area_bns as $item) {
                $countrytoarea_bns[$item['country_bn']] = $item['market_area_bn'];
            }

            foreach ($arr as $key => $val) {
                if (trim($val['country_bn']) && isset($countrytoarea_bns[trim($val['country_bn'])])) {
                    $val['area_bn'] = $countrytoarea_bns[trim($val['country_bn'])];
                } else {
                    $val['area_bn'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取营销区域
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setArea(&$arr) {
        if ($arr) {
            $marketarea_model = new Common_MarketAreaModel();
            $area_bns = [];
            foreach ($arr as $key => $val) {
                $area_bns[] = trim($val['area_bn']);
            }
            $area_names = $marketarea_model->getNamesBybns($area_bns);
            foreach ($arr as $key => $val) {
                if (trim($val['area_bn']) && isset($area_names[trim($val['area_bn'])])) {
                    $val['area_name'] = $area_names[trim($val['area_bn'])];
                } else {
                    $val['area_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

}
