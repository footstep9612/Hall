<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sysoplog
 * @author  zhongyg
 * @date    2017-11-24 14:50:09
 * @version V2.0
 * @desc
 */
class SysoplogController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    public function deleted() {
        $ids = $this->getPut('id');
//        $org_model = new OrgModel();
//        $org_ids = $org_model->getOrgIdsById($this->user['group_id'], 'ERUI', null);
//
//        if (!$org_ids) {
//            $this->setCode(MSG::ERROR_PARAM);
//            $this->setMessage('您不属于易瑞,没有查看权限!');
//            $this->jsonReturn();
//        }
        if (empty($ids)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择要删除的日志!');
            $this->jsonReturn();
        }
        $sysoplog_model = new SysOpLogModel();
        $flag = $sysoplog_model->deleted_data($ids);
        if ($flag) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('删除成功!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('删除失败!');
            $this->jsonReturn();
        }
    }

    /**
     * 新增系统日志文件
     * @param  string $action 操作 CREATE、UPDATE、DELETE、CHECK
     * @param  string $obj_id 对象ID
     * @param  string $uid 操作者ID
     * @param  mix $op_note 比如具体审核意见。如果是修改，可以是json串
     * @param  string $op_log 文本格式：yyyy-mm-dd hh:mm:ss 张三创建询单1
     * @param  string $op_result 操作结果：Y-成功；N-失败
     * @param  string $category 操作者ID
     * @return mix
     * @date 2017-08-01
     * @author zyg
     */
    public function createdAction() {
        try {
            $op_log_model = new SysOpLogModel();

            $category = $this->getPut('category');
            if (!$category) {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('请选择日志模块!');
                $this->jsonReturn();
            }
            $action = $this->getPut('action');
            if (!$action) {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('请选择操作方法!');
                $this->jsonReturn();
            }
            $obj_id = $this->getPut('obj_id');
            if (!$action) {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('请选择对象ID!');
                $this->jsonReturn();
            }
            $op_result = $this->getPut('op_result');
            $op_log = $this->getPut('op_log');

            $op_note = $this->getPut('op_note');
            $data['obj_id'] = $obj_id;
            $data['op_log'] = $op_log;
            $data['op_note'] = $op_note;
            $data['op_result'] = $op_result;
            return $op_log_model->create_data($data);
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

}
