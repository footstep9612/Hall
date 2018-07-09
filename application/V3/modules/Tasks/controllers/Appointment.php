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

    public function _remindList(&$list) {

        if ($list) {
            $inquiryRemind = new InquiryRemindModel();
            $inquiry = new InquiryModel();
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
            $country_model = new CountryModel();
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
            $marketAreaCountryModel = new MarketAreaCountryModel();
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
            $marketarea_model = new MarketAreaModel();
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
