<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RateController
 * @author  zhongyg
 * @date    2017-8-2 13:07:21
 * @version V2.0
 * @desc   物流费率
 */
class RateController extends PublicController {

    //put your code here
    public function init() {
        //  parent::init();
        $this->_model = new RateModel();
    }

    /*
     * Description of 物流费率列表
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    public function listAction() {
        $condtion = $this->get();

        $key = 'Rate_' . md5(json_encode($condtion));
        $data = redisGet($key);

        if ($data == '&&') {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(NULL);
        } elseif (!$data) {
            $arr = $this->_model->getList($condtion);
            if ($arr) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::MSG_SUCCESS;
                $data['data'] = $arr;
                $data['count'] = $this->_model->getCount($condtion);
                redisSet($key, json_encode($data), 86400);
                $this->jsonReturn($data);
            } elseif ($arr === null) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::ERROR_EMPTY;
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

    /*
     * Description of 物流费率详情
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    public function infoAction() {
        $id = $this->get('id');
        if ($id) {
            $result = $this->_model->where(['id' => $id])->find();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } elseif ($result === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        exit;
    }

    /*
     * Description of 新建物流物流费率
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    public function createAction() {
        $condition = $this->put_data;
        $result = $this->_model->create_data($condition, $this->user['id']);
        if ($result) {
            $this->_delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 删除物流费率缓存
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    private function _delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Rate_*');
        $redis->delete($keys);
    }

    /*
     * Description of 更新物流物流费率
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    public function updateAction() {

        $condition = $this->put_data;
        $where['id'] = $this->get('id');
        $result = $this->_model->where($where)->update_data($condition, $where);
        if ($result) {
            $this->_delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 删除物流费率
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    public function deleteAction() {

        $where['id'] = $this->get('id');
        if (!$where['id']) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $result = $this->_model->where($where)->delete();
        if ($result) {
            $this->_delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
