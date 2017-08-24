<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExportTariffController
 * @author  zhongyg
 * @date    2017-8-2 15:31:26
 * @version V2.0
 * @desc   增值税、关税信息
 */
class ExporttariffController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function listAction() {
        $data = $this->getPut();
        $data['lang'] = $this->getPut('lang', 'zh');
        $export_tariff_model = new ExportTariffModel();
        if (redisGet('Export_Tariff_listall_' . md5(json_encode($data)))) {
            $arr = json_decode(redisGet('Export_Tariff_listall_' . md5(json_encode($data))), true);
        } else {
            $arr = $export_tariff_model->getlist($data, false);
            if ($arr) {
                redisSet('Export_Tariff_listall_' . md5(json_encode($data)), json_encode($arr));
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
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function infoAction() {
        $id = $this->getPut('id');

        if (!$bn) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $export_tariff_model = new ExportTariffModel();
        $result = $export_tariff_model->info($bn, 'en');
        unset($result['id']);
        unset($result['lang']);
        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);
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
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   增值税、关税信息
     */
    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Export_Tariff_*');
        $redis->delete($keys);
    }

    /**
     * Description of 新增增值税、关税信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function createAction() {
        $export_tariff_model = new ExportTariffModel();
        $data = $this->getPut();

        $result = $export_tariff_model->create_data($data);
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
     * Description of 更新增值税、关税信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function updateAction() {
        $export_tariff_model = new ExportTariffModel();
        $data = $this->getPut();

        $result = $export_tariff_model->update_data($data);
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
     * Description of 删除增值税、关税信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   增值税、关税信息
     */
    public function deleteAction() {
        $condition = $this->put_data;
        $id = $this->getPut('id');
        if ($id) {
            $ids = explode(',', $id);
            if (is_array($ids)) {
                $where['id'] = ['in', $condition['id']];
            } else {
                $where['id'] = $id;
            }
        }
        $result = $this->_model->where($where)->save(['status' => 'DELETED']);
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
