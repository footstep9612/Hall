<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 供应链列表
 * @author  zhongyg
 * @date    2017-11-10 13:32:36
 * @version V2.0
 * @desc
 */
class SupplierchainController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 供应链列表
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function listChainAction() {
        $condition = $this->getPut();
        $supplier_model = new SupplierChainModel();
        $data = $supplier_model->getListChain($condition);
        if ($data) {
            $this->setvalue('count', $supplier_model->getCountChain($condition));
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('数据为空!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('获取失败!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 供应链列表
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function listAction() {
        $condition = $this->getPut();
        $supplier_model = new SupplierChainModel();
        $data = $supplier_model->getList($condition);
        if ($data) {
            $this->setvalue('count', $supplier_model->getCount($condition));
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('数据为空!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('获取失败!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 批量更改供应商等级
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function batchUpdateLevelAction() {
        $supplier_level = $this->getPut('supplier_level');
        if (!$supplier_level) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商等级不能为空!');
        }
        if (!is_numeric($supplier_level) || $supplier_level > 4 || $supplier_level < 1) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商等级必须是大于等于1小于等于4的数字!');
        }
        $supplier_ids = $this->getPut('supplier_id');

        if (empty($supplier_ids)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择供应商!');
        }
        if (!is_array($supplier_ids)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID必须是数字数组!');
        }
        $supplier_model = new SupplierChainModel();
        $data = $supplier_model->batchUpdateLevel($supplier_ids, $supplier_level);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('更新成功!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('更新失败!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 批量更改供应商等级
     * @author  zhongyg
     * @date    2017-11-10 13:32:36
     * @version V2.0
     * @desc
     */
    public function CheckedAction() {
        $supplier_level = $this->getPut('supplier_level');
        if (!$supplier_level) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商等级不能为空!');
        }
        if (!is_numeric($supplier_level) || $supplier_level > 4 || $supplier_level < 1) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商等级必须是大于等于1小于等于4的数字!');
        }
        $supplier_id = $this->getPut('supplier_id');
        if (empty($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择供应商!');
        }
        if (!is_numeric($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID必须是数字!');
        }
        $is_erui = $this->getPut('is_erui');
        if (empty($is_erui)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择是否符合易瑞!');
        }
        $supplier_model = new SupplierChainModel();
        $data = $supplier_model->ChainChecked($supplier_id, $supplier_level, $is_erui);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('更新成功!');
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('更新失败!');
            $this->jsonReturn();
        }
    }

}
