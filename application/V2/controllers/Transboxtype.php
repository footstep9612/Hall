<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FeeType
 * @author  zhongyg
 * @date    2017-8-1 17:34:40
 * @version V2.0
 * @desc
 */
class TransboxtypeController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /*
     * 所有计费单位
     */

    public function listAction() {
        $data = $this->getPut();

        $trans_box_type_model = new TransBoxTypeModel();

        $arr = $trans_box_type_model->getlist($data);
        $this->_setUserName($arr);
        if ($arr) {
            redisSet('TransBoxType_' . md5(json_encode($data)), json_encode($arr));
        }

        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    /*
     * 所有计费单位
     */

    public function infoAction() {
        $id = $this->get('id') ? $this->get('id') : $this->getPut('id');

        $trans_box_type_model = new TransBoxTypeModel();

        $arr = $info = $trans_box_type_model->info($id);
        if ($info) {
            $data = [$info];
            $this->_setUserName($data);
            $arr = $data[0];
        }

        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    private function _setUserName(&$arr) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                $userids[] = $val['created_by'];
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                if ($val['created_by'] && isset($usernames[$val['created_by']])) {
                    $val['created_by_name'] = $usernames[$val['created_by']];
                } else {
                    $val['created_by_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('TransBoxType*');
        $redis->delete($keys);
    }

    public function createAction() {
        $condition = $this->getPut(null);
        $trans_box_type_model = new TransBoxTypeModel();

        $result = $trans_box_type_model->create_data($condition);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function updateAction() {
        $trans_box_type_model = new TransBoxTypeModel();
        $condition = $this->getPut(null);

        if (!$condition['id']) {
            $condition['id'] = $this->get('id');
        }
        $result = $trans_box_type_model->update_data($condition);

        if ($result) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function deleteAction() {
        $trans_box_type_model = new TransBoxTypeModel();
        $id = $this->get('id') ? $this->get('id') : $this->getPut('id');
        $where['id'] = $id;
        if ($id) {
            $where['id'] = $id;
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $result = $trans_box_type_model->delete_data($id);
        if ($result) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
