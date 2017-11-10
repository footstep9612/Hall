<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Supplierbrand
 * @author  zhongyg
 * @date    2017-11-5 10:02:57
 * @version V2.0
 * @desc
 */
class SupplierbrandController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * 获取列表
     * @date    2017-11-5 10:02:57
     * @author zyg
     */
    public function listAction() {
        $condition = $this->getPut();
        $supplier_model = new SupplierBrandModel();
        $data = $supplier_model->getList($condition);

        $suppliercount = $supplier_model->getSupplierCount($condition);
        $brandcount = $supplier_model->getBrandCount($condition);
        if ($data) {
            $this->setvalue('supplier_count', $suppliercount);
            $this->setvalue('brand_count', $brandcount);
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setvalue('supplier_count', $suppliercount);
            $this->setvalue('brand_count', $brandcount);
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn($data);
        } else {

            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn(null);
        }
    }

    /**
     * 供应商品牌列表
     * @date    2017-11-5 10:02:57
     * @author zyg
     */
    public function listBySupplierIdAction() {
        $supplier_id = $this->getPut('supplier_id');
        if (empty($supplier_id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID不能为空!');
            $this->jsonReturn();
        }
        $supplier_model = new SupplierBrandModel();
        $data = $supplier_model->listBySupplierId($supplier_id);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn(null);
        }
    }

    private function _valid($condition, $is_batch = false) {
        if (empty($condition['supplier_id'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID不能为空!');
            $this->jsonReturn();
        } elseif (intval($condition['supplier_id']) <= 0) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商ID必须是大于零的整数!');
            $this->jsonReturn();
        } else {

            $supplier_id = $condition['supplier_id'];
        }

        if (empty($condition['brand_id']) && !$is_batch) {

            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('品牌ID不能为空!');
            $this->jsonReturn();
        } elseif ($condition['brand_id'] && intval($condition['brand_id']) <= 0 && !$is_batch) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('品牌ID必须是大于零的整数!');
            $this->jsonReturn();
        } elseif ($condition['brand_id'] && !$is_batch) {
            $brand_id = $condition['brand_id'];
        }


        if (empty($condition['brand_ids']) && $is_batch) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('品牌ID不能为空!');
            $this->jsonReturn();
        } elseif ($condition['brand_ids'] && !is_array($condition['brand_ids']) && $is_batch) {

            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('品牌ID组必须是整数数组!');
            $this->jsonReturn();
        } elseif ($condition['brand_ids'] && $is_batch) {
            $brand_ids = $condition['brand_ids'];
        }
        $supplier_model = new SupplierModel();
        $supplierinfo = $supplier_model->field('id')->where(['id' => $supplier_id, 'status' => ['in', [SupplierModel::STATUS_VALID, 'APPROVED']]])->find();

        if (!$supplierinfo) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('供应商不存在!');
            $this->jsonReturn();
        }

        $brand_model = new BrandModel();
        if ($brand_id && !$is_batch) {
            $brandinfo = $brand_model->field('id')->where(['id' => $brand_id, 'status' => SupplierModel::STATUS_VALID])->find();
            if (!$brandinfo) {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('品牌不存在!');
                $this->jsonReturn();
            }
        }
        if ($brand_ids && $is_batch) {
            $brandinfo = $brand_model->field('id')->where(['id' => ['in', $brand_ids], 'status' => SupplierModel::STATUS_VALID])->select();
            if (!$brandinfo) {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('品牌不存在!');
                $this->jsonReturn();
            }
        }
    }

    /**
     * 获取列表
     * @date    2017-11-5 10:02:57
     * @author zyg
     */
    public function createAction() {
        $condition = $this->getPut();
        $this->_valid($condition);
        $supplier_model = new SupplierBrandModel();
        $data = $supplier_model->create_data($condition);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn(null);
        }
    }

    /**
     * 更新供应商品牌
     * @date    2017-11-5 10:02:57
     * @author zyg
     */
    public function updateAction() {
        $condition = $this->getPut();
        $this->_valid($condition);
        $supplier_model = new SupplierBrandModel();
        $data = $supplier_model->update_data($condition);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn(null);
        }
    }

    /**
     * 批量更新新增供应商品牌
     * @date    2017-11-5 10:02:57
     * @author zyg
     */
    public function updateAndCreatesAction() {
        $condition = $this->getPut();
        $this->_valid($condition, true);
        $supplier_model = new SupplierBrandModel();
        $data = $supplier_model->updateAndCreates($condition);
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn(null);
        }
    }

}
