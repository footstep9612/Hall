<?php

/**
 * name: CheckLog.php
 * desc: 审核日志表
 * User: 张玉良
 * Date: 2017/8/21
 * Time: 9:31
 */
class Rfq_CheckLogModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'inquiry_check_log';

    public function __construct() {
        parent::__construct();
    }

    public function AddQuoteToSubmitLog($inquiry_id, $user) {

        $log = $this->field('out_at')->where(['inquiry_id' => $inquiry_id])->order('created_at desc')->find();
        $data = [
            'inquiry_id' => $inquiry_id,
            'action' => 'CREATE',
            'in_node' => 'BIZ_QUOTING',
            'out_node' => 'MARKET_APPROVING',
            'into_at' => !empty($log['out_at']) ? $log['out_at'] : date('Y-m-d H:i:s'),
            'out_at' => date('Y-m-d H:i:s'),
            'op_note' => '',
            'agent_id' => $user['id'],
            'created_by' => $user['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ];
        return $this->_addInquiryCheckLog($data, $user);
    }

    /**
     * @desc 记录询单日志
     *
     * @param array $data
     * @return mixed
     * @author liujf
     * @time 2018-01-15
     */
    private function _addInquiryCheckLog($data, $user) {


        $result = $this->addData($data);
//发送短信
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['id' => $data['inquiry_id']])->field('now_agent_id,serial_no,org_id')->find();
        $employeeModel = new EmployeeModel();
        $receiverInfo = $employeeModel
                        ->where(['id' => $inquiryInfo['now_agent_id']])
                        ->field('name,mobile,email')->find();
        if (!in_array($data['out_node'], ['QUOTE_SENT', 'INQUIRY_CLOSED'])) {
            if ($data['out_node'] == 'BIZ_DISPATCHING' && !empty($inquiryInfo['org_id'])) {
                $org_receiverInfo = (new OrgMemberModel())->getSmsUserByOrgId($inquiryInfo['org_id']);
                if (!empty($user)) {
                    $this->sendSms($org_receiverInfo['mobile'], $data['action'], $org_receiverInfo['name'], $inquiryInfo['serial_no'], $user['id'], $data['in_node'], $data['out_node']);
                } else {
                    $this->sendSms($receiverInfo['mobile'], $data['action'], $receiverInfo['name'], $inquiryInfo['serial_no'], $user['id'], $data['in_node'], $data['out_node']);
                }
            } else {
                $this->sendSms($receiverInfo['mobile'], $data['action'], $receiverInfo['name'], $inquiryInfo['serial_no'], $user['id'], $data['in_node'], $data['out_node']);
            }
        }


//催办测试清零
        $falg = $this->cleanInquiryRemind($data['inquiry_id']);

        if ($falg === false) {
            return false;
        }
        return $result;
    }

    public function cleanInquiryRemind($inquiry_id) {

        $inquiryModel = new InquiryModel();
        $falg = $inquiryModel->where(['id' => $inquiry_id])->save(['remind' => 0]);

        if ($falg === false) {
            return false;
        }
//提醒详情标为已读
        $inquiryRemind = new InquiryRemindModel();
        $falg = $inquiryRemind->where(['inquiry_id' => $inquiry_id])->save([
            'isread_flag' => 'Y',
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        if ($falg === false) {
            return false;
        }
        return true;
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d H:i:s', time());
    }

    /**
     * 添加审核日志
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    public function addData($condition = []) {
        $data = $this->create($condition);

        if (empty($condition['inquiry_id'])) {
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }
        if (empty($condition['action'])) {
            $results['code'] = '-103';
            $results['message'] = '没有操作类型!';
            return $results;
        }

        $time = $this->getTime();

        $data['out_at'] = $time;
        $data['created_at'] = $time;

        try {
            $id = $this->add($data);
            $data['id'] = $id;
            if ($id) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $data;
            } else {
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
        }

        return $results;
    }

}
