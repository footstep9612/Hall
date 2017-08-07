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
          parent::init();
    }

    /*
     * Description of 物流费率列表
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    public function listAction() {
        $condtion = $this->getPut();


        $rate_model = new RateModel();
        if ($data == '&&') {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(NULL);
        } elseif (!$data) {
            $arr = $rate_model->getList($condtion);
            if ($arr) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::MSG_SUCCESS;
                $data['data'] = $arr;
                $data['count'] = $rate_model->getCount($condtion);

                $this->jsonReturn($data);
            } elseif ($arr === null) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::ERROR_EMPTY;
                $data['data'] = $arr;
                $data['count'] = 0;

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
        $id = $this->getPut('id');
        $rate_model = new RateModel();
        if ($id) {
            $result = $rate_model->info($id);
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
        $condition = $this->getPut();
        $rate_model = new RateModel();
        $result = $rate_model->create_data($condition, $this->user['id']);
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
        $keys = $redis->getKeys('Rate');
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
        $rate_model = new RateModel();
        $condition = $this->getPut();
        $where['id'] = $this->get('id') ?: $this->getPut('id');
        $result = $rate_model->update_data($condition, $where);
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

        $where['id'] = $this->getPut('id');
        if (!$where['id']) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }

        $result = $this->_model->where($where)->save(['status' => 'DELETED']);
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
