<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Vatariff
 * @author  zhongyg
 * @date    2017-8-5 18:38:03
 * @version V2.0
 * @desc   关税税率
 */
class VatariffController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of MarketAreaModel
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   目的国 增值税、关税
     */
    public function listAction() {
        $data = $this->get() ?: $this->getPut();

        $va_tariff_model = new VatariffModel();

        if (redisGet('Vatariff_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('Vatariff_' . md5(json_encode($data))), true);
        } else {
            $arr = $va_tariff_model->getlist($data, false);

            $this->_setUserName($arr);
            if ($arr) {
                redisSet('Vatariff_' . md5(json_encode($data)), json_encode($arr));
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

    /**
     * Description of 详情
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   目的国 增值税、关税
     */
    public function infoAction() {
        $id = $this->getPut('id', '');
        if (!$id) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $va_tariff_model = new VatariffModel();
        $result = $va_tariff_model->info($id);
        $data = [$result];
        $this->_setUserName($data);
        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data[0]);
        } elseif ($result == null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        exit;
    }

    /**
     * Description of 删除缓存
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   目的国 增值税、关税
     */
    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Vatariff_*');
        $redis->delete($keys);
    }

    /**
     * Description of 新增目的国 增值税、关税
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   目的国 增值税、关税
     */
    public function createAction() {
        $data = $this->getPut();
        $va_tariff_model = new VatariffModel();

        $result = $va_tariff_model->create_data($data, $this->user['id']);

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
     * Description of 更新目的国 增值税、关税
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   目的国 增值税、关税
     */
    public function updateAction() {
        $data = $this->getPut();
        $va_tariff_model = new VatariffModel();
        $result = $va_tariff_model->update_data($data, $this->user['id']);
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
     * Description of 删除目的国 增值税、关税
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   目的国 增值税、关税
     */
    public function deleteAction() {

        $id = $this->get('id') ?: $this->getPut('id');
        if ($id) {
            $ids = explode(',', $id);
            if (is_array($ids)) {
                $where['id'] = ['in', $ids];
            } else {
                $where['id'] = $id;
            }
        }
        $va_tariff_model = new VatariffModel();
        $result = $va_tariff_model->where($where)
                ->save(['status' => 'DELETE']);
        if ($result !== false) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
