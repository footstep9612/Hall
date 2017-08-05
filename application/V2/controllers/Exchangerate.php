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

    public function listAction() {
        $condtion = $this->getPut();

        $key = 'Exchange_rate_' . md5(json_encode($condtion));
        $data = redisGet($key);

        if ($data == '&&') {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn(NULL);
        } elseif (!$data) {
            $arr = $this->_model->getListbycondition($condtion);
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
