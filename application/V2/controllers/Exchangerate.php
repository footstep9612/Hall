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
        $key = $condtion['cur_bn1'] . $condtion['cur_bn2'] . $condtion['effective_date'];
        $data = redisHashGet('Exchange_rate', $key);

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
                redisHashGet('Exchange_rate', $key, json_encode($data));
                $this->jsonReturn($data);
            } elseif ($arr === null) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::MSG_SUCCESS;
                $data['data'] = $arr;
                $data['count'] = 0;
                redisHashGet('Exchange_rate', $key, '&&');
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
                    $val[$field1] = $val[$field1] . '/' . $curs[$val[$field1]];
                }
                if ($val[$field2] && isset($curs[$val[$field2]])) {
                    $val[$field2] = $val[$field2] . '/' . $curs[$val[$field2]];
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

        if (empty($condition['cur_bn1'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('币种不能为空!');
            $this->jsonReturn();
        }
        if (empty($condition['cur_bn2'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('承兑币种不能为空!');
            $this->jsonReturn();
        }
        if (empty($condition['effective_date'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('生效日期不能为空!');
            $this->jsonReturn();
        } elseif (!$this->isDateTime($condition['effective_date'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('生效日期格式不对!');
            $this->jsonReturn();
        }

        if (empty($condition['rate'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('汇率不能为空!');
            $this->jsonReturn();
        } elseif (empty(floatval($condition['rate']))) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('汇率必须是浮点数字!');
            $this->jsonReturn();
        }
        $result = $this->_model->create_data($condition);

        if ($this->_model->error) {
            $this->setMessage($this->_model->error);
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    function isDateTime($dateTime) {
        $ret = strtotime($dateTime . '-01');
        return $ret !== FALSE && $ret != -1;
    }

    public function updateAction() {

        $condition = $this->getPut();
        if (empty($condition['id'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('更新ID不能为空!');
            $this->jsonReturn();
        }
        if (empty($condition['cur_bn1'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('币种不能为空!');
            $this->jsonReturn();
        }
        if (empty($condition['cur_bn2'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('承兑币种不能为空!');
            $this->jsonReturn();
        }
        if (empty($condition['effective_date'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('生效日期不能为空!');
            $this->jsonReturn();
        } elseif (!$this->isDateTime($condition['effective_date'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('生效日期格式不对!');
            $this->jsonReturn();
        }

        if (empty($condition['rate'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('汇率不能为空!');
            $this->jsonReturn();
        } elseif (empty(floatval($condition['rate']))) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('汇率必须是浮点数字!');
            $this->jsonReturn();
        } elseif (floatval($condition['rate']) <= 0) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('汇率必须大于零!');
            $this->jsonReturn();
        }
        $where['id'] = $this->getPut('id');
        $result = $this->_model->update_data($condition, $where);

        if ($result !== false) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function deleteAction() {

        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('要删除的汇率ID不能为空!');
            $this->jsonReturn();
        }
        $result = $this->_model->delete_data($id);
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
