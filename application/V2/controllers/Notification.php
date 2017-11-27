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
        $list = $inquiry->where(['now_agent_id'=>$this->user['id']])->order('id DESC')->field('id,serial_no,inflow_time,status,quote_status')->page($page, $pagesize)->select();

        foreach ($list as &$item){
            $item['remind_count'] = count($this->remindList($item['id']));
            $item['remind_list'] = $this->remindList($item['id']);
            $item['inflow_time'] = $this->_setInflowTime($item['inflow_time']);
        }

        $this->jsonReturn([
            'code'    => 1,
            'message' => '成功!',
            'count'   => count($inquiry->where(['now_agent_id'=>$this->user['id']])->order('id DESC')->field('id,serial_no,inflow_time,status,quote_status')->select()),
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

        $this->jsonReturn();

    }

    public function remindList($inquiry_id)
    {

        $inquiryRemind = new InquiryRemindModel();
        $inquiry = new InquiryModel();

        $where = ['inquiry_id'=>$inquiry_id];
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
                return "市场经办人";
            }elseif ($rolaArr['is_check'] == 'Y'){
                return "报价审核人";
            }elseif ($rolaArr['is_country_agent'] == 'Y'){
                return "区域负责人";
            }elseif ($rolaArr['is_erui'] == 'Y'){
                return "易瑞分单员";
            }elseif ($rolaArr['is_issue'] == 'Y'){
                return "事业部分单员";
            }elseif ($rolaArr['is_quote'] == 'Y'){
                return "报价人";
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

        $distance_str = $day."天".$hour."小时".$minut."分钟";
        return $distance_str;

    }

}

