<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogiRate
 * @author  zhongyg
 * @date    2017-8-3 15:30:59
 * @version V2.0
 * @desc  物流费率 
 */
class LogiRateController extends PublicController {

    //put your code here
    public function init() {
        //parent::init();
    }

    /**
     * Description of MarketAreaModel
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function listAction() {
        $data = $this->get() ?: $this->getPut();

        $Logi_Rate_model = new LogiRateModel();
        if (redisGet('Logi_Rate_listall_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('Logi_Rate_listall_' . md5(json_encode($data))), true);
        } else {
            $arr = $Logi_Rate_model->getlist($data, false);
            if ($arr) {
                redisSet('Logi_Rate_listall_' . md5(json_encode($data)), json_encode($arr));
            }
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

    /**
     * Description of 详情
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function infoAction() {
        $id = $this->get('id') ?: $this->getPut('id');

        if (!$id) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $Logi_Rate_model = new LogiRateModel();
        $result = $Logi_Rate_model->info($id);
        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } elseif ($result === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        exit;
    }

    /**
     * Description of 删除缓存
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Logi_Rate_list_*');
        $redis->delete($keys);
    }

    /**
     * Description of 新增物流费率
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function createAction() {
        $data = $this->getPut();
        $Logi_Rate_model = new LogiRateModel();
        $result = $Logi_Rate_model->create_data($data, $this->user['id']);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新物流费率
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function updateAction() {
        $id = $this->get('id') ?: $this->getPut('id');
        $data = $this->getPut();
        if (!isset($data['id']) || !$data['id']) {
            $data['id'] = $id;
        }
        $Logi_Rate_model = new LogiRateModel();
        $result = $Logi_Rate_model->update_data($data, $this->user['id']);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * Description of 删除物流费率
     * @author  zhongyg
     * @date    2017-8-3 15:30:59
     * @version V2.0
     * @desc   物流费率
     */
    public function deleteAction() {
        $condition = $this->getPut();
        $id = $this->get('id') ?: $this->getPut('id');
        if ($id) {
            $ids = explode(',', $id);
            if (is_array($ids)) {
                $where['id'] = ['in', $condition['id']];
            } else {
                $where['id'] = $id;
            }
        }
        $Logi_Rate_model = new LogiRateModel();
        $result = $Logi_Rate_model->where($where)->save(['status' => 'DELETE']);
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
