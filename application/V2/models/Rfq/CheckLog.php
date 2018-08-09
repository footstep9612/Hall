<?php

/**
 * name: CheckLog.php
 * desc: 审核日志表
 * User: 张玉良
 * Date: 2017/8/21
 * Time: 9:31
 */
class Rfq_CheckLogModel extends PublicModel {

    static private $mInstance = null;
    protected $dbName = 'erui_rfq';
    protected $tableName = 'inquiry_check_log';
    static public $mError = null;

    public function __construct() {
        parent::__construct();
    }

    static public function &Instance() {
        if (null == self::$mInstance) {
            $class = __CLASS__;
            self::$mInstance = new $class;
        }
        return self::$mInstance;
    }

    function __destruct() {
        self::Close();
    }

    static public function getErrorInfo() {
        return self::$mError;
    }

    /*
     * 数据绑定
     * @param string $bind_item 要绑定的字段
     * @param string $value 绑定的数据
     *
     */

    static public function Close() {

        self::$mInstance = null;
    }

    static public function addCheckLog($inquiry_id, $out_node, $user, $in_node = null, $action = 'CREATE', $op_note = null) {

        try {
            if (empty($in_node)) {
                $log = self::Instance()->field('out_at,out_node')->where(['inquiry_id' => $inquiry_id])->order('created_at desc')->find();

                if (empty($log)) {
                    $inquiry_model = new Rfq_InquiryModel();
                    $log = ['out_node' => 'DRAFT'];
                    $log['out_at'] = $inquiry_model->where(['id' => $inquiry_id, 'deleted_flag' => 'N'])->getField('created_at');
                }
            } else {
                $log = self::Instance()->field('out_at,out_node')->where(['inquiry_id' => $inquiry_id])->order('created_at desc')->find();
                if (empty($log)) {
                    $inquiry_model = new Rfq_InquiryModel();
                    $log['out_at'] = $inquiry_model->where(['id' => $inquiry_id, 'deleted_flag' => 'N'])->getField('created_at');
                }
                $log ['out_node'] = $in_node;
            }
            if ($log['out_node'] == $out_node) {
                return false;
            }
            $data = [
                'inquiry_id' => $inquiry_id,
                'action' => $action,
                'in_node' => $log['out_node'],
                'out_node' => $out_node,
                'into_at' => !empty($log['out_at']) ? $log['out_at'] : date('Y-m-d H:i:s'),
                'out_at' => date('Y-m-d H:i:s'),
                'op_note' => $op_note,
                'agent_id' => $user['id'],
                'created_by' => $user['id'],
                'created_at' => date('Y-m-d H:i:s'),
            ];
            return self::Instance()->_addInquiryCheckLog($data, $user);
        } catch (Exception $ex) {
            self::$mError = $ex->getMessage();
            return false;
        }
    }

    static public function AddQuoteToSubmitLog($inquiry_id, $user) {
        try {
            $log = self::Instance()->field('out_at,out_node')->where(['inquiry_id' => $inquiry_id])->order('created_at desc')->find();
            $data = [
                'inquiry_id' => $inquiry_id,
                'action' => 'CREATE',
                'in_node' => 'BIZ_QUOTING',
                'out_node' => 'MARKET_APPROVING',
                'into_at' => !empty($log['out_at']) ? $log['out_at'] : date('Y-m-d H:i:s'),
                'out_at' => date('Y-m-d H:i:s'),
                'op_note' => '',
                'agent_id' => defined(UID) ? UID : 0,
                'created_by' => defined(UID) ? UID : 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            return self::Instance()->_addInquiryCheckLog($data, $user);
        } catch (Exception $ex) {
            self::$mError = $ex->getMessage();
            return false;
        }
    }

    /**
     * @desc 记录询单日志
     *
     * @param array $data
     * @return mixed
     * @author liujf
     * @time 2018-01-15
     */
    static private function _addInquiryCheckLog($data, $user) {

        try {
            $result = self::Instance()->addData($data);

            if ($result !== true) {
                return false;
            }
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
                        self::Instance()->sendSms($org_receiverInfo['mobile'], $data['action'], $org_receiverInfo['name'], $inquiryInfo['serial_no'], $user['id'], $data['in_node'], $data['out_node']);
                    } else {
                        self::Instance()->sendSms($receiverInfo['mobile'], $data['action'], $receiverInfo['name'], $inquiryInfo['serial_no'], $user['id'], $data['in_node'], $data['out_node']);
                    }
                } else {
                    self::Instance()->sendSms($receiverInfo['mobile'], $data['action'], $receiverInfo['name'], $inquiryInfo['serial_no'], $user['id'], $data['in_node'], $data['out_node']);
                }
            }


//催办测试清零
            $falg = self::Instance()->cleanInquiryRemind($data['inquiry_id']);

            if ($falg === false) {
                return false;
            }
            return true;
        } catch (Exception $ex) {
            self::$mError = $ex->getMessage();
            return false;
        }
    }

    static public function cleanInquiryRemind($inquiry_id) {
        try {
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
        } catch (Exception $ex) {
            self::$mError = $ex->getMessage();
            return false;
        }
    }

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    static public function getTime() {
        return date('Y-m-d H:i:s', time());
    }

    /**
     * 添加审核日志
     * @author zhangyuliang
     * @param array $condition
     * @return array
     */
    static public function addData($condition = []) {

        $data = self::Instance()->create($condition);

        if (empty($condition['inquiry_id'])) {

            self::$mError = '没有询单ID!';
            return false;
        }
        if (empty($condition['action'])) {

            self::$mError = '没有操作类型!';
            return false;
        }

        $time = self::Instance()->getTime();

        $data['out_at'] = $time;
        $data['created_at'] = $time;

        try {
            $id = self::Instance()->add($data);
            $data['id'] = $id;
            if ($id) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $ex) {
            self::$mError = $ex->getMessage();
            return false;
        }
    }

    /**
     * @param        $to            收信人手机号
     * @param        $action        操作说明 SUBMIT(询报价提交) REJECT(询报价退回)
     * @param        $receiver      收信人名称 如:买买提
     * @param        $serial_no     询单流程编码
     * @param        $from          发信人名称
     * @param string $areaCode      手机所属区号 默认86
     * @param int    $subType       短信发送方式  0普通文本 1模板
     * @param int    $groupSending  类型：0为单独发送，1为批量发送
     * @param string $useType       发送用途： 例如：Order、Customer、System等
     * @author 买买提
     * @return string
     */
    static public function sendSms($to, $action, $receiver, $serial_no, $from, $in_node, $out_node, $areaCode = "86", $subType = 1, $groupSending = 0, $useType = "询报价系统") {
        try {
            if (empty($receiver)) {
                jsonReturn(['code' => -104, 'message' => '收信人名字不能为空']);
            }

            if (empty($serial_no)) {
                jsonReturn(['code' => -104, 'message' => '询单流程编码不能为空']);
            }

            $data = [
                'useType' => $useType,
                'to' => '["' . $to . '"]',
                'areaCode' => $areaCode,
                'subType' => $subType,
                'groupSending' => $groupSending,
            ];

            if ($action == "CREATE") {
                $data['tplId'] = '55047';
                $data['tplParas'] = '["' . $receiver . '","' . $from . '","' . $serial_no . '"]';
            } elseif ($action == "REJECT") {
                $data['tplId'] = '55048';
                $data['tplParas'] = '["' . $receiver . '","' . $serial_no . '","' . $from . '"]';
            }
            $response = json_decode(MailHelper::sendSms($data), true); //记录短信
            if ($response['code'] == 200) {

                $smsLog = new SmsLogModel();
                $smsLog->add($smsLog->create([
                            'serial_no' => $serial_no,
                            'sms_id' => $response['message'],
                            'mobile' => $to,
                            'receiver' => $receiver,
                            'from' => $from,
                            'action' => $action,
                            'in_node' => $in_node,
                            'out_node' => $out_node,
                            'send_at' => date('Y-m-d H:i:s')
                ]));
            }

            return;
        } catch (Exception $ex) {
            self::$mError = $ex->getMessage();
            return false;
        }
    }

}
