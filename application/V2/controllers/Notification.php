<?php

/**
 * 询报价模块提醒功能相关接口类
 * @desc   NotificationController
 * @Author 买买提
 */
class NotificationController extends PublicController
{

    /**
     * 通知列表
     */
    public function listAction()
    {

        $request = $this->validateRequestParams();

        $page = !empty($request['currentPage']) ? $request['currentPage'] : 1;
        $pagesize = !empty($request['pageSize']) ? $request['pageSize'] : 10;

        $inquiry = new InquiryModel();

        $list = $inquiry->where(['now_agent_id'=>$this->user['id'],'deleted_flag'=>'N'])
                        ->where("status !='INQUIRY_CLOSED' and status !='QUOTE_SENT'")
                        ->order('id DESC')
                        ->field('id,serial_no,inflow_time,status,quote_status,country_bn')
                        ->page($page, $pagesize)
                        ->select();

        foreach ($list as &$item){
            $item['remind_count'] = count($this->remindList($item['id']));
            $item['remind_list'] = $this->remindList($item['id']);
            $item['inflow_time'] = $this->_setInflowTime($item['inflow_time']);
        }

        $this->jsonReturn([
            'code'    => 1,
            'message' => L('NOTIFICATION_SUCCESS'),
            'count'   => count($inquiry->where(['now_agent_id'=>$this->user['id'],'deleted_flag'=>'N'])->where("status !='INQUIRY_CLOSED' and status !='QUOTE_SENT'")->field('id')->select()),
            'data'    => $list
        ]);

    }

    public function createAction()
    {

        $request = $this->validateRequestParams('id');

        //递增询单催办数
        $inquiry = new InquiryModel();
        $remind = $inquiry->where(['id'=>$request['id']])->getField('remind') + 1;
        $inquiry->where(['id'=>$request['id']])->save([
            'remind'=>$remind,
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
            'code'     => 1,
            'message' => L('NOTIFICATION_SUCCESS')
        ]);

    }

    public function remindList($inquiry_id)
    {

        $inquiryRemind = new InquiryRemindModel();
        $inquiry = new InquiryModel();

        $where = ['inquiry_id'=>$inquiry_id,'isread_flag'=>'N'];
        $data = $inquiryRemind->where($where)->field('inquiry_id,created_by,created_at')->select();

        foreach ($data as &$datum){
            $datum['role_name'] = $this->_setRoleName($inquiry->getUserRoleById($datum['created_by']));
            $datum['created_by'] = $this->_setUserName($datum['created_by']);
        }

        return $data;

    }

    private function _setRoleName($rolaArr)
    {
        if (!empty($rolaArr)){
            if ($rolaArr['is_agent'] == 'Y'){
                return L('NOTIFICATION_AGENT');
            }elseif ($rolaArr['is_check'] == 'Y'){
                return L('NOTIFICATION_CHECK');
            }elseif ($rolaArr['is_country_agent'] == 'Y'){
                return L('NOTIFICATION_COUNTRY_AGENT');
            }elseif ($rolaArr['is_erui'] == 'Y'){
                return L('NOTIFICATION_ERUI');
            }elseif ($rolaArr['is_issue'] == 'Y'){
                return L('NOTIFICATION_ISSUE');
            }elseif ($rolaArr['is_quote'] == 'Y'){
                return L('NOTIFICATION_QUOTE');
            }
        }
    }

    private function _setUserName($id)
    {
        $employee = new EmployeeModel();
        return $employee->where(['id'=>$id])->getField('name');
    }

    private function _setInflowTime($start_time, $end_time='')
    {

        $end_time = $end_time ? $end_time : time();
        $start_time = strtotime($start_time);

        $distance = $end_time - $start_time;

        $day = round( $distance / 86400 );
        $hour = round( ($distance % 86400) / 3600 );
        $minut = round( ($distance % 3600) / 60 );

        $distance_str = $day.L('NOTIFICATION_DAYS').$hour.L('NOTIFICATION_HOURS').$minut.L('NOTIFICATION_MINUTES');
        return $distance_str;

    }

}

