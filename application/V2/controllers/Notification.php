<?php

/**
 * 询报价模块提醒功能相关接口类
 * @desc   NotificationController
 * @Author 买买提
 */
class NotificationController extends PublicController {

    /**
     * 通知列表
     */
    public function listAction() {

        $request = $this->validateRequestParams();

        $page = !empty($request['currentPage']) ? $request['currentPage'] : 1;
        $pagesize = !empty($request['pageSize']) ? $request['pageSize'] : 10;

        $inquiry = new InquiryModel();

        $urlPermModel = new UrlPermModel();
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
            $buyerModel = new BuyerModel();
            $marketAreaCountryModel = new MarketAreaCountryModel();
            $marketAreaModel = new MarketAreaModel();
            $countryModel = new CountryModel();
            $urlPermModel = new UrlPermModel();
            $currentPage = empty($request['currentPage']) ? 1 : $request['currentPage'];
            $pageSize = empty($request['pageSize']) ? 10 : $request['pageSize'];
            $where = ['status' => 'APPROVING', 'country_bn' => ['in', $this->user['country_bn'] ?: ['-1']],
                '`name` is not null and `name`<>\'\'',
                'deleted_flag' => 'N'];
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

    public function getCountAction() {
        $where_inquiry = [
            'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
            'deleted_flag' => 'N'
        ];
        $role_nos = $this->user['role_no'];
        $inquiry_model = new InquiryModel();
        $org_model = new OrgModel();
        if (!empty($this->user['country_bn']) && in_array('201711242', $this->user['role_no'])) {
            $where = ['status' => 'APPROVING',
                'country_bn' => ['in',
                    $this->user['country_bn']]
                , '`name` is not null and `name`<>\'\'', 'deleted_flag' => 'N'];
            if (in_array(InquiryModel::inquiryIssueRole, $role_nos) || in_array(InquiryModel::quoteIssueMainRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsByIdAndNode($this->user['group_id'], ['in', ['erui', 'eub']])];
                    $map1['status'] = ['in', ['BIZ_DISPATCHING']];
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;
                    $map['now_agent_id'] = $this->user['id'];
                    $map['_logic'] = 'or';
                    $where_inquiry['_complex'] = $map;
                }
            } elseif (in_array(InquiryModel::quoteIssueAuxiliaryRole, $role_nos) || in_array(InquiryModel::inquiryIssueAuxiliaryRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsByIdAndNode($this->user['group_id'], ['in', ['erui', 'eub']])];
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
                        'table' => (new BuyerModel())->getTableName(), 'where' => $where], true)
                    ->select();


            if ($list) {
                foreach ($list as $val) {
                    $count += isset($val['tp_count']) ? $val['tp_count'] : 0;
                }
            }
        } else {

            if (in_array(InquiryModel::inquiryIssueRole, $role_nos) || in_array(InquiryModel::quoteIssueMainRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsByIdAndNode($this->user['group_id'], ['in', ['erui', 'eub']])];
                    $map1['status'] = ['in', ['BIZ_DISPATCHING']];
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;
                    $map['now_agent_id'] = $this->user['id'];
                    $map['_logic'] = 'or';
                    $where_inquiry['_complex'] = $map;
                }
            } elseif (in_array(InquiryModel::quoteIssueAuxiliaryRole, $role_nos) || in_array(InquiryModel::inquiryIssueAuxiliaryRole, $role_nos)) {
                if ($this->user['group_id']) {
                    $map1 = [];
                    $map1['org_id'] = ['in', $org_model->getOrgIdsByIdAndNode($this->user['group_id'], ['in', ['erui', 'eub']])];
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

    public function createAction() {

        $request = $this->validateRequestParams('id');

        //递增询单催办数
        $inquiry = new InquiryModel();
        $remind = $inquiry->where(['id' => $request['id']])->getField('remind') + 1;
        $inquiry->where(['id' => $request['id']])->save([
            'remind' => $remind,
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        //创建提醒表记录
        $inquiryRemind = new InquiryRemindModel();
        $inquiryRemind->add($inquiryRemind->create([
                    'inquiry_id' => $request['id'],
                    'created_by' => $this->user['id'],
                    'op_note' => $request['op_note'],
                    'created_at' => date('Y-m-d H:i:s')
        ]));

        $this->jsonReturn([
            'code' => 1,
            'message' => L('NOTIFICATION_SUCCESS')
        ]);
    }

    public function remindList($inquiry_id) {

        $inquiryRemind = new InquiryRemindModel();
        $inquiry = new InquiryModel();

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

    public function remindCount($inquiry_id) {

        $inquiryRemind = new InquiryRemindModel();
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
        $employee = new EmployeeModel();
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
