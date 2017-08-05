<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cxchangerate
 *
 * @author zhongyg
 */
class ExchangerateController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
        $this->_model = new ExchangeRateModel();
    }

    /* 获取分类列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   汇率列表
     */

    public function listAction() {
        $condtion = $this->getPut();
        unset($condtion['token']);
        $key = 'Exchange_rate_' . md5(json_encode($condtion));
        $data = redisGet($key);

        if ($data == '&&') {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn(NULL);
        } elseif (!$data) {
            $arr = $this->_model->getListbycondition($condtion);
            $this->_setUserName($arr);
            $this->_setCurrency($arr, 'cur_bn1', 'cur_bn2');

            if ($arr) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::MSG_SUCCESS;
                $data['data'] = $arr;
                $data['count'] = $this->_model->getCount($condtion);
                redisSet($key, json_encode($data), 86400);
                $this->jsonReturn($data);
            } elseif ($arr === null) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::MSG_SUCCESS;
                $data['data'] = $arr;
                $data['count'] = 0;
                redisSet($key, '&&', 86400);
                $this->jsonReturn(null);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        } else {
            $data = json_decode($data, true);
            $data['code'] = MSG::MSG_SUCCESS;
            $this->jsonReturn($data);
        }
    }

    /* id转换为姓名
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   汇率列表
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

    /* id转换为姓名
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   汇率列表
     */

    private function _setCurrency(&$arr, $field1, $field2) {
        if ($arr) {
            $currency_model = new CurrencyModel();
            $currency_bns = [];
            foreach ($arr as $key => $val) {
                $currency_bns[] = $val[$field1];
                $currency_bns[] = $val[$field2];
            }
            $curs = $currency_model->getNameByBns($currency_bns);
            foreach ($arr as $key => $val) {
                if ($val[$field1] && isset($curs[$val[$field1]])) {
                    $val[$field1] = $val[$field1] . '_' . $curs[$val[$field1]];
                }
                if ($val[$field2] && isset($curs[$val[$field2]])) {
                    $val[$field2] = $val[$field2] . '_' . $curs[$val[$field2]];
                }
                $arr[$key] = $val;
            }
        }
    }

    /**
     * 分类联动
     */
    public function infoAction() {
        $id = $this->getPut('id');
        if ($id) {
            $result = $this->_model->field('id,effective_date,cur_bn1,cur_bn2,rate')->where(['id' => $id])->find();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        exit;
    }

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Exchange_rate_*');
        $redis->delete($keys);
    }

    public function createAction() {
        $condition = $this->getPut();
        $result = $this->_model->create_data($condition, $this->user['id']);
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

        $condition = $this->getPut();
        $where['id'] = $this->getPut('id');
        $result = $this->_model->update_data($condition, $where);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function deleteAction() {

        $where['id'] = $this->getPut('id');
        if (!$where['id']) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $result = $this->_model->where($where)->delete();
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
