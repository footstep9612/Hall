<?php

/**
 * 询单提醒记录模型
 * @desc   InquiryRemindModel
 * @Author 买买提
 */
class Rfq_InquiryRemindModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'inquiry_remind';

    public function __construct() {
        parent::__construct();
    }

    public function remindList(&$list) {

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

}
