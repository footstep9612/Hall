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
        $marketAreaCountryModel = new MarketAreaCountryModel();
        $marketAreaModel = new MarketAreaModel();
        $countryModel = new CountryModel();
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

        foreach ($list as &$item) {
            $item['remind_count'] = count($this->remindList($item['id']));
            $item['remind_list'] = $this->remindList($item['id']);
            $item['inflow_time'] = $this->_setInflowTime($item['inflow_time']);
            $item['area_bn'] = $marketAreaCountryModel->where(['country_bn' => $item['country_bn']])->getField('market_area_bn');
            $item['area_name'] = $marketAreaModel->getAreaNameByBn($item['area_bn'], $this->lang);
            $item['country_name'] = $countryModel->getCountryNameByBn($item['country_bn'], $this->lang);
        }

        if ($list) {
            $res['code'] = '1';
            $res['message'] = L('SUCCESS');
            $res['count'] = $inquiry->where($where)->count('id') ?: 0;
            $res['parent_id'] = $urlPermModel->getMenuIdByName('询报价');
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
            $where = ['status' => 'APPROVING', 'country_bn' => ['in', $this->user['country_bn'] ?: ['-1']], 'deleted_flag' => 'N'];
            $buyerList = $buyerModel->field('id, status, country_bn, name')
                    ->where($where)
                    ->page($currentPage, $pageSize)
                    ->order('id DESC')
                    ->select();
            foreach ($buyerList as &$buyer) {
                $buyer['area_bn'] = $marketAreaCountryModel->where(['country_bn' => $buyer['country_bn']])->getField('market_area_bn');
                $buyer['area_name'] = $marketAreaModel->getAreaNameByBn($buyer['area_bn'], $this->lang);
                $buyer['country_name'] = $countryModel->getCountryNameByBn($buyer['country_bn'], $this->lang);
            }
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
            'now_agent_id' => $this->user['id'],
            'status' => ['not in', ['INQUIRY_CLOSED', 'REJECT_CLOSE', 'QUOTE_SENT']],
            'deleted_flag' => 'N'
        ];
        $inquiry_model = new InquiryModel();

        if ($this->user['country_bn']) {
            $where = ['status' => 'APPROVING',
                'country_bn' => ['in',
                    $this->user['country_bn']]
                , 'deleted_flag' => 'N'];
            $list = $inquiry_model->field('COUNT(id) AS tp_count')
                    ->where($where_inquiry)
                    ->union(['field' => 'COUNT(id) AS tp_count',
                        'table' => (new BuyerModel)->getTableName(), 'where' => $where], true)
                    ->select();
            $count = 0;
            echo $inquiry_model->_sql();
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

}
