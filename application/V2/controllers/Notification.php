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

        $this->validateRequestParams();

        $inquiry = new InquiryModel();
        $list = $inquiry->where(['now_agent_id'=>$this->user['id']])->order('id DESC')->field('id,serial_no,inflow_time,status,quote_status')->select();

        foreach ($list as &$item){
            $item['remind_count'] = count($this->remindList($item['id']));
            $item['remind_list'] = $this->remindList($item['id']);
            $item['inflow_time'] = $this->_setInflowTime($item['inflow_time']);
        }

        $this->jsonReturn([
            'code'    => 1,
            'message' => '成功!',
            'count'   => count($list),
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
            'created_at' => date('Y-m-d H:i:s')
        ]));

        $this->jsonReturn();

    }

    public function remindList($inquiry_id)
    {

        $inquiryRemind = new InquiryRemindModel();

        $where = ['inquiry_id'=>$inquiry_id];
        $data = $inquiryRemind->where($where)->field('inquiry_id,created_by,created_at')->select();

        foreach ($data as &$datum){
            $datum['created_by'] = $this->_setUserName($datum['created_by']);
            $datum['role_name'] = $this->_setRoleName($datum['created_by']);
        }

        return $data;

    }

    private function _setRoleName($id)
    {


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

        $distance_str = $day."天".$hour."小时".$minut."分钟";
        return $distance_str;

    }

}

